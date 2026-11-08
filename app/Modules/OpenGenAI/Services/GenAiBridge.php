<?php

namespace App\Modules\OpenGenAI\Services;

use App\Modules\AIGateway\Services\GatewayService;
use App\Modules\OpenGenAI\Models\GenAiJob;
use App\Modules\OpenGenAI\Models\GenAiProvider;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Str;

/**
 * GenAiBridge — jambatan antara AI Gateway (katalog model terkawal) dan
 * OpenGenAI (provider sendiri tenant).
 *
 * AI Gateway ialah pintu rasmi platform: kunci API dikawal admin, kos
 * dikira automatik, PII direduksi. OpenGenAI pula membolehkan tenant
 * menyambungkan provider sendiri (Ollama tempatan, akaun sendiri, dll).
 *
 * Jambatan ini membolehkan mana-mana modul AI BIJAN menggunakan kedua-dua
 * laluan melalui satu kontrak:
 *
 *   - `pakaiGateway()` — model rasmi platform (bajo, terkawal)
 *   - `pakaiSendiri()`   — provider tenant (privasi, fleksibel)
 */
class GenAiBridge
{
    public function __construct(
        private GatewayService $gateway,
        private TenantContext $ctx,
    ) {}

    /**
     * Jana melalui AI Gateway (model rasmi platform).
     *
     * @param  array<string, mixed>  $option
     * @return array{success: bool, text: ?string, error: ?string, job_id: ?int}
     */
    public function pakaiGateway(string $prompt, string $taskType = 'default', array $option = []): array
    {
        $tenantId = (int) $this->ctx->id();

        if ($tenantId <= 0) {
            return ['success' => false, 'text' => null, 'error' => 'Tiada tenant aktif.', 'job_id' => null];
        }

        $hasil = $this->gateway->call(
            $tenantId,
            [['role' => 'user', 'content' => $prompt]],
            $taskType,
            array_merge($option, ['source_module' => 'open_gen_ai'])
        );

        $teks = $hasil['content'] ?? $hasil['text'] ?? null;

        // Rekod dalam gen_ai_jobs supaya jejak audit seragam.
        $job = GenAiJob::create([
            'uuid' => (string) Str::uuid(),
            'model' => $hasil['model'] ?? null,
            'capability' => GenAiJob::KEMAHRIRAN_TEXT,
            'task_type' => $taskType,
            'status' => ($hasil['success'] ?? false)
                ? GenAiJob::STATUS_SUCCEEDED
                : GenAiJob::STATUS_FAILED,
            'prompt' => $prompt,
            'output_text' => $teks,
            'output_meta' => $hasil['meta'] ?? null,
            'prompt_tokens' => (int) ($hasil['usage']['prompt_tokens'] ?? 0),
            'completion_tokens' => (int) ($hasil['usage']['completion_tokens'] ?? 0),
            'total_tokens' => (int) ($hasil['usage']['total_tokens'] ?? 0),
            'error_message' => $hasil['error'] ?? null,
            'created_by' => $option['user_id'] ?? auth()->id(),
            'started_at' => now(),
            'completed_at' => now(),
        ]);

        return [
            'success' => (bool) ($hasil['success'] ?? false),
            'text' => $teks,
            'error' => $hasil['error'] ?? null,
            'job_id' => $job->id,
        ];
    }

    /**
     * Jana melalui provider sendiri tenant (OpenGenAI).
     *
     * @param  array<string, mixed>  $option
     * @return array{success: bool, text: ?string, error: ?string, job_id: ?int}
     */
    public function pakaiSendiri(string $prompt, string $taskType = 'default', array $option = []): array
    {
        $servis = app(GenAiService::class);
        $hasil = $servis->jalankan(GenAiJob::KEMAHRIRAN_TEXT, $prompt, array_merge($option, [
            'task_type' => $taskType,
        ]));

        return [
            'success' => $hasil['success'],
            'text' => $hasil['output'],
            'error' => $hasil['error'],
            'job_id' => $hasil['job']?->id,
        ];
    }

    /**
     * Jana automatik: guna provider sendiri jika tenant ada, selain itu
     * jatuh balik kepada AI Gateway.
     *
     * @param  array<string, mixed>  $option
     * @return array{success: bool, text: ?string, error: ?string, job_id: ?int, sumber: string}
     */
    public function jana(string $prompt, string $taskType = 'default', array $option = []): array
    {
        $adaSendiri = GenAiProvider::query()
            ->where('is_active', true)
            ->exists();

        if ($adaSendiri && ($option['utamakan_sendiri'] ?? true)) {
            $hasil = $this->pakaiSendiri($prompt, $taskType, $option);

            if ($hasil['success']) {
                $hasil['sumber'] = 'provider-sendiri';

                return $hasil;
            }
        }

        $hasil = $this->pakaiGateway($prompt, $taskType, $option);
        $hasil['sumber'] = 'ai-gateway';

        return $hasil;
    }
}
