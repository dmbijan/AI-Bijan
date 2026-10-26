<?php

namespace App\Modules\Affiliate\Services;

use App\Modules\Affiliate\Models\ReferralCode;
use App\Modules\Affiliate\Models\Referral;
use App\Modules\Affiliate\Models\AffiliateClick;
use Illuminate\Support\Facades\DB;

class ReferralService
{
    /**
     * Create default referral code for affiliate.
     */
    public function createDefaultCode(int $affiliateId, int $tenantId): ReferralCode
    {
        return ReferralCode::create([
            'tenant_id' => $tenantId,
            'affiliate_id' => $affiliateId,
            'referral_code' => ReferralCode::generateCode($affiliateId),
            'name' => 'Default Referral Link',
            'description' => 'Default referral code for affiliate',
            'type' => 'default',
            'url' => null,
            'status' => 'active',
            'usage_count' => 0,
            'conversion_count' => 0,
            'total_clicks' => 0,
            'total_conversions' => 0,
            'conversion_rate' => 0.0,
        ]);
    }

    /**
     * Create custom referral code.
     */
    public function createCustomCode(int $affiliateId, int $tenantId, array $data): ReferralCode
    {
        $data['referral_code'] = $data['referral_code'] ?? ReferralCode::generateCode($affiliateId);
        $data['status'] = $data['status'] ?? 'active';
        $data['usage_count'] = 0;
        $data['conversion_count'] = 0;
        $data['total_clicks'] = 0;
        $data['total_conversions'] = 0;
        $data['conversion_rate'] = 0.0;

        return ReferralCode::create($data);
    }

    /**
     * Get referral code by code string.
     */
    public function getReferralCode(string $code, int $tenantId): ?ReferralCode
    {
        return ReferralCode::where('referral_code', strtoupper($code))
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Track a click on referral link.
     */
    public function trackClick(string $code, int $tenantId, array $clickData = []): AffiliateClick
    {
        $referralCode = $this->getReferralCode($code, $tenantId);

        if (!$referralCode || !$referralCode->isValid()) {
            throw new \InvalidArgumentException('Invalid or expired referral code');
        }

        $clickId = md5(uniqid('click_', true));

        return DB::transaction(function () use ($referralCode, $clickData, $clickId) {
            // Create click record
            $click = AffiliateClick::create([
                'tenant_id' => $referralCode->tenant_id,
                'affiliate_id' => $referralCode->affiliate_id,
                'referral_code_id' => $referralCode->id,
                'click_id' => $clickId,
                'ip_address' => $clickData['ip_address'] ?? request()->ip(),
                'user_agent' => $clickData['user_agent'] ?? request()->userAgent(),
                'referrer_url' => $clickData['referrer_url'] ?? request()->header('referer'),
                'landing_url' => $clickData['landing_url'] ?? request()->url(),
                'country' => $clickData['country'] ?? null,
                'region' => $clickData['region'] ?? null,
                'city' => $clickData['city'] ?? null,
                'device' => $clickData['device'] ?? null,
                'browser' => $clickData['browser'] ?? null,
                'os' => $clickData['os'] ?? null,
                'converted' => false,
            ]);

            // Increment referral code clicks
            $referralCode->incrementClicks();

            // Set cookie for tracking duration
            $cookieDuration = config('affiliate.referral.cookie_duration_days', 30);
            cookie()->queue('aff_ref_' . $code, $referralCode->id, $cookieDuration * 1440);

            return $click;
        });
    }

    /**
     * Create a referral from code.
     */
    public function createReferral(string $code, int $tenantId, ?int $referredUserId = null, ?int $referredContactId = null): Referral
    {
        $referralCode = $this->getReferralCode($code, $tenantId);

        if (!$referralCode) {
            throw new \InvalidArgumentException('Referral code not found');
        }

        if (!config('affiliate.referral.allow_self_referral', false) && auth()->id() === $referralCode->affiliate_id) {
            throw new \InvalidArgumentException('Self-referral is not allowed');
        }

        // Check if already referred
        $existing = Referral::where('tenant_id', $tenantId)
            ->where('referral_code', $referralCode->referral_code)
            ->where(function ($q) use ($referredUserId, $referredContactId) {
                if ($referredUserId) {
                    $q->where('referred_user_id', $referredUserId);
                }
                if ($referredContactId) {
                    $q->orWhere('referred_contact_id', $referredContactId);
                }
            })
            ->first();

        if ($existing) {
            throw new \InvalidArgumentException('This user has already been referred');
        }

        return Referral::create([
            'tenant_id' => $tenantId,
            'affiliate_id' => $referralCode->affiliate_id,
            'referral_code_id' => $referralCode->id,
            'referred_user_id' => $referredUserId,
            'referred_contact_id' => $referredContactId,
            'referral_code' => $referralCode->referral_code,
            'status' => 'pending',
        ]);
    }

    /**
     * Convert a referral to commission.
     */
    public function convertReferral(int $referralId, string $conversionType, float $conversionValue): Referral
    {
        $referral = Referral::findOrFail($referralId);

        if ($referral->status === 'converted') {
            throw new \InvalidArgumentException('Referral already converted');
        }

        return $referral->convert($conversionType, $conversionValue);
    }

    /**
     * Get all referrals for an affiliate.
     */
    public function getAffiliateReferrals(int $affiliateId, int $tenantId, array $filters = [], int $perPage = 20)
    {
        $query = Referral::where('affiliate_id', $affiliateId)
            ->where('tenant_id', $tenantId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('conversion_type', $filters['type']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Deactivate expired referral codes.
     */
    public function deactivateExpiredCodes(): int
    {
        return ReferralCode::deactivateExpired();
    }

    /**
     * Generate affiliate link URL.
     */
    public function generateLink(string $code, ?string $subId = null, array $params = []): string
    {
        $baseUrl = config('app.url', url('/'));
        $prefix = config('affiliate.tracking.link_prefix', '/go/');
        
        $url = $baseUrl . $prefix . $code;

        if ($subId) {
            $url .= '?subid=' . urlencode($subId);
        }

        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }

        return $url;
    }
}
