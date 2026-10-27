<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Marketing\Services\DealService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DealController extends Controller
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
    protected DealService $dealService;

    public function __construct(DealService $dealService)
    {
        $this->dealService = $dealService;
    }

    /**
     * List all deals.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $filters = $request->only(['stage', 'active_only', 'contact_id', 'campaign_id']);

        $deals = $this->dealService->getDeals($tenantId, $filters);

        return response()->json([
            'success' => true,
            'data' => $deals,
        ]);
    }

    /**
     * Get a single deal.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $deal = $this->dealService->getDealById($id, $tenantId);

        if (!$deal) {
            return response()->json([
                'success' => false,
                'message' => 'Deal not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $deal,
        ]);
    }

    /**
     * Create a new deal.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_id' => 'required|integer',
            'campaign_id' => 'nullable|integer',
            'value' => 'required|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'properties' => 'nullable|array',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $data = $request->all();
        $data['tenant_id'] = $tenantId;

        $deal = $this->dealService->createDeal($data);

        return response()->json([
            'success' => true,
            'data' => $deal,
            'message' => 'Deal created successfully.',
        ], 201);
    }

    /**
     * Update a deal.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $deal = $this->dealService->getDealById($id, $tenantId);

        if (!$deal) {
            return response()->json([
                'success' => false,
                'message' => 'Deal not found.',
            ], 404);
        }

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'stage' => 'sometimes|in:prospect,qualified,proposal,negotiation,closed_won,closed_lost',
            'value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'lost_reason' => 'nullable|string',
            'properties' => 'nullable|array',
        ]);

        $deal = $this->dealService->updateDeal($deal, $request->all());

        return response()->json([
            'success' => true,
            'data' => $deal,
            'message' => 'Deal updated successfully.',
        ]);
    }

    /**
     * Delete a deal.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $deal = $this->dealService->getDealById($id, $tenantId);

        if (!$deal) {
            return response()->json([
                'success' => false,
                'message' => 'Deal not found.',
            ], 404);
        }

        $this->dealService->deleteDeal($deal);

        return response()->json([
            'success' => true,
            'message' => 'Deal deleted successfully.',
        ]);
    }

    /**
     * Move deal to next stage.
     */
    public function moveStage(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'stage' => 'required|in:prospect,qualified,proposal,negotiation,closed_won,closed_lost',
            'lost_reason' => 'nullable|string',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $deal = $this->dealService->getDealById($id, $tenantId);

        if (!$deal) {
            return response()->json([
                'success' => false,
                'message' => 'Deal not found.',
            ], 404);
        }

        try {
            $deal = $this->dealService->moveStage($deal, $request->stage);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $deal,
            'message' => 'Deal stage updated.',
        ]);
    }

    /**
     * Add activity to deal.
     */
    public function addActivity(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'type' => 'required|string',
            'description' => 'required|string',
            'value' => 'nullable|numeric|min:0',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $deal = $this->dealService->getDealById($id, $tenantId);

        if (!$deal) {
            return response()->json([
                'success' => false,
                'message' => 'Deal not found.',
            ], 404);
        }

        $activity = $this->dealService->addActivity(
            $deal,
            $request->type,
            $request->description,
            $request->value ?? 0
        );

        return response()->json([
            'success' => true,
            'data' => $activity,
            'message' => 'Activity added.',
        ]);
    }

    /**
     * Get deal activities.
     */
    public function getActivities(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $deal = $this->dealService->getDealById($id, $tenantId);

        if (!$deal) {
            return response()->json([
                'success' => false,
                'message' => 'Deal not found.',
            ], 404);
        }

        $activities = $this->dealService->getDealActivities($deal);

        return response()->json([
            'success' => true,
            'data' => $activities,
        ]);
    }

    /**
     * Close deal as won.
     */
    public function closeWon(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $deal = $this->dealService->getDealById($id, $tenantId);

        if (!$deal) {
            return response()->json([
                'success' => false,
                'message' => 'Deal not found.',
            ], 404);
        }

        $deal = $this->dealService->closeAsWon($deal);

        return response()->json([
            'success' => true,
            'data' => $deal,
            'message' => 'Deal closed as won.',
        ]);
    }

    /**
     * Close deal as lost.
     */
    public function closeLost(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'lost_reason' => 'required|string',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $deal = $this->dealService->getDealById($id, $tenantId);

        if (!$deal) {
            return response()->json([
                'success' => false,
                'message' => 'Deal not found.',
            ], 404);
        }

        $deal = $this->dealService->closeAsLost($deal, $request->lost_reason);

        return response()->json([
            'success' => true,
            'data' => $deal,
            'message' => 'Deal closed as lost.',
        ]);
    }

    /**
     * Get deal statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $stats = $this->dealService->getDealStats($tenantId);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Forecast revenue.
     */
    public function forecast(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $forecast = $this->dealService->forecastRevenue($tenantId);

        return response()->json([
            'success' => true,
            'data' => ['forecasted_revenue' => $forecast],
        ]);
    }

    /**
     * Get overdue deals.
     */
    public function overdue(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $deals = $this->dealService->getOverdueDeals($tenantId);

        return response()->json([
            'success' => true,
            'data' => $deals,
        ]);
    }
}
