<?php

namespace App\Modules\Entitlement\Http\Controllers;

use App\Models\Plan;
use App\Modules\Entitlement\Models\EntitlementGrant;
use App\Modules\Entitlement\Services\EntitlementService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * API B-Entitlement (BAB 90).
 */
class EntitlementController extends Controller
{
    public function __construct(private EntitlementService $servis)
    {
    }

    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    // ---------------------------------------------------------------
    // STATIK
    // ---------------------------------------------------------------

    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->ringkasan($this->tenantId()),
        ]);
    }

    public function plans(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Plan::query()
                ->where('is_active', true)
                ->orderBy('price_monthly')
                ->get()
                ->map(fn ($p) => [
                    'code' => $p->code,
                    'name' => $p->name,
                    'price_monthly' => (float) $p->price_monthly,
                    'max_users' => $p->max_users,
                    'max_locations' => $p->max_locations,
                    'storage_mb' => $p->storage_mb,
                    'ai_credits_monthly' => (int) $p->ai_credits_monthly,
                    'modules' => (array) ($p->modules ?? []),
                    'limits' => \App\Modules\Entitlement\Models\PlanLimit::query()
                        ->where('plan_id', $p->id)
                        ->get()
                        ->map(fn ($l) => [
                            'limit_key' => $l->limit_key,
                            'name' => $l->name,
                            'limit' => $l->limit_value,
                            'unit' => $l->unit,
                            'period' => $l->period,
                        ])->all(),
                ])->all(),
        ]);
    }

    public function activeModules(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->modulAktif($this->tenantId()),
        ]);
    }

    public function limits(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->semakSemuaHad($this->tenantId()),
        ]);
    }

    public function catalogue(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->senaraiSemuaModul(),
        ]);
    }

    public function seedPlans(): JsonResponse
    {
        $jumlah = $this->servis->janaPlanLalai();

        return response()->json([
            'success' => true,
            'created' => $jumlah,
            'message' => $jumlah > 0
                ? "{$jumlah} plan lalai dijana."
                : 'Semua plan lalai telah wujud.',
        ]);
    }

    // ---------------------------------------------------------------
    // MUTASI
    // ---------------------------------------------------------------

    public function changePlan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan_code' => ['required', 'string', 'max:50'],
            'status' => ['nullable', 'in:trial,active,past_due,suspended,cancelled'],
            'ends_at' => ['nullable', 'date'],
        ]);

        $plan = Plan::query()->where('code', $data['plan_code'])->firstOrFail();

        $pilihan = [];

        if (isset($data['status'])) {
            $pilihan['status'] = $data['status'];
        }

        if (isset($data['ends_at'])) {
            $pilihan['ends_at'] = \Illuminate\Support\Carbon::parse($data['ends_at']);
        }

        $langganan = $this->servis->tukarPlan($this->tenantId(), $plan->id, $pilihan);
        $this->servis->clearCache();

        return response()->json([
            'success' => true,
            'data' => $langganan,
            'modules_active' => $this->servis->modulAktif($this->tenantId()),
        ]);
    }

    public function grantModule(Request $request): JsonResponse
    {
        $data = $request->validate([
            'module' => ['required', 'string', 'max:40'],
            'source' => ['nullable', 'in:addon,trial,override'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $grant = $this->servis->grantModul(
            $this->tenantId(),
            $data['module'],
            $data['source'] ?? 'addon',
            isset($data['expires_at']) ? \Illuminate\Support\Carbon::parse($data['expires_at']) : null
        );

        $this->servis->clearCache();

        return response()->json(['success' => true, 'data' => $grant], 201);
    }

    public function revokeModule(Request $request): JsonResponse
    {
        $data = $request->validate([
            'module' => ['required', 'string', 'max:40'],
        ]);

        $berjaya = $this->servis->revokeModul($this->tenantId(), $data['module']);
        $this->servis->clearCache();

        return response()->json([
            'success' => $berjaya,
            'message' => $berjaya ? 'Modul dinyahaktifkan.' : 'Grant tidak dijumpai.',
        ]);
    }
}
