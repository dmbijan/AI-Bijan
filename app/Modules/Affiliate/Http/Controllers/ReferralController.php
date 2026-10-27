<?php

namespace App\Modules\Affiliate\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Affiliate\Services\ReferralService;
use App\Modules\Affiliate\Models\ReferralCode;
use Illuminate\Http\Request;

class ReferralController extends Controller
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

    public function __construct(ReferralService $service)
    {
        $this->service = $service;
    }

    /**
     * Display referral codes.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'type']);
        
        $query = ReferralCode::where('tenant_id', $this->tenantId());
        
        if ($request->has('affiliate_id')) {
            $query->where('affiliate_id', $request->affiliate_id);
        }

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('referral_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $codes = $query->orderByDesc('usage_count')->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $codes,
        ]);
    }

    /**
     * Store a new referral code.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'affiliate_id' => 'required|exists:affiliates,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|in:default,campaign,social,email',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        $code = $this->service->createCustomCode(
            $validated['affiliate_id'],
            $this->tenantId(),
            $validated + ['url' => null]
        );

        return response()->json([
            'success' => true,
            'message' => 'Referral code created successfully',
            'data' => $code,
        ], 201);
    }

    /**
     * Display the specified referral code.
     */
    public function show(ReferralCode $referralCode)
    {
        if ($referralCode->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $referralCode->load(['affiliate']),
        ]);
    }

    /**
     * Update the referral code.
     */
    public function update(Request $request, ReferralCode $referralCode)
    {
        if ($referralCode->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,inactive,pending',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        $referralCode->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Referral code updated',
            'data' => $referralCode,
        ]);
    }

    /**
     * Generate affiliate link URL.
     */
    public function generateLink(Request $request, ReferralCode $referralCode)
    {
        if ($referralCode->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $link = $this->service->generateLink(
            $referralCode->referral_code,
            $request->input('subid'),
            $request->only(['utm_source', 'utm_medium', 'utm_campaign'])
        );

        return response()->json([
            'success' => true,
            'data' => [
                'referral_code' => $referralCode->referral_code,
                'link' => $link,
            ],
        ]);
    }

    /**
     * Track a click on referral link.
     */
    public function trackClick(Request $request, string $code)
    {
        try {
            $click = $this->service->trackClick($code, $this->tenantId(), [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer_url' => $request->header('referer'),
                'landing_url' => $request->url(),
                'country' => $request->header('X-Country') ?? null,
                'region' => $request->header('X-Region') ?? null,
                'city' => $request->header('X-City') ?? null,
                'device' => $request->header('X-Device') ?? null,
                'browser' => $request->header('X-Browser') ?? null,
                'os' => $request->header('X-OS') ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Click tracked successfully',
                'data' => $click,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Convert a referral.
     */
    public function convertReferral(Request $request)
    {
        $validated = $request->validate([
            'referral_id' => 'required|exists:affiliate_referrals,id',
            'conversion_type' => 'required|in:sale,subscription,upgrade,renewal,first_purchase',
            'conversion_value' => 'required|numeric|min:0',
        ]);

        try {
            $referral = $this->service->convertReferral(
                $validated['referral_id'],
                $validated['conversion_type'],
                $validated['conversion_value']
            );

            return response()->json([
                'success' => true,
                'message' => 'Referral converted successfully',
                'data' => $referral,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get referrals for an affiliate.
     */
    public function getAffiliateReferrals(Request $request, int $affiliateId)
    {
        $filters = $request->only(['status', 'type']);
        $referrals = $this->service->getAffiliateReferrals($affiliateId, $this->tenantId(), $filters);

        return response()->json([
            'success' => true,
            'data' => $referrals,
        ]);
    }

    /**
     * Deactivate expired codes.
     */
    public function deactivateExpired()
    {
        $count = $this->service->deactivateExpiredCodes();

        return response()->json([
            'success' => true,
            'message' => "{$count} expired codes deactivated",
        ]);
    }
}
