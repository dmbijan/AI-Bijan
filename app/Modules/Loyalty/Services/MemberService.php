<?php

namespace App\Modules\Loyalty\Services;

use App\Modules\Loyalty\Models\Member;
use App\Modules\Loyalty\Models\Tier;
use Illuminate\Pagination\LengthAwarePaginator;

class MemberService
{
    /**
     * Get all members for a tenant with pagination.
     */
    public function getMembers(int $tenantId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = Member::where('tenant_id', $tenantId)->with(['tier']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['tier_id'])) {
            $query->byTier($filters['tier_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('member_code', 'like', "%{$search}%");
            });
        }

        if (isset($filters['high_value'])) {
            $query->highValue();
        }

        return $query->orderByDesc('current_balance')->paginate($perPage);
    }

    /**
     * Get a single member by ID.
     */
    public function getMemberById(int $memberId, int $tenantId): ?Member
    {
        return Member::where('id', $memberId)
            ->where('tenant_id', $tenantId)
            ->with(['tier', 'ledgerEntries'])
            ->first();
    }

    /**
     * Get member by code.
     */
    public function getMemberByCode(string $code, int $tenantId): ?Member
    {
        return Member::where('member_code', $code)
            ->where('tenant_id', $tenantId)
            ->with(['tier'])
            ->first();
    }

    /**
     * Create a new member.
     */
    public function createMember(array $data): Member
    {
        $data['member_code'] = Member::generateCode();
        
        // Assign default tier if not specified
        if (!isset($data['tier_id'])) {
            $defaultTier = Tier::where('tenant_id', $data['tenant_id'])
                ->where('is_default', true)
                ->value('id');
            
            if ($defaultTier) {
                $data['tier_id'] = $defaultTier;
            }
        }

        $data['status'] = 'active';
        $data['current_balance'] = 0;
        $data['total_points_earned'] = 0;
        $data['total_points_redeemed'] = 0;
        $data['total_points_expired'] = 0;

        $member = Member::create($data);

        // Award signup bonus if configured
        $signupBonus = config('loyalty.points.earning_rules.signup_bonus', 0);
        if ($signupBonus > 0) {
            app(PointsService::class)->awardPoints(
                $member->id,
                $data['tenant_id'],
                'signup_bonus',
                $signupBonus,
                null,
                'Welcome bonus for joining loyalty program'
            );
        }

        return $member;
    }

    /**
     * Update member details.
     */
    public function updateMember(Member $member, array $data): Member
    {
        unset($data['member_code']); // Cannot change code
        unset($data['tenant_id']);   // Cannot change tenant
        
        $member->update($data);
        return $member;
    }

    /**
     * Enroll a CRM contact as a loyalty member.
     */
    public function enrollFromContact(int $contactId, int $tenantId): Member
    {
        $crmContact = \App\Modules\CRM\Models\Contact::find($contactId);
        
        if (!$crmContact || $crmContact->tenant_id !== $tenantId) {
            throw new \InvalidArgumentException('Contact not found or belongs to different tenant');
        }

        // Check if already a member
        $existing = Member::where('tenant_id', $tenantId)
            ->where('crm_contact_id', $contactId)
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->createMember([
            'tenant_id' => $tenantId,
            'crm_contact_id' => $contactId,
            'first_name' => $crmContact->first_name,
            'last_name' => $crmContact->last_name,
            'email' => $crmContact->email,
            'phone' => $crmContact->phone,
        ]);
    }

    /**
     * Deactivate a member.
     */
    public function deactivateMember(Member $member): void
    {
        $member->update(['status' => 'inactive']);
    }

    /**
     * Reactivate a member.
     */
    public function reactivateMember(Member $member): void
    {
        $member->update(['status' => 'active']);
    }

    /**
     * Update member tier based on current points balance.
     */
    public function updateMemberTier(Member $member): ?Tier
    {
        $newTier = Tier::where('tenant_id', $member->tenant_id)
            ->where('is_active', true)
            ->where('min_points', '<=', $member->current_balance)
            ->orderByDesc('min_points')
            ->first();

        if (!$newTier || $newTier->id === $member->tier_id) {
            return null;
        }

        $member->update(['tier_id' => $newTier->id]);
        return $newTier;
    }

    /**
     * Get member statistics.
     */
    public function getMemberStats(int $tenantId): array
    {
        $stats = Member::where('tenant_id', $tenantId)
            ->selectRaw('
                COUNT(*) as total_members,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_members,
                AVG(current_balance) as avg_balance,
                SUM(lifetime_value) as total_ltv,
                AVG(lifetime_value) as avg_ltv
            ')
            ->first();

        // Members by tier
        $tierBreakdown = Member::where('tenant_id', $tenantId)
            ->join('loyalty_tiers', 'loyalty_members.tier_id', '=', 'loyalty_tiers.id')
            ->selectRaw('loyalty_tiers.name, COUNT(*) as count')
            ->groupBy('loyalty_tiers.name')
            ->get();

        return [
            'total' => $stats->total_members ?? 0,
            'active' => $stats->active_members ?? 0,
            'avg_balance' => (float) $stats->avg_balance ?? 0,
            'total_ltv' => (float) $stats->total_ltv ?? 0,
            'avg_ltv' => (float) $stats->avg_ltv ?? 0,
            'tier_breakdown' => $tierBreakdown,
        ];
    }
}
