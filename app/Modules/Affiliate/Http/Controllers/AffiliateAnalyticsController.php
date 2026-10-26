<?php

namespace App\Modules\Affiliate\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Affiliate\Services\AffiliateAnalyticsService;
use Illuminate\Http\Request;

class AffiliateAnalyticsController extends Controller
{
    protected $service;

    public function __construct(AffiliateAnalyticsService $service)
    {
        $this->service = $service;
    }

    /**
     * Get overall program analytics.
     */
    public function getOverview(Request $request)
    {
        $dateRange = [];
        if ($request->has('date_from')) {
            $dateRange['start'] = $request->date_from;
        }
        if ($request->has('date_to')) {
            $dateRange['end'] = $request->date_to;
        }

        $overview = $this->service->getProgramOverview($request->tenant_id, $dateRange);

        return response()->json([
            'success' => true,
            'data' => $overview,
        ]);
    }

    /**
     * Get top performing affiliates.
     */
    public function getTopAffiliates(Request $request)
    {
        $affiliates = $this->service->getTopAffiliates(
            $request->tenant_id,
            $request->get('limit', 10),
            $request->only(['tier'])
        );

        return response()->json([
            'success' => true,
            'data' => $affiliates,
        ]);
    }

    /**
     * Get conversion funnel data.
     */
    public function getFunnel(Request $request)
    {
        $dateRange = [];
        if ($request->has('date_from')) {
            $dateRange['start'] = $request->date_from;
        }
        if ($request->has('date_to')) {
            $dateRange['end'] = $request->date_to;
        }

        $funnel = $this->service->getConversionFunnel($request->tenant_id, $dateRange);

        return response()->json([
            'success' => true,
            'data' => $funnel,
        ]);
    }

    /**
     * Get commission trends.
     */
    public function getTrends(Request $request)
    {
        $validated = $request->validate([
            'period' => 'nullable|in:daily,weekly,monthly',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after:date_from',
        ]);

        $trends = $this->service->getCommissionTrends(
            $request->tenant_id,
            $validated['period'] ?? 'monthly',
            array_filter($validated, fn($k) => in_array($k, ['date_from', 'date_to']), ARRAY_FILTER_USE_KEY)
        );

        return response()->json([
            'success' => true,
            'data' => $trends,
        ]);
    }

    /**
     * Get payout report.
     */
    public function getPayoutReport(Request $request)
    {
        $dateRange = [];
        if ($request->has('date_from')) {
            $dateRange['start'] = $request->date_from;
        }
        if ($request->has('date_to')) {
            $dateRange['end'] = $request->date_to;
        }

        $report = $this->service->getPayoutReport($request->tenant_id, $dateRange);

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Get ROI metrics.
     */
    public function getROI(Request $request)
    {
        $dateRange = [];
        if ($request->has('date_from')) {
            $dateRange['start'] = $request->date_from;
        }
        if ($request->has('date_to')) {
            $dateRange['end'] = $request->date_to;
        }

        $roi = $this->service->getROI($request->tenant_id, $dateRange);

        return response()->json([
            'success' => true,
            'data' => $roi,
        ]);
    }

    /**
     * Get performance comparison between affiliates.
     */
    public function getComparison(Request $request)
    {
        $validated = $request->validate([
            'affiliate_ids' => 'required|array',
            'affiliate_ids.*' => 'integer',
        ]);

        $comparison = $this->service->getPerformanceComparison(
            $request->tenant_id,
            $validated['affiliate_ids']
        );

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }
}
