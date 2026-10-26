<?php

namespace App\Modules\Loyalty\Http\Controllers;

use App\Modules\Loyalty\Services\PointsService;
use App\Modules\Loyalty\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointsController
{
    protected PointsService $pointsService;

    public function __construct(PointsService $pointsService)
    {
        $this->pointsService = $pointsService;
    }

    /**
     * Get member's current balance.
     */
    public function getBalance(int $memberId, Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        try {
            $balance = $this->pointsService->getBalance($memberId, $tenantId);
            return response()->json($balance);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    /**
     * Get recent ledger entries for a member.
     */
    public function getRecentEntries(int $memberId, Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $limit = $request->input('limit', 20);
        
        $entries = $this->pointsService->getRecentEntries($memberId, $tenantId, $limit);
        
        return response()->json($entries);
    }

    /**
     * Award points to a member.
     */
    public function award(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:loyalty_members,id',
            'type' => 'required|string|in:purchase,signup_bonus,birthday_bonus,referral_bonus,review_bonus,social_share_bonus,manual',
            'points' => 'required|integer|min:1',
            'reference_type' => 'nullable|string|max:255',
            'reference_id' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'expiration_months' => 'nullable|integer|min:1',
        ]);

        try {
            $ledgerEntry = $this->pointsService->awardPoints(
                $validated['member_id'],
                app(TenantContext::class)->id(),
                $validated['type'],
                $validated['points'],
                $validated['reference_type'] ?? null,
                $validated['reference_id'] ?? null,
                $validated['description'] ?? null,
                $validated['expiration_months'] ?? null
            );

            return response()->json($ledgerEntry, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Redeem/burn points.
     */
    public function redeem(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:loyalty_members,id',
            'points' => 'required|integer|min:1',
            'reference_type' => 'nullable|string|max:255',
            'reference_id' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $ledgerEntry = $this->pointsService->redeemPoints(
                $validated['member_id'],
                app(TenantContext::class)->id(),
                $validated['points'],
                $validated['reference_type'] ?? null,
                $validated['reference_id'] ?? null,
                $validated['description'] ?? null
            );

            return response()->json($ledgerEntry, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\App\Modules\Loyalty\Services\InsufficientFundsException $e) {
            return response()->json(['message' => $e->getMessage()], 402);
        }
    }

    /**
     * Transfer points between members.
     */
    public function transfer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_member_id' => 'required|exists:loyalty_members,id',
            'to_member_id' => 'required|exists:loyalty_members,id',
            'points' => 'required|integer|min:1',
        ]);

        try {
            $result = $this->pointsService->transferPoints(
                $validated['from_member_id'],
                $validated['to_member_id'],
                app(TenantContext::class)->id(),
                $validated['points']
            );

            return response()->json($result, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\App\Modules\Loyalty\Services\InsufficientFundsException $e) {
            return response()->json(['message' => $e->getMessage()], 402);
        }
    }

    /**
     * Adjust points manually (admin action).
     */
    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:loyalty_members,id',
            'points' => 'required|integer',
            'reason' => 'required|string',
        ]);

        try {
            $ledgerEntry = $this->pointsService->adjustPoints(
                $validated['member_id'],
                app(TenantContext::class)->id(),
                $validated['points'],
                $validated['reason']
            );

            return response()->json($ledgerEntry, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Expire old points (admin/cron action).
     */
    public function expire(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'before_months' => 'nullable|integer|min:1',
        ]);

        $totalExpired = $this->pointsService->expirePoints(
            app(TenantContext::class)->id(),
            $validated['before_months'] ?? null
        );

        return response()->json([
            'message' => "{$totalExpired} points expired",
            'total_expired' => $totalExpired,
        ]);
    }
}
