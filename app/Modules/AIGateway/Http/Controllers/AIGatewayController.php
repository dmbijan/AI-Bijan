<?php

namespace App\Modules\AIGateway\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AIGateway\Services\GatewayService;
use App\Modules\AIGateway\Services\ModelCatalogService;
use App\Modules\AIGateway\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIGatewayController extends Controller
{
    public function __construct(
        protected GatewayService $gateway,
        protected WalletService $wallet,
        protected ModelCatalogService $catalog
    ) {
    }

    private function tenantId(): int
    {
        return app(\App\Support\Tenancy\TenantContext::class)->id();
    }

    // ===== Model Catalog (customer view) =====

    public function listModels(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->catalog->listForCustomer($request->input('tier')),
            'tiers' => config('ai_gateway.tiers'),
        ]);
    }

    // ===== Chat / Completion =====

    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'messages' => 'required|array|min:1',
            'messages.*.role' => 'required|in:system,user,assistant',
            'messages.*.content' => 'required|string',
            'task_type' => 'nullable|string',
            'model' => 'nullable|string',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_output_tokens' => 'nullable|integer|min:1',
        ]);

        $result = $this->gateway->call(
            $this->tenantId(),
            $request->messages,
            $request->input('task_type', 'default'),
            [
                'user_id' => auth()->id(),
                'agent_key' => $request->input('agent_key'),
                'source_module' => $request->input('source_module', 'ai_gateway'),
                'model' => $request->input('model'),
                'temperature' => $request->input('temperature'),
                'max_output_tokens' => $request->input('max_output_tokens'),
            ]
        );

        $status = ($result['success'] ?? false) ? 200 : 422;

        return response()->json($result, $status);
    }

    // ===== Wallet =====

    public function getWallet(): JsonResponse
    {
        $wallet = $this->wallet->get($this->tenantId());

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => (float) $wallet->balance,
                'period_quota' => (float) $wallet->period_quota,
                'used_this_period' => (float) $wallet->used_this_period,
                'effective_balance' => $wallet->effectiveBalance(),
                'usage_percent' => $wallet->usagePercent(),
                'period_start' => $wallet->period_start,
                'period_end' => $wallet->period_end,
                'currency' => $wallet->currency,
                'auto_recharge_enabled' => $wallet->auto_recharge_enabled,
            ],
            'alerts' => $wallet->applicableAlerts(),
        ]);
    }

    public function walletLedger(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->wallet->ledger($this->tenantId(), $request->integer('limit', 50)),
        ]);
    }

    public function usageSummary(Request $request): JsonResponse
    {
        $from = $request->filled('from') ? \Carbon\Carbon::parse($request->from) : null;
        $to = $request->filled('to') ? \Carbon\Carbon::parse($request->to) : null;

        return response()->json([
            'success' => true,
            'data' => $this->wallet->usageSummary($this->tenantId(), $from, $to),
        ]);
    }

    public function topUp(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'reference' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        try {
            $wallet = $this->wallet->topUp(
                $this->tenantId(),
                (float) $request->amount,
                $request->only(['reference', 'description'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Kredit ditambah.',
                'data' => [
                    'balance' => (float) $wallet->balance,
                    'effective_balance' => $wallet->effectiveBalance(),
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ===== Admin — Model Catalog =====

    public function adminListModels(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->catalog->listForAdmin($request->all()),
            'health' => $this->catalog->healthCheck(),
        ]);
    }

    public function adminUpsertModel(Request $request): JsonResponse
    {
        $request->validate([
            'provider_slug' => 'required|string',
            'provider_model_id' => 'required|string',
            'display_name' => 'required|string',
            'tier' => 'required|in:flash,standard,premium,ultra',
        ]);

        try {
            $model = $this->catalog->upsert($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Model disimpan dalam katalog.',
                'data' => $model,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function adminToggleModel(int $id): JsonResponse
    {
        try {
            $model = $this->catalog->toggle($id);
            return response()->json([
                'success' => true,
                'message' => $model->is_active ? 'Model diaktifkan.' : 'Model dinyahaktifkan.',
                'data' => $model,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 404);
        }
    }

    public function adminUsageByModel(Request $request): JsonResponse
    {
        $from = $request->filled('from') ? \Carbon\Carbon::parse($request->from) : null;
        $to = $request->filled('to') ? \Carbon\Carbon::parse($request->to) : null;

        return response()->json([
            'success' => true,
            'data' => $this->catalog->usageByModel($this->tenantId(), $from, $to),
        ]);
    }

    // ===== Admin — Usage Events =====

    public function adminUsageEvents(Request $request): JsonResponse
    {
        $query = \App\Modules\AIGateway\Models\AiUsageEvent::forTenant($this->tenantId());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('model')) {
            $query->byModel($request->model);
        }
        if ($request->filled('agent_key')) {
            $query->byAgent($request->agent_key);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('created_at')->paginate($request->integer('per_page', 50)),
        ]);
    }

    // ===== Metadata =====

    public function metadata(): JsonResponse
    {
        $provider = config('ai_gateway.provider');

        return response()->json([
            'success' => true,
            'data' => [
                'provider' => [
                    'name' => $provider['name'],
                    'base_url' => $provider['base_url'],
                    // API key TIDAK pernah didedahkan
                    'key_configured' => !empty($provider['api_key']),
                ],
                'model_routing' => config('ai_gateway.model_routing'),
                'tiers' => config('ai_gateway.tiers'),
                'credit_rules' => config('ai_gateway.credit_rules'),
                'guardrails' => config('ai_gateway.guardrails'),
                'features' => config('ai_gateway.features'),
            ],
        ]);
    }
}
