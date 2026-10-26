<?php

namespace App\Modules\Affiliate\Services;

use App\Modules\Affiliate\Models\Commission;
use App\Modules\Affiliate\Models\AffiliatePayout;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    /**
     * Calculate commission amount.
     */
    public function calculateCommission(float $value, ?string $type = null): float
    {
        $rate = config('affiliate.tiers.standard.commission_rate', 10.0);

        // Override by type if specified
        if ($type && isset(config('affiliate.tiers.custom_commission_types')[$type])) {
            $rate = config('affiliate.tiers.custom_commission_types.' . $type, $rate);
        }

        return ($value * $rate) / 100;
    }

    /**
     * Create a commission record.
     */
    public function createCommission(int $tenantId, int $affiliateId, int $referralCodeId, float $value, string $type, array $data = []): Commission
    {
        $amount = $this->calculateCommission($value, $type);

        return DB::transaction(function () use ($tenantId, $affiliateId, $referralCodeId, $value, $amount, $type, $data) {
            $commission = Commission::create([
                'tenant_id' => $tenantId,
                'affiliate_id' => $affiliateId,
                'referral_code_id' => $referralCodeId,
                'commission_code' => Commission::generateCode(),
                'referral_id' => $data['referral_id'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'invoice_id' => $data['invoice_id'] ?? null,
                'contact_id' => $data['contact_id'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'commission_type' => $type,
                'base_value' => $value,
                'commission_rate' => config('affiliate.tiers.standard.commission_rate', 10.0),
                'commission_amount' => $amount,
                'tax_amount' => 0,
                'net_amount' => $amount,
                'status' => 'pending',
                'currency' => config('affiliate.currency', 'MYR'),
                'description' => $data['description'] ?? null,
            ]);

            // Update affiliate stats
            $affiliate = \App\Modules\Affiliate\Models\Affiliate::find($affiliateId);
            if ($affiliate) {
                $affiliate->increment('total_commissions');
                $affiliate->increment('pending_balance', $amount);
            }

            return $commission;
        });
    }

    /**
     * Approve a commission.
     */
    public function approveCommission(Commission $commission, int $userId): Commission
    {
        return DB::transaction(function () use ($commission, $userId) {
            $commission->approve($userId);

            // Update affiliate pending balance to approved
            $affiliate = $commission->affiliate;
            if ($affiliate) {
                $affiliate->decrement('pending_balance', $commission->net_amount);
                $affiliate->increment('approved_commissions');
                $affiliate->increment('total_earnings', $commission->net_amount);
            }

            return $commission;
        });
    }

    /**
     * Reject a commission.
     */
    public function rejectCommission(Commission $commission, string $reason, int $userId): Commission
    {
        return DB::transaction(function () use ($commission, $reason, $userId) {
            $commission->reject($reason, $userId);

            // Update affiliate stats
            $affiliate = $commission->affiliate;
            if ($affiliate) {
                $affiliate->decrement('pending_balance', $commission->net_amount);
                $affiliate->increment('rejected_commissions');
            }

            return $commission;
        });
    }

    /**
     * Get commissions with filters.
     */
    public function getCommissions(int $tenantId, array $filters = [], int $perPage = 20)
    {
        $query = Commission::where('tenant_id', $tenantId);

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['type'])) {
            $query->where('commission_type', $filters['type']);
        }

        if (isset($filters['affiliate_id'])) {
            $query->where('affiliate_id', $filters['affiliate_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Process payout for approved commissions.
     */
    public function processPayout(int $affiliateId, int $tenantId, array $payoutData = []): AffiliatePayout
    {
        return DB::transaction(function () use ($affiliateId, $tenantId, $payoutData) {
            // Get all unpaid commissions for this affiliate
            $commissions = Commission::where('affiliate_id', $affiliateId)
                ->where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->whereNull('payout_id')
                ->get();

            if ($commissions->isEmpty()) {
                throw new \InvalidArgumentException('No approved commissions to payout');
            }

            $totalAmount = $commissions->sum('net_amount');

            // Create payout record
            $payout = AffiliatePayout::create([
                'tenant_id' => $tenantId,
                'affiliate_id' => $affiliateId,
                'payout_code' => AffiliatePayout::generateCode(),
                'amount' => $totalAmount,
                'currency' => config('affiliate.currency', 'MYR'),
                'payment_method' => $payoutData['payment_method'] ?? 'bank_transfer',
                'payment_details' => $payoutData['payment_details'] ?? [],
                'status' => 'pending',
            ]);

            // Link commissions to payout
            foreach ($commissions as $commission) {
                $commission->markAsPaid($payout->id);
            }

            return $payout;
        });
    }

    /**
     * Get payout summary for an affiliate.
     */
    public function getPayoutSummary(int $affiliateId, int $tenantId): array
    {
        $totalEarned = Commission::where('affiliate_id', $affiliateId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->sum('net_amount');

        $totalPaid = Commission::where('affiliate_id', $affiliateId)
            ->where('tenant_id', $tenantId)
            ->whereNotNull('payout_id')
            ->sum('net_amount');

        $pendingBalance = Commission::where('affiliate_id', $affiliateId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->sum('net_amount');

        return [
            'total_earned' => $totalEarned,
            'total_paid' => $totalPaid,
            'pending_balance' => $pendingBalance,
            'available_for_payout' => $pendingBalance,
        ];
    }
}
