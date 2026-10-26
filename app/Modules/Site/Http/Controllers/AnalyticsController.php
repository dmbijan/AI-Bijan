<?php

namespace App\Modules\Site\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Site\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnalyticsController extends Controller
{
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
        $stats = $this->analyticsService->getPageViews($siteId, $request->tenant_id, [
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
        $stats = $this->analyticsService->getTrafficStats($siteId, $request->tenant_id, [
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
        $stats = $this->analyticsService->getFormStats($siteId, $request->tenant_id);
        
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
        $pages = $this->analyticsService->getTopPages($siteId, $request->tenant_id, $limit);
        
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
        $posts = $this->analyticsService->getTopPosts($siteId, $request->tenant_id, $limit);
        
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
        $rate = $this->analyticsService->getConversionRate($siteId, $request->tenant_id, [
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
        $locations = $this->analyticsService->getVisitorLocations($siteId, $request->tenant_id, [
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
        $referrers = $this->analyticsService->getReferrers($siteId, $request->tenant_id, [
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
        $pageViews = $this->analyticsService->getPageViews($siteId, $request->tenant_id);
        $trafficStats = $this->analyticsService->getTrafficStats($siteId, $request->tenant_id);
        $formStats = $this->analyticsService->getFormStats($siteId, $request->tenant_id);

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
