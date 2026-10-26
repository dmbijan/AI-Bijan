<?php

namespace App\Modules\Affiliate\Services;

use App\Modules\Affiliate\Models\Affiliate;
use Illuminate\Pagination\LengthAwarePaginator;

class AffiliateService
{
    /**
     * Get all affiliates for a tenant with pagination.
     */
    public function getAffiliates(int $tenantId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Affiliate::where('tenant_id', $tenantId);

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['tier'])) {
            $query->where('tier', $filters['tier']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('affiliate_code', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('total_earnings')->paginate($perPage);
    }

    /**
     * Get a single affiliate by ID.
     */
    public function getAffiliateById(int $affiliateId, int $tenantId): ?Affiliate
    {
        return Affiliate::where('id', $affiliateId)
            ->where('tenant_id', $tenantId)
            ->with(['referrals', 'commissions'])
            ->first();
    }

    /**
     * Get affiliate by code.
     */
    public function getAffiliateByCode(string $code, int $tenantId): ?Affiliate
    {
        return Affiliate::where('affiliate_code', $code)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Create a new affiliate application.
     */
    public function createApplication(array $data): Affiliate
    {
        $data['affiliate_code'] = Affiliate::generateCode();
        $data['status'] = config('affiliate.referral.require_approval', false) ? 'pending' : 'active';
        $data['tier'] = 'standard';
        $data['commission_rate'] = config('affiliate.tiers.standard.commission_rate', 10.0);
        $data['total_earnings'] = 0;
        $data['total_commissions'] = 0;
        $data['total_payouts'] = 0;
        $data['pending_balance'] = 0;
        $data['approved_commissions'] = 0;
        $data['rejected_commissions'] = 0;
        $data['total_clicks'] = 0;
        $data['total_conversions'] = 0;

        $affiliate = Affiliate::create($data);

        // Generate default referral code
        app(ReferralService::class)->createDefaultCode($affiliate->id, $tenantId);

        return $affiliate;
    }

    /**
     * Update affiliate details.
     */
    public function updateAffiliate(Affiliate $affiliate, array $data): Affiliate
    {
        unset($data['affiliate_code']); // Cannot change code
        unset($data['tenant_id']);       // Cannot change tenant
        
        $affiliate->update($data);
        return $affiliate;
    }

    /**
     * Approve an affiliate application.
     */
    public function approveApplication(Affiliate $affiliate, int $userId): Affiliate
    {
        $affiliate->activate();
        return $affiliate;
    }

    /**
     * Reject an affiliate application.
     */
    public function rejectApplication(Affiliate $affiliate, string $reason): Affiliate
    {
        $affiliate->reject($reason);
        return $affiliate;
    }

    /**
     * Activate an affiliate account.
     */
    public function activate(Affiliate $affiliate): Affiliate
    {
        $affiliate->activate();
        return $affiliate;
    }

    /**
     * Deactivate an affiliate account.
     */
    public function deactivate(Affiliate $affiliate): Affiliate
    {
        $affiliate->deactivate();
        return $affiliate;
    }

    /**
     * Evaluate tier upgrades for all affiliates.
     */
    public function evaluateTiers(int $tenantId): array
    {
        $affiliates = Affiliate::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();

        $upgraded = 0;
        $demoted = 0;

        foreach ($affiliates as $affiliate) {
            $newTier = $affiliate->evaluateTier();
            
            if ($newTier && $newTier !== $affiliate->tier) {
                $affiliate->promoteToTier($newTier);
                $upgraded++;
            }
        }

        return [
            'total_evaluated' => $affiliates->count(),
            'upgraded' => $upgraded,
            'demoted' => $demoted,
        ];
    }

    /**
     * Bulk evaluate tiers (admin action).
     */
    public function bulkEvaluateTiers(int $tenantId): array
    {
        return $this->evaluateTiers($tenantId);
    }

    /**
     * Get affiliate statistics.
     */
    public function getAffiliateStats(int $tenantId): array
    {
        $stats = Affiliate::where('tenant_id', $tenantId)
            ->selectRaw('
                COUNT(*) as total_affiliates,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_affiliates,
                SUM(total_earnings) as total_earnings,
                AVG(total_earnings) as avg_earnings,
                SUM(total_clicks) as total_clicks,
                SUM(total_conversions) as total_conversions
            ')
            ->first();

        // Affiliates by tier
        $byTier = Affiliate::where('tenant_id', $tenantId)
            ->selectRaw('tier, COUNT(*) as count')
            ->groupBy('tier')
            ->get();

        return [
            'total' => (int) $stats->total_affiliates ?? 0,
            'active' => (int) $stats->active_affiliates ?? 0,
            'total_earnings' => (float) $stats->total_earnings ?? 0,
            'avg_earnings' => (float) $stats->avg_earnings ?? 0,
            'total_clicks' => (int) $stats->total_clicks ?? 0,
            'total_conversions' => (int) $stats->total_conversions ?? 0,
            'by_tier' => $byTier,
        ];
    }
}
