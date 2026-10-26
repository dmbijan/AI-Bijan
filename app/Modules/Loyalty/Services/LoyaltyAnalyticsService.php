<?php

namespace App\Modules\Loyalty\Services;

use App\Modules\Loyalty\Models\Member;
use App\Modules\Loyalty\Models\PointsLedger;
use App\Modules\Loyalty\Models\Redemption;
use Illuminate\Support\Facades\DB;

class LoyaltyAnalyticsService
{
    /**
     * Get overall loyalty program analytics.
     */
    public function getAnalytics(int $tenantId, ?string $fromDate = null, ?string $toDate = null): array
    {
        // Build date filter
        $dateQuery = function ($query) use ($fromDate, $toDate) {
            if ($fromDate) {
                $query->where('created_at', '>=', $fromDate);
            }
            if ($toDate) {
                $query->where('created_at', '<=', $toDate);
            }
        };

        // Member metrics
        $memberMetrics = Member::where('tenant_id', $tenantId)
            ->when($fromDate || $toDate, function ($query) use ($fromDate, $toDate) {
                if ($fromDate && $toDate) {
                    $query->where(function ($q) use ($fromDate, $toDate) {
                        $q->where('created_at', '>=', $fromDate)
                          ->where('created_at', '<=', $toDate);
                    });
                } elseif ($fromDate) {
                    $query->where('created_at', '>=', $fromDate);
                } elseif ($toDate) {
                    $query->where('created_at', '<=', $toDate);
                }
            })
            ->selectRaw('
                COUNT(*) as total_members,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_members,
                AVG(current_balance) as avg_balance,
                AVG(lifetime_value) as avg_ltv,
                SUM(lifetime_value) as total_ltv
            ')
            ->first();

        // Points metrics
        $pointsMetrics = PointsLedger::where('tenant_id', $tenantId)
            ->when($fromDate || $toDate, $dateQuery)
            ->selectRaw('
                SUM(CASE WHEN points > 0 THEN points ELSE 0 END) as total_earned,
                SUM(CASE WHEN points < 0 THEN ABS(points) ELSE 0 END) as total_redeemed,
                SUM(points) as net_points,
                COUNT(*) as total_transactions
            ')
            ->first();

        // Redemption metrics
        $redemptionMetrics = Redemption::where('tenant_id', $tenantId)
            ->when($fromDate || $toDate, function ($query) use ($fromDate, $toDate) {
                if ($fromDate && $toDate) {
                    $query->where(function ($q) use ($fromDate, $toDate) {
                        $q->where('created_at', '>=', $fromDate)
                          ->where('created_at', '<=', $toDate);
                    });
                } elseif ($fromDate) {
                    $query->where('created_at', '>=', $fromDate);
                } elseif ($toDate) {
                    $query->where('created_at', '<=', $toDate);
                }
            })
            ->selectRaw('
                COUNT(*) as total_redemptions,
                SUM(points_spent) as total_points_burned,
                SUM(CASE WHEN status = "fulfilled" THEN 1 ELSE 0 END) as fulfilled,
                SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending
            ')
            ->first();

        // ROI calculation: (LTV from loyalty - program_cost) / program_cost
        $programCost = config('loyalty.analytics.program_cost_estimate', 0);
        $totalLTV = (float) $memberMetrics->total_ltv ?? 0;
        $roi = $programCost > 0 
            ? (($totalLTV - $programCost) / $programCost) * 100 
            : 0;

        return [
            'members' => [
                'total' => (int) $memberMetrics->total_members ?? 0,
                'active' => (int) $memberMetrics->active_members ?? 0,
                'avg_balance' => (float) $memberMetrics->avg_balance ?? 0,
                'avg_ltv' => (float) $memberMetrics->avg_ltv ?? 0,
                'total_ltv' => $totalLTV,
            ],
            'points' => [
                'total_earned' => (int) $pointsMetrics->total_earned ?? 0,
                'total_redeemed' => (int) $pointsMetrics->total_redeemed ?? 0,
                'net_points' => (int) $pointsMetrics->net_points ?? 0,
                'total_transactions' => (int) $pointsMetrics->total_transactions ?? 0,
            ],
            'redemptions' => [
                'total' => (int) $redemptionMetrics->total_redemptions ?? 0,
                'points_burned' => (int) $redemptionMetrics->total_points_burned ?? 0,
                'fulfilled' => (int) $redemptionMetrics->fulfilled ?? 0,
                'pending' => (int) $redemptionMetrics->pending ?? 0,
            ],
            'roi_percentage' => round($roi, 2),
            'point_value' => config('loyalty.points.burning.point_value', 0.01),
            'burn_rate' => $pointsMetrics->total_earned > 0
                ? ((float) $pointsMetrics->total_redeemed / (float) $pointsMetrics->total_earned) * 100
                : 0,
        ];
    }

    /**
     * Get member segmentation data.
     */
    public function getSegmentation(int $tenantId): array
    {
        // RFM-like segmentation based on points
        $segments = \DB::table('loyalty_members')
            ->join('loyalty_tiers', 'loyalty_members.tier_id', '=', 'loyalty_tiers.id')
            ->where('loyalty_members.tenant_id', $tenantId)
            ->selectRaw('
                loyalty_tiers.name as tier,
                loyalty_members.status,
                COUNT(*) as count,
                AVG(loyalty_members.lifetime_value) as avg_ltv,
                AVG(loyalty_members.current_balance) as avg_balance
            ')
            ->groupBy('loyalty_tiers.name', 'loyalty_members.status')
            ->get();

        // High value members (top 20%)
        $highValueCount = Member::where('tenant_id', $tenantId)
            ->where('lifetime_value', '>', function ($query) use ($tenantId) {
                $query->selectRaw('PERCENTILE_CONT(0.8) WITHIN GROUP (ORDER BY lifetime_value)')
                    ->from('loyalty_members')
                    ->where('tenant_id', $tenantId);
            })
            ->count();

        // At risk members (inactive for 6+ months)
        $atRiskCount = Member::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('last_activity_date', '<', now()->subMonths(6))
            ->count();

        return [
            'total_members' => Member::where('tenant_id', $tenantId)->count(),
            'high_value_count' => $highValueCount,
            'at_risk_count' => $atRiskCount,
            'segmentation_by_tier' => $segments,
        ];
    }

    /**
     * Calculate member LTV trends.
     */
    public function getLTVTrends(int $tenantId, int $months = 12): array
    {
        $trends = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $monthStart = now()->startOfMonth()->subMonths($i);
            $monthEnd = $monthStart->copy()->addMonth()->startOfMonth()->subDay();

            $monthlyLTV = Member::where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->avg('lifetime_value');

            $newMembers = Member::where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $churnedMembers = Member::where('tenant_id', $tenantId)
                ->where('status', 'inactive')
                ->where(function ($query) use ($monthStart, $monthEnd) {
                    $query->whereBetween('updated_at', [$monthStart, $monthEnd]);
                })
                ->count();

            $trends[] = [
                'month' => $monthStart->format('Y-m'),
                'ltv' => (float) $monthlyLTV ?? 0,
                'new_members' => $newMembers,
                'churned' => $churnedMembers,
            ];
        }

        return $trends;
    }

    /**
     * Get churn prediction metrics.
     */
    public function getChurnMetrics(int $tenantId): array
    {
        // Members who haven't earned or redeemed in 3 months
        $inactiveThreshold = now()->subMonths(3);

        $noActivity = Member::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where(function ($query) use ($inactiveThreshold) {
                $query->whereDoesntHave('ledgerEntries', function ($q) use ($inactiveThreshold) {
                    $q->where('created_at', '>=', $inactiveThreshold);
                });
            })
            ->count();

        // Churn rate by tier
        $churnByTier = \DB::table('loyalty_tiers')
            ->leftJoin('loyalty_members', 'loyalty_tiers.id', '=', 'loyalty_members.tier_id')
            ->where('loyalty_tiers.tenant_id', $tenantId)
            ->selectRaw('
                loyalty_tiers.name,
                COUNT(loyalty_members.id) as total,
                SUM(CASE WHEN loyalty_members.status = "inactive" THEN 1 ELSE 0 END) as churned
            ')
            ->groupBy('loyalty_tiers.name')
            ->get();

        return [
            'inactive_3_months' => $noActivity,
            'churn_rate_percentage' => 0, // Would need historical data for accurate calculation
            'by_tier' => $churnByTier,
        ];
    }

    /**
     * Get points liability report.
     */
    public function getPointsLiability(int $tenantId): array
    {
        $totalOutstanding = PointsLedger::where('tenant_id', $tenantId)
            ->where('points', '>', 0)
            ->sum('points');

        $expiringSoon = PointsLedger::where('tenant_id', $tenantId)
            ->where('points', '>', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now()->addMonths(3))
            ->sum('points');

        $pointValue = config('loyalty.points.burning.point_value', 0.01);

        return [
            'total_outstanding_points' => (int) $totalOutstanding,
            'expiring_within_3_months' => (int) $expiringSoon,
            'point_monetary_value' => $pointValue,
            'total_liability_rm' => round($totalOutstanding * $pointValue, 2),
            'expiring_liability_rm' => round($expiringSoon * $pointValue, 2),
        ];
    }
}
