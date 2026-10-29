<?php

namespace App\Modules\COGS\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use App\Http\Controllers\Controller;
use App\Modules\COGS\Services\MarginAnalysisService;
use Illuminate\Http\Request;

class MarginReportController extends Controller
{
    protected $marginAnalysisService;

    public function __construct(MarginAnalysisService $marginAnalysisService)
    {
        $this->marginAnalysisService = $marginAnalysisService;
    }

    /**
     * Calculate margin for a product sale
     */
    public function calculateMargin(Request $request, int $productId)
    {
        $validated = $request->validate([
            'selling_price' => 'required|numeric|min:0',
        ]);

        $result = $this->marginAnalysisService->calculateMargin(
            app(TenantContext::class)->id(),
            $productId,
            $validated['selling_price']
        );

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Get all product margins
     */
    public function getAllMargins(Request $request)
    {
        $filters = $request->only(['category_id', 'min_margin', 'max_margin', 'status']);
        
        return response()->json($this->marginAnalysisService->getAllProductMargins(
            app(TenantContext::class)->id(),
            $filters
        ));
    }

    /**
     * Get margin by category
     */
    public function getMarginByCategory(Request $request)
    {
        return response()->json($this->marginAnalysisService->getMarginByCategory(
            app(TenantContext::class)->id()
        ));
    }

    /**
     * Get margin trend for product
     */
    public function getMarginTrend(Request $request, int $productId)
    {
        $validated = $request->validate([
            'months' => 'integer|min:1|max:24',
        ]);

        return response()->json($this->marginAnalysisService->getMarginTrend(
            app(TenantContext::class)->id(),
            $productId,
            $validated['months'] ?? 6
        ));
    }

    /**
     * Get low margin products
     */
    public function getLowMarginProducts(Request $request)
    {
        $validated = $request->validate([
            'threshold' => 'numeric|min:0|max:100',
        ]);

        return response()->json($this->marginAnalysisService->getLowMarginProducts(
            app(TenantContext::class)->id(),
            $validated['threshold'] ?? null
        ));
    }

    /**
     * Calculate break-even point
     */
    public function calculateBreakEven(Request $request, int $productId)
    {
        $validated = $request->validate([
            'fixed_costs' => 'nullable|numeric|min:0',
        ]);

        $result = $this->marginAnalysisService->calculateBreakEven(
            app(TenantContext::class)->id(),
            $productId,
            $validated['fixed_costs'] ?? null
        );

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Generate margin report
     */
    public function generateReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $report = $this->marginAnalysisService->generateMarginReport(
            app(TenantContext::class)->id(),
            $validated['start_date'],
            $validated['end_date']
        );

        return response()->json($report);
    }

    /**
     * Get profitability ranking
     */
    public function getProfitabilityRanking(Request $request)
    {
        $limit = $request->input('limit', 20);
        
        return response()->json($this->marginAnalysisService->getProfitabilityRanking(
            app(TenantContext::class)->id(),
            $limit
        ));
    }
}
