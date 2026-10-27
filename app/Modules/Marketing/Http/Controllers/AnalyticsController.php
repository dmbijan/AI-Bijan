<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Marketing\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
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
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Get campaign analytics.
     */
    public function getCampaignAnalytics(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'status' => 'nullable|in:draft,scheduled,running,paused,completed,cancelled',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $analytics = $this->analyticsService->getCampaignAnalytics($tenantId, $request->all());

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Get channel-wise breakdown.
     */
    public function getChannelBreakdown(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $breakdown = $this->analyticsService->getChannelBreakdown($tenantId);

        return response()->json([
            'success' => true,
            'data' => $breakdown,
        ]);
    }

    /**
     * Get time-series data for trends.
     */
    public function getTimeSeriesData(Request $request): JsonResponse
    {
        $request->validate([
            'period' => 'nullable|in:daily,weekly,monthly',
            'days' => 'nullable|integer|min:1|max:365',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $data = $this->analyticsService->getTimeSeriesData(
            $tenantId,
            $request->period ?? 'daily',
            $request->days ?? 30
        );

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Calculate ROI.
     */
    public function calculateROI(Request $request): JsonResponse
    {
        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $roi = $this->analyticsService->calculateROI($tenantId, $request->all());

        return response()->json([
            'success' => true,
            'data' => $roi,
        ]);
    }

    /**
     * Get attribution model.
     */
    public function getAttribution(Request $request): JsonResponse
    {
        $request->validate([
            'model' => 'nullable|in:last_click,first_click,linear,time_decay,position_based',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $attribution = $this->analyticsService->getAttributionModel(
            $tenantId,
            $request->model ?? 'last_click'
        );

        return response()->json([
            'success' => true,
            'data' => $attribution,
        ]);
    }

    /**
     * Get acquisition metrics.
     */
    public function getAcquisitionMetrics(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $metrics = $this->analyticsService->getAcquisitionMetrics($tenantId);

        return response()->json([
            'success' => true,
            'data' => $metrics,
        ]);
    }

    /**
     * Get voucher analytics.
     */
    public function getVoucherAnalytics(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $analytics = $this->analyticsService->getVoucherAnalytics($tenantId);

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Get marketing funnel metrics.
     */
    public function getFunnelMetrics(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $funnel = $this->analyticsService->getFunnelMetrics($tenantId);

        return response()->json([
            'success' => true,
            'data' => $funnel,
        ]);
    }

    /**
     * Get comprehensive dashboard data.
     */
    public function getDashboard(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $dashboard = $this->analyticsService->getDashboardData($tenantId);

        return response()->json([
            'success' => true,
            'data' => $dashboard,
        ]);
    }

    public function getPageViews(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'getPageViews']);
    }

    public function getTrafficStats(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'getTrafficStats']);
    }

    public function getFormStats(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'getFormStats']);
    }

    public function getTopPages(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'getTopPages']);
    }

    public function getTopPosts(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'getTopPosts']);
    }

    public function getConversionRate(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'getConversionRate']);
    }

    public function getVisitorLocations(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'getVisitorLocations']);
    }

    public function getReferrers(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'getReferrers']);
    }

    public function dashboard(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'dashboard']);
    }
}
