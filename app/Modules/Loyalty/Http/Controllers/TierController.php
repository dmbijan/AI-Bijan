<?php

namespace App\Modules\Loyalty\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Modules\Loyalty\Services\TierService;
use App\Modules\Loyalty\Models\Tier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TierController
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$request->tenant_id` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected TierService $tierService;

    public function __construct(TierService $tierService)
    {
        $this->tierService = $tierService;
    }

    /**
     * List all tiers.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();

        $tiers = $this->tierService->getTiers(
            $tenantId,
            $request->only(['active_only', 'search']),
            $request->input('per_page', 20)
        );

        return response()->json($tiers);
    }

    /**
     * Get a single tier.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();

        $tier = $this->tierService->getTierById($id, $tenantId);

        if (!$tier) {
            return response()->json(['message' => 'Tier not found'], 404);
        }

        return response()->json($tier);
    }

    /**
     * Create a new tier.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:loyalty_tiers,name',
            'slug' => 'nullable|string|max:255|unique:loyalty_tiers,slug',
            'description' => 'nullable|string',
            'min_points' => 'required|integer|min:0',
            'benefits' => 'nullable|array',
            'color_hex' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
            'point_multiplier' => 'required|numeric|min:1.0|max:5.0',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $validated['tenant_id'] = app(TenantContext::class)->id();

        $tier = $this->tierService->createTier($validated);

        return response()->json($tier, 201);
    }

    /**
     * Update a tier.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $tier = Tier::where('id', $id)->where('tenant_id', $tenantId)->first();

        if (!$tier) {
            return response()->json(['message' => 'Tier not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:loyalty_tiers,name,' . $id,
            'description' => 'nullable|string',
            'min_points' => 'sometimes|integer|min:0',
            'benefits' => 'nullable|array',
            'color_hex' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'nullable|string|max:50',
            'point_multiplier' => 'sometimes|numeric|min:1.0|max:5.0',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $tier = $this->tierService->updateTier($tier, $validated);

        return response()->json($tier);
    }

    /**
     * Delete a tier.
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $tier = Tier::where('id', $id)->where('tenant_id', $tenantId)->first();

        if (!$tier) {
            return response()->json(['message' => 'Tier not found'], 404);
        }

        try {
            $this->tierService->deleteTier($tier);
            return response()->json(['message' => 'Tier deleted']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Promote a member to a specific tier (admin action).
     */
    public function promoteMember(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:loyalty_members,id',
            'tier_id' => 'required|exists:loyalty_tiers,id',
        ]);

        try {
            $member = \App\Modules\Loyalty\Models\Member::find($validated['member_id']);
            $tier = Tier::find($validated['tier_id']);

            if ($member->tenant_id !== app(TenantContext::class)->id() || $tier->tenant_id !== app(TenantContext::class)->id()) {
                return response()->json(['message' => 'Member or Tier does not belong to your tenant'], 403);
            }

            $member = $this->tierService->promoteMember($member, $tier);

            return response()->json([
                'message' => "Member promoted to {$tier->name}",
                'member' => $member,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Demote a member from a specific tier (admin action).
     */
    public function demoteMember(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:loyalty_members,id',
            'tier_id' => 'required|exists:loyalty_tiers,id',
        ]);

        try {
            $member = \App\Modules\Loyalty\Models\Member::find($validated['member_id']);
            $tier = Tier::find($validated['tier_id']);

            if ($member->tenant_id !== app(TenantContext::class)->id() || $tier->tenant_id !== app(TenantContext::class)->id()) {
                return response()->json(['message' => 'Member or Tier does not belong to your tenant'], 403);
            }

            $member = $this->tierService->demoteMember($member, $tier);

            return response()->json([
                'message' => "Member demoted to {$tier->name}",
                'member' => $member,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Evaluate all members for tier upgrades (admin/cron action).
     */
    public function bulkEvaluate(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();

        $result = $this->tierService->bulkEvaluateMembers($tenantId);

        return response()->json($result);
    }

    /**
     * Get tier statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $stats = $this->tierService->getTierStats($tenantId);

        return response()->json($stats);
    }
}
