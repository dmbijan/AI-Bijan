<?php

namespace App\Modules\Loyalty\Services;

use App\Modules\Loyalty\Models\Reward;
use App\Modules\Loyalty\Models\Redemption;
use App\Modules\Loyalty\Models\Member;
use Illuminate\Pagination\LengthAwarePaginator;

class RewardService
{
    /**
     * Get all rewards for a tenant with pagination.
     */
    public function getRewards(int $tenantId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Reward::where('tenant_id', $tenantId);

        if (isset($filters['active_only'])) {
            $query->active();
        }

        if (isset($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('cost_points')->paginate($perPage);
    }

    /**
     * Get a single reward by ID.
     */
    public function getRewardById(int $rewardId, int $tenantId): ?Reward
    {
        return Reward::where('id', $rewardId)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Create a new reward.
     */
    public function createReward(array $data): Reward
    {
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Reward::generateSlug($data['name']);
        }

        $data['total_redemptions'] = 0;
        $data['is_active'] = $data['is_active'] ?? false;

        return Reward::create($data);
    }

    /**
     * Update a reward.
     */
    public function updateReward(Reward $reward, array $data): Reward
    {
        unset($data['slug']); // Cannot change slug
        unset($data['total_redemptions']); // Managed automatically
        
        $reward->update($data);
        return $reward;
    }

    /**
     * Delete a reward.
     */
    public function deleteReward(Reward $reward): void
    {
        $reward->delete();
    }

    /**
     * Redeem a reward for a member.
     */
    public function redeemReward(int $memberId, int $rewardId, int $tenantId): Redemption
    {
        $member = Member::where('id', $memberId)
            ->where('tenant_id', $tenantId)
            ->first();

        $reward = Reward::where('id', $rewardId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$member || !$reward) {
            throw new \InvalidArgumentException('Member or Reward not found');
        }

        if (!$reward->canRedeem($member->current_balance)) {
            throw new \InvalidArgumentException('Reward cannot be redeemed (insufficient points or reward unavailable)');
        }

        return DB::transaction(function () use ($member, $reward) {
            // Burn points
            app(PointsService::class)->redeemPoints(
                $member->id,
                $member->tenant_id,
                $reward->cost_points,
                'reward',
                $reward->id,
                "Redeemed {$reward->name}"
            );

            // Create redemption record
            $redemption = Redemption::create([
                'tenant_id' => $member->tenant_id,
                'member_id' => $member->id,
                'reward_id' => $reward->id,
                'points_spent' => $reward->cost_points,
                'status' => 'pending',
                'redemption_code' => strtoupper(substr(md5(uniqid()), 0, 12)),
            ]);

            // Increment total redemptions on reward
            $reward->increment('total_redemptions');

            return $redemption;
        });
    }

    /**
     * Fulfill a redemption.
     */
    public function fulfillRedemption(Redemption $redemption): Redemption
    {
        return $redemption->markAsFulfilled();
    }

    /**
     * Cancel a redemption.
     */
    public function cancelRedemption(Redemption $redemption): Redemption
    {
        return $redemption->markAsCancelled();
    }

    /**
     * Check if a member can redeem a specific reward.
     */
    public function canRedeem(int $memberId, int $rewardId, int $tenantId): array
    {
        $member = Member::where('id', $memberId)
            ->where('tenant_id', $tenantId)
            ->first();

        $reward = Reward::where('id', $rewardId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$member || !$reward) {
            return ['can_redeem' => false, 'reason' => 'Member or Reward not found'];
        }

        if (!$reward->canRedeem($member->current_balance)) {
            return [
                'can_redeem' => false,
                'reason' => $member->current_balance < $reward->cost_points 
                    ? 'Insufficient points' 
                    : 'Reward is at maximum redemptions',
            ];
        }

        return [
            'can_redeem' => true,
            'member_balance' => $member->current_balance,
            'reward_cost' => $reward->cost_points,
            'remaining_balance' => $member->current_balance - $reward->cost_points,
        ];
    }

    /**
     * Get reward statistics.
     */
    public function getRewardStats(int $tenantId): array
    {
        $stats = Reward::where('tenant_id', $tenantId)
            ->selectRaw('
                COUNT(*) as total_rewards,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_rewards,
                SUM(total_redemptions) as total_redemptions,
                AVG(cost_points) as avg_cost
            ')
            ->first();

        // Rewards by type
        $byType = Reward::where('tenant_id', $tenantId)
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        return [
            'total' => $stats->total_rewards ?? 0,
            'active' => $stats->active_rewards ?? 0,
            'total_redemptions' => $stats->total_redemptions ?? 0,
            'avg_cost' => (float) $stats->avg_cost ?? 0,
            'by_type' => $byType,
        ];
    }
}
