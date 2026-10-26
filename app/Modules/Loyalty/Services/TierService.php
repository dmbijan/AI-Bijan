<?php

namespace App\Modules\Loyalty\Services;

use App\Modules\Loyalty\Models\Tier;
use App\Modules\Loyalty\Models\Member;
use Illuminate\Pagination\LengthAwarePaginator;

class TierService
{
    /**
     * Get all tiers for a tenant with pagination.
     */
    public function getTiers(int $tenantId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Tier::where('tenant_id', $tenantId);

        if (isset($filters['active_only'])) {
            $query->active();
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('min_points')->paginate($perPage);
    }

    /**
     * Get a single tier by ID.
     */
    public function getTierById(int $tierId, int $tenantId): ?Tier
    {
        return Tier::where('id', $tierId)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Create a new tier.
     */
    public function createTier(array $data): Tier
    {
        if (!isset($data['slug']) || empty($data['slug'])) {
            $data['slug'] = Tier::generateSlug($data['name']);
        }

        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        $data['is_default'] = $data['is_default'] ?? false;
        $data['color_hex'] = $data['color_hex'] ?? '#808080';
        $data['icon'] = $data['icon'] ?? 'star';
        $data['point_multiplier'] = $data['point_multiplier'] ?? 1.0;
        $data['benefits'] = $data['benefits'] ?? [];

        // If this is set as default, unset others
        if ($data['is_default']) {
            Tier::where('tenant_id', $data['tenant_id'])
                ->update(['is_default' => false]);
        }

        return Tier::create($data);
    }

    /**
     * Update a tier.
     */
    public function updateTier(Tier $tier, array $data): Tier
    {
        unset($data['slug']); // Cannot change slug
        
        // If this is set as default, unset others
        if (isset($data['is_default']) && $data['is_default']) {
            Tier::where('tenant_id', $tier->tenant_id)
                ->where('id', '!=', $tier->id)
                ->update(['is_default' => false]);
        }

        $tier->update($data);
        return $tier;
    }

    /**
     * Delete a tier.
     */
    public function deleteTier(Tier $tier): void
    {
        // Check if any members are in this tier
        $memberCount = Member::where('tenant_id', $tier->tenant_id)
            ->where('tier_id', $tier->id)
            ->count();

        if ($memberCount > 0) {
            throw new \InvalidArgumentException("Cannot delete tier with {$memberCount} active members");
        }

        $tier->delete();
    }

    /**
     * Evaluate member eligibility for all tiers.
     */
    public function evaluateMember(Member $member): ?Tier
    {
        $currentBalance = $member->current_balance;

        // Find highest tier member qualifies for
        $newTier = Tier::where('tenant_id', $member->tenant_id)
            ->where('is_active', true)
            ->where('min_points', '<=', $currentBalance)
            ->orderByDesc('min_points')
            ->first();

        // If tier changed, update member
        if ($newTier && $newTier->id !== $member->tier_id) {
            $oldTier = $member->tier;
            $member->update(['tier_id' => $newTier->id]);
            
            return [
                'upgraded' => true,
                'old_tier' => $oldTier?->name,
                'new_tier' => $newTier->name,
                'tier' => $newTier,
            ];
        }

        return null;
    }

    /**
     * Promote a member to a specific tier (admin action).
     */
    public function promoteMember(Member $member, Tier $tier): Member
    {
        if ($tier->tenant_id !== $member->tenant_id) {
            throw new \InvalidArgumentException('Tier does not belong to member\'s tenant');
        }

        $member->update(['tier_id' => $tier->id]);
        return $member;
    }

    /**
     * Demote a member to a specific tier (admin action).
     */
    public function demoteMember(Member $member, Tier $tier): Member
    {
        if ($tier->tenant_id !== $member->tenant_id) {
            throw new \InvalidArgumentException('Tier does not belong to member\'s tenant');
        }

        $member->update(['tier_id' => $tier->id]);
        return $member;
    }

    /**
     * Bulk evaluate all members for tier upgrades.
     */
    public function bulkEvaluateMembers(int $tenantId): array
    {
        $members = Member::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get();

        $upgraded = 0;
        $demoted = 0;

        foreach ($members as $member) {
            $result = $this->evaluateMember($member);
            
            if ($result && isset($result['upgraded'])) {
                $upgraded++;
            }
        }

        return [
            'total_evaluated' => $members->count(),
            'upgraded' => $upgraded,
            'demoted' => $demoted,
        ];
    }

    /**
     * Get tier statistics.
     */
    public function getTierStats(int $tenantId): array
    {
        $stats = Tier::where('tenant_id', $tenantId)
            ->selectRaw('
                COUNT(*) as total_tiers,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_tiers
            ')
            ->first();

        // Members per tier
        $membersPerTier = \DB::table('loyalty_tiers')
            ->join('loyalty_members', 'loyalty_tiers.id', '=', 'loyalty_members.tier_id')
            ->where('loyalty_tiers.tenant_id', $tenantId)
            ->selectRaw('loyalty_tiers.name, COUNT(loyalty_members.id) as member_count')
            ->groupBy('loyalty_tiers.name')
            ->orderByDesc('member_count')
            ->get();

        return [
            'total' => $stats->total_tiers ?? 0,
            'active' => $stats->active_tiers ?? 0,
            'members_per_tier' => $membersPerTier,
        ];
    }
}
