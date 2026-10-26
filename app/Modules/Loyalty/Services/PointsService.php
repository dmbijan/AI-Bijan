<?php

namespace App\Modules\Loyalty\Services;

use App\Modules\Loyalty\Models\Member;
use App\Modules\Loyalty\Models\PointsLedger;
use Illuminate\Support\Facades\DB;

class PointsService
{
    /**
     * Award points to a member.
     */
    public function awardPoints(
        int $memberId,
        int $tenantId,
        string $type,
        int $points,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $description = null,
        ?int $expirationMonths = null
    ): PointsLedger {
        $member = Member::where('id', $memberId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$member) {
            throw new \InvalidArgumentException('Member not found');
        }

        // Apply tier multiplier
        $multiplier = $member->point_multiplier ?? 1.0;
        $adjustedPoints = (int) floor($points * $multiplier);

        return DB::transaction(function () use ($member, $tenantId, $type, $adjustedPoints, $referenceType, $referenceId, $description, $expirationMonths) {
            // Calculate new balance
            $newBalance = $member->current_balance + $adjustedPoints;

            // Create ledger entry
            $ledgerEntry = PointsLedger::create([
                'tenant_id' => $tenantId,
                'member_id' => $member->id,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'points' => $adjustedPoints,
                'balance_after' => $newBalance,
                'type' => $type,
                'description' => $description ?? "{$adjustedPoints} points earned from {$type}",
                'expires_at' => $expirationMonths 
                    ? now()->addMonths($expirationMonths) 
                    : null,
            ]);

            // Update member totals
            $member->increment('current_balance', $adjustedPoints);
            $member->increment('total_points_earned', $adjustedPoints);
            $member->updateActivity();

            return $ledgerEntry;
        });
    }

    /**
     * Redeem/burn points for a reward.
     */
    public function redeemPoints(
        int $memberId,
        int $tenantId,
        int $pointsToRedeem,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?string $description = null
    ): PointsLedger {
        $member = Member::where('id', $memberId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$member) {
            throw new \InvalidArgumentException('Member not found');
        }

        if ($member->current_balance < $pointsToRedeem) {
            throw new \InsufficientFundsException('Not enough points to redeem');
        }

        return DB::transaction(function () use ($member, $tenantId, $pointsToRedeem, $referenceType, $referenceId, $description) {
            // Calculate new balance
            $newBalance = $member->current_balance - $pointsToRedeem;

            // Create ledger entry (negative points)
            $ledgerEntry = PointsLedger::create([
                'tenant_id' => $tenantId,
                'member_id' => $member->id,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'points' => -$pointsToRedeem,
                'balance_after' => $newBalance,
                'type' => 'redemption',
                'description' => $description ?? "{$pointsToRedeem} points redeemed",
            ]);

            // Update member totals
            $member->decrement('current_balance', $pointsToRedeem);
            $member->increment('total_points_redeemed', $pointsToRedeem);

            return $ledgerEntry;
        });
    }

    /**
     * Expire old points.
     */
    public function expirePoints(int $tenantId, ?int $beforeMonths = null): int
    {
        $ttlMonths = $beforeMonths ?? config('loyalty.points.expiration.ttl_months', 24);
        $cutoffDate = now()->subMonths($ttlMonths);

        // Find expired but not yet processed entries
        $expiredEntries = PointsLedger::where('tenant_id', $tenantId)
            ->where('points', '>', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->whereNotIn('type', ['expiration'])
            ->get();

        $totalExpired = 0;

        DB::transaction(function () use ($expiredEntries, &$totalExpired) {
            foreach ($expiredEntries as $entry) {
                $member = Member::find($entry->member_id);
                
                if (!$member) {
                    continue;
                }

                $expiredAmount = $entry->points;
                $totalExpired += $expiredAmount;

                // Create expiration ledger entry
                PointsLedger::create([
                    'tenant_id' => $entry->tenant_id,
                    'member_id' => $member->id,
                    'reference_type' => 'points_ledger',
                    'reference_id' => $entry->id,
                    'points' => -$expiredAmount,
                    'balance_after' => $member->current_balance - $expiredAmount,
                    'type' => 'expiration',
                    'description' => "Points expired ({$entry->description})",
                ]);

                // Update member
                $member->decrement('current_balance', $expiredAmount);
                $member->increment('total_points_expired', $expiredAmount);
            }
        });

        return $totalExpired;
    }

    /**
     * Get member's current balance.
     */
    public function getBalance(int $memberId, int $tenantId): array
    {
        $member = Member::where('id', $memberId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$member) {
            throw new \InvalidArgumentException('Member not found');
        }

        return [
            'member_id' => $member->id,
            'member_code' => $member->member_code,
            'current_balance' => $member->current_balance,
            'total_earned' => $member->total_points_earned,
            'total_redeemed' => $member->total_points_redeemed,
            'total_expired' => $member->total_points_expired,
            'tier_name' => $member->tier_name,
            'point_multiplier' => $member->point_multiplier,
        ];
    }

    /**
     * Get recent ledger entries for a member.
     */
    public function getRecentEntries(int $memberId, int $tenantId, int $limit = 20)
    {
        return PointsLedger::where('member_id', $memberId)
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Transfer points between members.
     */
    public function transferPoints(
        int $fromMemberId,
        int $toMemberId,
        int $tenantId,
        int $points
    ): array {
        $fromMember = Member::where('id', $fromMemberId)
            ->where('tenant_id', $tenantId)
            ->first();

        $toMember = Member::where('id', $toMemberId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$fromMember || !$toMember) {
            throw new \InvalidArgumentException('One or both members not found');
        }

        if ($fromMember->current_balance < $points) {
            throw new \InsufficientFundsException('Sender has insufficient points');
        }

        return DB::transaction(function () use ($fromMember, $toMember, $tenantId, $points) {
            // Deduct from sender
            $this->redeemPoints(
                $fromMember->id,
                $tenantId,
                $points,
                'transfer',
                $toMember->id,
                "Points transferred to {$toMember->member_code}"
            );

            // Add to receiver
            $this->awardPoints(
                $toMember->id,
                $tenantId,
                'referral',
                $points,
                'transfer',
                $fromMember->id,
                "Points received from {$fromMember->member_code}"
            );

            return [
                'from_member' => $fromMember->member_code,
                'to_member' => $toMember->member_code,
                'points' => $points,
            ];
        });
    }

    /**
     * Adjust points manually (admin action).
     */
    public function adjustPoints(
        int $memberId,
        int $tenantId,
        int $points,
        string $reason
    ): PointsLedger {
        $member = Member::where('id', $memberId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$member) {
            throw new \InvalidArgumentException('Member not found');
        }

        return DB::transaction(function () use ($member, $tenantId, $points, $reason) {
            $newBalance = $member->current_balance + $points;

            $ledgerEntry = PointsLedger::create([
                'tenant_id' => $tenantId,
                'member_id' => $member->id,
                'reference_type' => 'admin_adjustment',
                'reference_id' => null,
                'points' => $points,
                'balance_after' => $newBalance,
                'type' => 'adjustment',
                'description' => $reason,
            ]);

            $member->increment('current_balance', $points);

            return $ledgerEntry;
        });
    }
}

class InsufficientFundsException extends \Exception
{
}
