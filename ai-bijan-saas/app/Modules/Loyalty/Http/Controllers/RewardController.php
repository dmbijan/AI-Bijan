<?php

namespace App\Modules\Loyalty\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use App\Modules\Loyalty\Services\RewardService;
use App\Modules\Loyalty\Models\Reward;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RewardController
{
    protected RewardService $rewardService;

    public function __construct(RewardService $rewardService)
    {
        $this->rewardService = $rewardService;
    }

    /**
     * List all rewards.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();

        $rewards = $this->rewardService->getRewards(
            $tenantId,
            $request->only(['active_only', 'type', 'search']),
            $request->input('per_page', 20)
        );

        return response()->json($rewards);
    }

    /**
     * Get a single reward.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();

        $reward = $this->rewardService->getRewardById($id, $tenantId);

        if (!$reward) {
            return response()->json(['message' => 'Reward not found'], 404);
        }

        return response()->json($reward);
    }

    /**
     * Create a new reward.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:loyalty_rewards,slug',
            'description' => 'nullable|string',
            'type' => 'required|in:discount,free_shipping,product,cashback,experience',
            'cost_points' => 'required|integer|min:1',
            'max_redemptions' => 'nullable|integer|min:1',
            'image_url' => 'nullable|string|max:2048',
            'terms_conditions' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = app(TenantContext::class)->id();

        $reward = $this->rewardService->createReward($validated);

        return response()->json($reward, 201);
    }

    /**
     * Update a reward.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $reward = Reward::where('id', $id)->where('tenant_id', $tenantId)->first();

        if (!$reward) {
            return response()->json(['message' => 'Reward not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:discount,free_shipping,product,cashback,experience',
            'cost_points' => 'sometimes|integer|min:1',
            'max_redemptions' => 'nullable|integer|min:1',
            'image_url' => 'nullable|string|max:2048',
            'terms_conditions' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $reward = $this->rewardService->updateReward($reward, $validated);

        return response()->json($reward);
    }

    /**
     * Delete a reward.
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $reward = Reward::where('id', $id)->where('tenant_id', $tenantId)->first();

        if (!$reward) {
            return response()->json(['message' => 'Reward not found'], 404);
        }

        $this->rewardService->deleteReward($reward);

        return response()->json(['message' => 'Reward deleted']);
    }

    /**
     * Redeem a reward for a member.
     */
    public function redeem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:loyalty_members,id',
            'reward_id' => 'required|exists:loyalty_rewards,id',
        ]);

        try {
            $redemption = $this->rewardService->redeemReward(
                $validated['member_id'],
                $validated['reward_id'],
                app(TenantContext::class)->id()
            );

            return response()->json($redemption, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Fulfill a redemption.
     */
    public function fulfill(int $id, Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();

        try {
            $redemption = $this->rewardService->fulfillRedemption($id, $tenantId);
            return response()->json($redemption);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Cancel a redemption.
     */
    public function cancel(int $id, Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();

        try {
            $redemption = $this->rewardService->cancelRedemption($id, $tenantId);
            return response()->json($redemption);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Check if a member can redeem a specific reward.
     */
    public function checkEligibility(int $memberId, int $rewardId, Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();

        $eligibility = $this->rewardService->canRedeem($memberId, $rewardId, $tenantId);

        return response()->json($eligibility);
    }

    /**
     * Get reward statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $stats = $this->rewardService->getRewardStats($tenantId);

        return response()->json($stats);
    }
}
