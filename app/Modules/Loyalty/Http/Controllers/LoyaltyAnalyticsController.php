<?php

namespace App\Modules\Loyalty\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Modules\Loyalty\Services\LoyaltyAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyAnalyticsController
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
    protected LoyaltyAnalyticsService $analyticsService;

    public function __construct(LoyaltyAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get overall loyalty program analytics.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();

        $analytics = $this->analyticsService->getAnalytics(
            $tenantId,
            $request->input('from'),
            $request->input('to')
        );

        return response()->json($analytics);
    }

    /**
     * Get member segmentation data.
     */
    public function segmentation(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $segmentation = $this->analyticsService->getSegmentation($tenantId);

        return response()->json($segmentation);
    }

    /**
     * Get LTV trends.
     */
    public function ltvTrends(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $months = $request->input('months', 12);

        $trends = $this->analyticsService->getLTVTrends($tenantId, $months);

        return response()->json($trends);
    }

    /**
     * Get churn prediction metrics.
     */
    public function churnMetrics(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $churnMetrics = $this->analyticsService->getChurnMetrics($tenantId);

        return response()->json($churnMetrics);
    }

    /**
     * Get points liability report.
     */
    public function liability(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $liability = $this->analyticsService->getPointsLiability($tenantId);

        return response()->json($liability);
    }
}
