<?php

namespace App\Modules\AIGateway\Services;

use App\Modules\AIGateway\Models\AiModel;
use App\Modules\AIGateway\Models\AiCreditWallet;
use App\Modules\AIGateway\Models\AiUsageEvent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AI Gateway Service (BAB 21)
 *
 * Satu pintu untuk semua panggilan model AI.
 * API key adalah ADMIN ONLY dan tidak pernah didedahkan ke klien.
 */
class GatewayService
{
    public function __construct(
        protected PiiRedactor $redactor,
        protected PromptGuard $guard
    ) {
    }

    /**
     * Pilih model berdasarkan jenis tugas
     */
    public function routeModel(string $taskType, ?string $preferredModel = null): ?AiModel
    {
        if ($preferredModel) {
            $model = AiModel::findByModelId($preferredModel);
            if ($model && $model->is_active) {
                return $model;
            }
        }

        $modelId = config("ai_gateway.model_routing.{$taskType}")
            ?? config('ai_gateway.model_routing.default');

        return AiModel::findByModelId($modelId);
    }

    /**
     * Panggil model AI melalui gateway
     */
    public function call(
        int $tenantId,
        array $messages,
        string $taskType = 'default',
        array $options = []
    ): array {
        $userId = $options['user_id'] ?? auth()->id();
        $agentKey = $options['agent_key'] ?? null;
        $sourceModule = $options['source_module'] ?? 'ai_gateway';
        $correlationId = $options['correlation_id'] ?? (string) \Illuminate\Support\Str::ulid();

        // 0. Imbas prompt injection (BAB 62) — MESTI sebelum apa-apa, termasuk mod simulasi.
        if (config('ai_gateway.safety.scan_input', true)) {
            $imbas = $this->guard->imbasMesej($messages);
            if ($imbas['ditolak']) {
                Log::warning('AI Gateway: prompt injection disekat', [
                    'correlation_id' => $correlationId,
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'patterns' => $imbas['corak'],
                ]);

                return $this->failResponse('Permintaan disekat: corak arahan tidak selamat dikesan ('.implode(', ', $imbas['corak']).').', $correlationId);
            }
        }

        // 1. Feature flag
        if (!config('ai_gateway.features.live_calls_enabled', false)) {
            return $this->simulatedResponse($tenantId, $messages, $taskType, $correlationId);
        }

        // 2. Pilih model
        $model = $this->routeModel($taskType, $options['model'] ?? null);
        if (!$model) {
            return $this->failResponse('Tiada model tersedia untuk jenis tugas ini.', $correlationId);
        }

        // 3. Circuit breaker
        if ($this->isCircuitOpen($model->provider_model_id)) {
            return $this->failResponse('Circuit breaker terbuka — provider sedang bermasalah.', $correlationId);
        }

        // 4. Redact PII
        $safeMessages = $this->redactor->redactMessages($messages);

        // 5. Anggaran kos — semak had
        $estimatedTokens = $this->estimateTokens($safeMessages);
        if ($estimatedTokens > config('ai_gateway.guardrails.max_tokens_per_request', 200000)) {
            return $this->failResponse('Permintaan melebihi had token maksimum.', $correlationId);
        }

        // 6. Semak baki wallet
        $wallet = AiCreditWallet::forTenant($tenantId);
        $estimatedCredits = $model->calculateCredits($estimatedTokens, (int) ($options['max_output_tokens'] ?? 1000));

        if (!$wallet->canAfford($estimatedCredits)) {
            $this->logUsage($tenantId, $userId, $agentKey, $sourceModule, $model, $taskType, 0, 0, 0, 0, 0, AiUsageEvent::STATUS_BLOCKED, 'Baki kredit tidak mencukupi.', $correlationId);
            return $this->failResponse('Baki kredit AI tidak mencukupi. Sila top-up.', $correlationId);
        }

        // 7. Panggil provider
        $start = microtime(true);
        try {
            $response = $this->callProvider($model, $safeMessages, $options);
            $latency = (int) ((microtime(true) - $start) * 1000);

            if (!$response['success']) {
                $this->recordFailure($model->provider_model_id);
                $this->logUsage($tenantId, $userId, $agentKey, $sourceModule, $model, $taskType, 0, 0, 0, 0, $latency, AiUsageEvent::STATUS_FAILED, $response['error'] ?? null, $correlationId);
                return $this->failResponse($response['error'] ?? 'Panggilan provider gagal.', $correlationId);
            }

            $this->resetFailures($model->provider_model_id);

            // 7b. Sanitasi output — buang kunci/rahsia yang mungkin bocor (BAB 62).
            $kandungan = (string) $response['content'];
            if (config('ai_gateway.safety.scan_output', true)) {
                $kandungan = $this->guard->sanitasiOutput($kandungan);
            }

            // 8. Kira kredit sebenar
            $inputTokens = (int) ($response['input_tokens'] ?? $estimatedTokens);
            $outputTokens = (int) ($response['output_tokens'] ?? 0);
            $credits = $model->calculateCredits($inputTokens, $outputTokens);

            // 9. Tolak kredit
            $wallet->deduct($credits);

            // 10. Log penggunaan
            $usage = $this->logUsage(
                $tenantId, $userId, $agentKey, $sourceModule, $model, $taskType,
                $inputTokens, $outputTokens, $inputTokens + $outputTokens,
                $credits, $latency, AiUsageEvent::STATUS_SUCCESS, null, $correlationId
            );

            return [
                'success' => true,
                'content' => $kandungan,
                'model' => $model->display_name,
                'usage' => [
                    'input_tokens' => $inputTokens,
                    'output_tokens' => $outputTokens,
                    'total_tokens' => $inputTokens + $outputTokens,
                    'credits_charged' => $credits,
                    'latency_ms' => $latency,
                ],
                'correlation_id' => $correlationId,
                'usage_event_id' => $usage->id ?? null,
            ];
        } catch (\Throwable $e) {
            $latency = (int) ((microtime(true) - $start) * 1000);
            Log::error('AI Gateway call failed', ['error' => $e->getMessage(), 'correlation_id' => $correlationId]);
            $this->recordFailure($model->provider_model_id);
            $this->logUsage($tenantId, $userId, $agentKey, $sourceModule, $model, $taskType, 0, 0, 0, 0, $latency, AiUsageEvent::STATUS_FAILED, $e->getMessage(), $correlationId);

            return $this->failResponse($e->getMessage(), $correlationId);
        }
    }

    /**
     * Panggilan HTTP sebenar ke provider
     */
    protected function callProvider(AiModel $model, array $messages, array $options): array
    {
        $provider = config('ai_gateway.provider');

        $response = Http::withHeaders([
            $provider['auth_header'] => $provider['auth_prefix'] . $provider['api_key'],
            'Content-Type' => 'application/json',
        ])
            ->timeout($provider['timeout_seconds'])
            ->connectTimeout($provider['connect_timeout_seconds'])
            ->post(rtrim($provider['base_url'], '/') . '/chat/completions', [
                'model' => $model->provider_model_id,
                'messages' => $messages,
                'temperature' => $options['temperature'] ?? 0.7,
                'max_tokens' => $options['max_output_tokens'] ?? 1000,
            ]);

        if (!$response->successful()) {
            return [
                'success' => false,
                'error' => "HTTP {$response->status()}: " . substr($response->body(), 0, 300),
            ];
        }

        $data = $response->json();

        return [
            'success' => true,
            'content' => $data['choices'][0]['message']['content'] ?? '',
            'input_tokens' => $data['usage']['prompt_tokens'] ?? 0,
            'output_tokens' => $data['usage']['completion_tokens'] ?? 0,
        ];
    }

    /**
     * Respons simulasi apabila live calls dimatikan
     */
    protected function simulatedResponse(int $tenantId, array $messages, string $taskType, string $correlationId): array
    {
        $model = $this->routeModel($taskType);
        $estimatedTokens = $this->estimateTokens($messages);

        return [
            'success' => true,
            'content' => '[SIMULASI] Panggilan AI tidak diaktifkan. Tetapkan AI_GATEWAY_LIVE=true untuk panggilan sebenar.',
            'model' => $model?->display_name ?? 'simulated',
            'usage' => [
                'input_tokens' => $estimatedTokens,
                'output_tokens' => 0,
                'total_tokens' => $estimatedTokens,
                'credits_charged' => 0,
                'latency_ms' => 0,
            ],
            'correlation_id' => $correlationId,
            'simulated' => true,
        ];
    }

    protected function failResponse(string $error, string $correlationId): array
    {
        return [
            'success' => false,
            'error' => $error,
            'correlation_id' => $correlationId,
        ];
    }

    /**
     * Log penggunaan
     */
    protected function logUsage(
        int $tenantId,
        ?int $userId,
        ?string $agentKey,
        string $sourceModule,
        AiModel $model,
        string $taskType,
        int $inputTokens,
        int $outputTokens,
        int $totalTokens,
        int $credits,
        int $latencyMs,
        string $status,
        ?string $error,
        string $correlationId
    ): AiUsageEvent {
        return AiUsageEvent::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'agent_key' => $agentKey,
            'source_module' => $sourceModule,
            'provider_model_id' => $model->provider_model_id,
            'model_display_name' => $model->display_name,
            'tier' => $model->tier,
            'task_type' => $taskType,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_tokens' => $totalTokens,
            'credits_charged' => $credits,
            'cost_rm' => $credits * (float) config('ai_gateway.base_credit_rate', 0.0001),
            'latency_ms' => $latencyMs,
            'status' => $status,
            'error_message' => $error,
            'correlation_id' => $correlationId,
        ]);
    }

    // ===== Circuit Breaker =====

    protected function isCircuitOpen(string $modelId): bool
    {
        if (!config('ai_gateway.guardrails.circuit_breaker.enabled', true)) {
            return false;
        }

        return Cache::has($this->breakerKey($modelId));
    }

    protected function recordFailure(string $modelId): void
    {
        $threshold = config('ai_gateway.guardrails.circuit_breaker.failure_threshold', 5);
        $cooldown = config('ai_gateway.guardrails.circuit_breaker.cooldown_seconds', 60);
        $key = $this->breakerKey($modelId);

        $count = Cache::increment($key);

        if ($count >= $threshold) {
            Cache::put($key, $count, $cooldown);
        }
    }

    protected function resetFailures(string $modelId): void
    {
        Cache::forget($this->breakerKey($modelId));
    }

    protected function breakerKey(string $modelId): string
    {
        return 'ai_gateway:breaker:' . md5($modelId);
    }

    // ===== Helpers =====

    /**
     * Anggaran token (~4 aksara per token)
     */
    protected function estimateTokens(array $messages): int
    {
        $text = json_encode($messages);
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Senarai model untuk paparan pelanggan (tanpa ID dalaman)
     */
    public function customerModelList(?string $tier = null): array
    {
        $query = AiModel::active()->orderBy('tier');

        if ($tier) {
            $query->byTier($tier);
        }

        return $query->get()->map(fn ($m) => $m->toCustomerArray())->toArray();
    }
}
