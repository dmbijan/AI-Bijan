<?php

namespace App\Modules\Site\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Site\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$this->tenantId()` yang sentiasa
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
     * Get page view statistics.
     */
    public function getPageViews(Request $request, int $siteId): JsonResponse
    {
        $stats = $this->analyticsService->getPageViews($siteId, $this->tenantId(), [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get traffic statistics.
     */
    public function getTrafficStats(Request $request, int $siteId): JsonResponse
    {
        $stats = $this->analyticsService->getTrafficStats($siteId, $this->tenantId(), [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get form submission statistics.
     */
    public function getFormStats(Request $request, int $siteId): JsonResponse
    {
        $stats = $this->analyticsService->getFormStats($siteId, $this->tenantId());
        
        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get top pages by views.
     */
    public function getTopPages(Request $request, int $siteId): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $pages = $this->analyticsService->getTopPages($siteId, $this->tenantId(), $limit);
        
        return response()->json([
            'success' => true,
            'data' => $pages,
        ]);
    }

    /**
     * Get top posts by views.
     */
    public function getTopPosts(Request $request, int $siteId): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $posts = $this->analyticsService->getTopPosts($siteId, $this->tenantId(), $limit);
        
        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }

    /**
     * Get conversion rate.
     */
    public function getConversionRate(Request $request, int $siteId): JsonResponse
    {
        $rate = $this->analyticsService->getConversionRate($siteId, $this->tenantId(), [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);
        
        return response()->json([
            'success' => true,
            'data' => [
                'conversion_rate' => $rate,
            ],
        ]);
    }

    /**
     * Get visitor locations.
     */
    public function getVisitorLocations(Request $request, int $siteId): JsonResponse
    {
        $locations = $this->analyticsService->getVisitorLocations($siteId, $this->tenantId(), [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $locations,
        ]);
    }

    /**
     * Get referrer data.
     */
    public function getReferrers(Request $request, int $siteId): JsonResponse
    {
        $referrers = $this->analyticsService->getReferrers($siteId, $this->tenantId(), [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $referrers,
        ]);
    }

    /**
     * Get complete analytics dashboard data.
     */
    public function dashboard(Request $request, int $siteId): JsonResponse
    {
        $pageViews = $this->analyticsService->getPageViews($siteId, $this->tenantId());
        $trafficStats = $this->analyticsService->getTrafficStats($siteId, $this->tenantId());
        $formStats = $this->analyticsService->getFormStats($siteId, $this->tenantId());

        return response()->json([
            'success' => true,
            'data' => [
                'page_views' => $pageViews,
                'traffic_stats' => $trafficStats,
                'form_stats' => $formStats,
            ],
        ]);
    }
}
