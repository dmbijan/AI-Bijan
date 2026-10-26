<?php

namespace App\Modules\Affiliate\Services;

use App\Modules\Affiliate\Models\Affiliate;
use App\Modules\Affiliate\Models\Commission;
use App\Modules\Affiliate\Models\Referral;
use App\Modules\Affiliate\Models\AffiliateClick;
use Illuminate\Support\Facades\DB;

class AffiliateAnalyticsService
{
    /**
     * Get overall affiliate program analytics.
     */
    public function getProgramOverview(int $tenantId, array $dateRange = []): array
    {
        $query = Commission::where('tenant_id', $tenantId);

        if (!empty($dateRange)) {
            $query->whereBetween('created_at', [$dateRange['start'] ?? now()->startOfMonth(), $dateRange['end'] ?? now()]);
        }

        $totalCommissions = $query->sum('commission_amount');
        $approvedCommissions = (clone $query)->where('status', 'approved')->sum('net_amount');
        $pendingCommissions = (clone $query)->where('status', 'pending')->sum('net_amount');

        // Calculate ROI
        $totalRevenue = config('affiliate.track_revenue', false) ? 
            (clone $query)->sum('base_value') : 0;

        $roi = $totalRevenue > 0 ? (($totalRevenue - $totalCommissions) / $totalRevenue) * 100 : 0;

        return [
            'total_commissions' => $totalCommissions,
            'approved_commissions' => $approvedCommissions,
            'pending_commissions' => $pendingCommissions,
            'total_revenue' => $totalRevenue,
            'roi' => round($roi, 2),
            'total_affiliates' => Affiliate::where('tenant_id', $tenantId)->count(),
            'active_affiliates' => Affiliate::where('tenant_id', $tenantId)->where('status', 'active')->count(),
            'total_referrals' => Referral::where('tenant_id', $tenantId)->count(),
            'converted_referrals' => Referral::where('tenant_id', $tenantId)->where('status', 'converted')->count(),
            'conversion_rate' => $this->calculateConversionRate($tenantId),
        ];
    }

    /**
     * Get top performing affiliates.
     */
    public function getTopAffiliates(int $tenantId, int $limit = 10, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Affiliate::where('tenant_id', $tenantId)
            ->withCount(['commissions as total_commissions_count' => function ($q) {
                $q->where('status', 'approved');
            }])
            ->withSum(['commissions as total_earnings_sum' => function ($q) {
                $q->where('status', 'approved');
            }], 'net_amount')
            ->withCount(['clicks']);

        if (isset($filters['tier'])) {
            $query->byTier($filters['tier']);
        }

        return $query->orderByDesc('total_earnings_sum')->limit($limit)->get();
    }

    /**
     * Get conversion funnel data.
     */
    public function getConversionFunnel(int $tenantId, array $dateRange = []): array
    {
        $query = AffiliateClick::where('tenant_id', $tenantId);

        if (!empty($dateRange)) {
            $query->whereBetween('created_at', [$dateRange['start'] ?? now()->startOfMonth(), $dateRange['end'] ?? now()]);
        }

        $totalClicks = $query->count();
        $uniqueClicks = $query->distinct('click_id')->count('click_id');
        $convertedClicks = $query->where('converted', true)->count();

        // Click to referral conversion
        $referrals = Referral::where('tenant_id', $tenantId);
        if (!empty($dateRange)) {
            $referrals->whereBetween('created_at', [$dateRange['start'] ?? now()->startOfMonth(), $dateRange['end'] ?? now()]);
        }
        $totalReferrals = $referrals->count();

        return [
            'total_clicks' => $totalClicks,
            'unique_clicks' => $uniqueClicks,
            'converted_clicks' => $convertedClicks,
            'total_referrals' => $totalReferrals,
            'click_to_referral_rate' => $totalClicks > 0 ? round(($totalReferrals / $totalClicks) * 100, 2) : 0,
            'click_conversion_rate' => $totalClicks > 0 ? round(($convertedClicks / $totalClicks) * 100, 2) : 0,
        ];
    }

    /**
     * Get commission trends over time.
     */
    public function getCommissionTrends(int $tenantId, string $period = 'monthly', array $dateRange = []): array
    {
        $query = Commission::where('tenant_id', $tenantId)
            ->where('status', 'approved');

        if (!empty($dateRange)) {
            $query->whereBetween('created_at', [$dateRange['start'] ?? now()->startOfYear(), $dateRange['end'] ?? now()]);
        }

        $groupBy = match($period) {
            'daily' => 'DATE(created_at)',
            'weekly' => 'YEARWEEK(created_at)',
            'monthly' => 'YEAR(created_at), MONTH(created_at)',
            default => 'YEAR(created_at), MONTH(created_at)',
        };

        $trends = $query->selectRaw("{$groupBy} as period, SUM(commission_amount) as total, COUNT(*) as count")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'period' => $period,
            'data' => $trends,
            'totals' => [
                'amount' => $query->sum('commission_amount'),
                'count' => $query->count(),
            ],
        ];
    }

    /**
     * Get payout reports.
     */
    public function getPayoutReport(int $tenantId, array $dateRange = []): array
    {
        $payoutQuery = \App\Modules\Affiliate\Models\AffiliatePayout::where('tenant_id', $tenantId);

        if (!empty($dateRange)) {
            $payoutQuery->whereBetween('processed_at', [$dateRange['start'] ?? now()->startOfMonth(), $dateRange['end'] ?? now()]);
        }

        $totalPayouts = $payoutQuery->sum('amount');
        $pendingPayouts = (clone $payoutQuery)->where('status', 'pending')->sum('amount');
        $processedPayouts = (clone $payoutQuery)->where('status', 'processed')->sum('amount');

        return [
            'total_payouts' => $totalPayouts,
            'pending_payouts' => $pendingPayouts,
            'processed_payouts' => $processedPayouts,
            'payout_count' => $payoutQuery->count(),
        ];
    }

    /**
     * Calculate overall conversion rate.
     */
    private function calculateConversionRate(int $tenantId): float
    {
        $totalReferrals = Referral::where('tenant_id', $tenantId)->count();
        $convertedReferrals = Referral::where('tenant_id', $tenantId)
            ->where('status', 'converted')
            ->count();

        return $totalReferrals > 0 ? round(($convertedReferrals / $totalReferrals) * 100, 2) : 0;
    }

    /**
     * Get affiliate performance comparison.
     */
    public function getPerformanceComparison(int $tenantId, array $affiliateIds): array
    {
        return Affiliate::whereIn('id', $affiliateIds)
            ->where('tenant_id', $tenantId)
            ->withCount([
                'clicks',
                'referrals as converted_count' => function ($q) {
                    $q->where('status', 'converted');
                },
                'commissions as approved_count' => function ($q) {
                    $q->where('status', 'approved');
                },
                'commissions as total_earnings' => function ($q) {
                    $q->where('status', 'approved');
                },
            ])
            ->get()
            ->map(function ($affiliate) {
                return [
                    'id' => $affiliate->id,
                    'name' => $affiliate->first_name . ' ' . $affiliate->last_name,
                    'email' => $affiliate->email,
                    'tier' => $affiliate->tier,
                    'total_clicks' => $affiliate->total_clicks,
                    'total_conversions' => $affiliate->total_conversions,
                    'conversion_rate' => $affiliate->conversion_rate,
                    'total_earnings' => $affiliate->total_earnings,
                    'avg_commission' => $affiliate->approved_commissions > 0 
                        ? $affiliate->total_earnings / $affiliate->approved_commissions 
                        : 0,
                ];
            });
    }
}
