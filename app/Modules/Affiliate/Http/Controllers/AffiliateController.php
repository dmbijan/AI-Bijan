<?php

namespace App\Modules\Affiliate\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Affiliate\Services\AffiliateService;
use App\Modules\Affiliate\Models\Affiliate;
use Illuminate\Http\Request;

class AffiliateController extends Controller
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

    public function __construct(AffiliateService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of affiliates.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'tier', 'search']);
        $affiliates = $this->service->getAffiliates($this->tenantId(), $filters);

        return response()->json([
            'success' => true,
            'data' => $affiliates,
        ]);
    }

    /**
     * Store a new affiliate application.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:affiliates,email',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'website' => 'nullable|url',
            'social_media' => 'nullable|array',
            'payment_method' => 'nullable|array',
            'bio' => 'nullable|string',
        ]);

        $affiliate = $this->service->createApplication($validated + [
            'tenant_id' => $this->tenantId(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Affiliate application submitted successfully',
            'data' => $affiliate,
        ], 201);
    }

    /**
     * Display the specified affiliate.
     */
    public function show(Affiliate $affiliate)
    {
        if ($affiliate->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $affiliate->load(['referrals', 'commissions']),
        ]);
    }

    /**
     * Update the affiliate.
     */
    public function update(Request $request, Affiliate $affiliate)
    {
        if ($affiliate->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'website' => 'nullable|url',
            'social_media' => 'nullable|array',
            'payment_method' => 'nullable|array',
            'bio' => 'nullable|string',
        ]);

        $affiliate = $this->service->updateAffiliate($affiliate, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Affiliate updated successfully',
            'data' => $affiliate,
        ]);
    }

    /**
     * Approve an affiliate application.
     */
    public function approve(Request $request, Affiliate $affiliate)
    {
        if ($affiliate->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $affiliate = $this->service->approveApplication($affiliate, $request->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Affiliate approved successfully',
            'data' => $affiliate,
        ]);
    }

    /**
     * Reject an affiliate application.
     */
    public function reject(Request $request, Affiliate $affiliate)
    {
        if ($affiliate->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $affiliate = $this->service->rejectApplication($affiliate, $validated['reason']);

        return response()->json([
            'success' => true,
            'message' => 'Affiliate rejected',
            'data' => $affiliate,
        ]);
    }

    /**
     * Activate an affiliate account.
     */
    public function activate(Affiliate $affiliate)
    {
        if ($affiliate->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $affiliate = $this->service->activate($affiliate);

        return response()->json([
            'success' => true,
            'message' => 'Affiliate activated',
            'data' => $affiliate,
        ]);
    }

    /**
     * Deactivate an affiliate account.
     */
    public function deactivate(Affiliate $affiliate)
    {
        if ($affiliate->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $affiliate = $this->service->deactivate($affiliate);

        return response()->json([
            'success' => true,
            'message' => 'Affiliate deactivated',
            'data' => $affiliate,
        ]);
    }

    /**
     * Get affiliate statistics.
     */
    public function stats(Request $request)
    {
        $stats = $this->service->getAffiliateStats($this->tenantId());

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Evaluate all affiliate tiers.
     */
    public function evaluateTiers(Request $request)
    {
        $results = $this->service->bulkEvaluateTiers($this->tenantId());

        return response()->json([
            'success' => true,
            'message' => 'Tier evaluation completed',
            'data' => $results,
        ]);
    }
}
