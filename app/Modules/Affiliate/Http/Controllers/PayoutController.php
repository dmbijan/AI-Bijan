<?php

namespace App\Modules\Affiliate\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Affiliate\Services\CommissionService;
use App\Modules\Affiliate\Models\AffiliatePayout;
use Illuminate\Http\Request;

class PayoutController extends Controller
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
     * Display payouts.
     */
    public function index(Request $request)
    {
        $query = AffiliatePayout::where('tenant_id', $this->tenantId())
            ->with(['affiliate']);

        if ($request->has('affiliate_id')) {
            $query->where('affiliate_id', $request->affiliate_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $payouts = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $payouts,
        ]);
    }

    /**
     * Display a specific payout.
     */
    public function show(AffiliatePayout $payout)
    {
        if ($payout->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $payout->load(['commissions']),
        ]);
    }

    /**
     * Process a payout.
     */
    public function process(Request $request, AffiliatePayout $payout)
    {
        if ($payout->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'transaction_reference' => 'required|string',
        ]);

        $payout = $payout->process(
            $validated['transaction_reference'],
            $request->user_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Payout processed',
            'data' => $payout,
        ]);
    }

    /**
     * Cancel a payout.
     */
    public function cancel(Request $request, AffiliatePayout $payout)
    {
        if ($payout->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $payout = $payout->cancel($validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Payout cancelled',
            'data' => $payout,
        ]);
    }

    /**
     * Create payout from approved commissions.
     */
    public function createFromCommissions(Request $request)
    {
        $validated = $request->validate([
            'affiliate_id' => 'required|exists:affiliates,id',
            'payment_method' => 'nullable|in:bank_transfer,paypal,stripe,wire',
            'payment_details' => 'nullable|array',
        ]);

        try {
            $payout = $this->service->processPayout(
                $validated['affiliate_id'],
                $this->tenantId(),
                array_filter($validated, fn($k) => !in_array($k, ['affiliate_id']), ARRAY_FILTER_USE_KEY)
            );

            return response()->json([
                'success' => true,
                'message' => 'Payout created',
                'data' => $payout,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get payout summary for an affiliate.
     */
    public function getSummary(Request $request, int $affiliateId)
    {
        $summary = $this->service->getPayoutSummary($affiliateId, $this->tenantId());

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
