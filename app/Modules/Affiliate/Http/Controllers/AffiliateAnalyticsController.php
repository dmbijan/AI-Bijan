<?php

namespace App\Modules\Affiliate\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Affiliate\Services\AffiliateAnalyticsService;
use Illuminate\Http\Request;

class AffiliateAnalyticsController extends Controller
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

        $overview = $this->service->getProgramOverview($this->tenantId(), $dateRange);

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
            $this->tenantId(),
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

        $funnel = $this->service->getConversionFunnel($this->tenantId(), $dateRange);

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
            $this->tenantId(),
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

        $report = $this->service->getPayoutReport($this->tenantId(), $dateRange);

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

        $roi = $this->service->getROI($this->tenantId(), $dateRange);

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
            $this->tenantId(),
            $validated['affiliate_ids']
        );

        return response()->json([
            'success' => true,
            'data' => $comparison,
        ]);
    }
}
