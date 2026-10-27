<?php

namespace App\Modules\Affiliate\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Affiliate\Services\CommissionService;
use App\Modules\Affiliate\Models\Commission;
use Illuminate\Http\Request;

class CommissionController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$this->tenantId()` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected $service;

    public function __construct(CommissionService $service)
    {
        $this->service = $service;
    }

    /**
     * Display commissions.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'type', 'affiliate_id', 'date_from', 'date_to']);
        $commissions = $this->service->getCommissions($this->tenantId(), $filters);

        return response()->json([
            'success' => true,
            'data' => $commissions,
        ]);
    }

    /**
     * Display a specific commission.
     */
    public function show(Commission $commission)
    {
        if ($commission->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $commission->load(['affiliate', 'referral']),
        ]);
    }

    /**
     * Approve a commission.
     */
    public function approve(Request $request, Commission $commission)
    {
        if ($commission->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $commission = $this->service->approveCommission($commission, $request->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Commission approved',
            'data' => $commission,
        ]);
    }

    /**
     * Reject a commission.
     */
    public function reject(Request $request, Commission $commission)
    {
        if ($commission->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $commission = $this->service->rejectCommission($commission, $validated['reason'], $request->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Commission rejected',
            'data' => $commission,
        ]);
    }

    /**
     * Bulk approve commissions.
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $count = Commission::whereIn('id', $validated['ids'])
            ->where('tenant_id', $this->tenantId())
            ->where('status', 'pending')
            ->update(['status' => 'approved']);

        return response()->json([
            'success' => true,
            'message' => "{$count} commissions approved",
        ]);
    }

    /**
     * Create a commission manually.
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'affiliate_id' => 'required|exists:affiliates,id',
            'referral_code_id' => 'nullable|exists:affiliate_referral_codes,id',
            'referral_id' => 'nullable|exists:affiliate_referrals,id',
            'order_id' => 'nullable|string',
            'invoice_id' => 'nullable|string',
            'contact_id' => 'nullable|integer',
            'user_id' => 'nullable|integer',
            'commission_type' => 'required|in:sale,subscription,upgrade,renewal,first_purchase',
            'base_value' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $commission = $this->service->createCommission(
            $this->tenantId(),
            $validated['affiliate_id'],
            $validated['referral_code_id'] ?? 0,
            $validated['base_value'],
            $validated['commission_type'],
            array_filter($validated, fn($k) => !in_array($k, ['affiliate_id', 'referral_code_id', 'base_value', 'commission_type']), ARRAY_FILTER_USE_KEY)
        );

        return response()->json([
            'success' => true,
            'message' => 'Commission created',
            'data' => $commission,
        ], 201);
    }
}
