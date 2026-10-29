<?php

namespace App\Modules\COGS\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use App\Http\Controllers\Controller;
use App\Modules\COGS\Services\CostCalculationService;
use Illuminate\Http\Request;

class CostCalculationController extends Controller
{
    protected $costCalculationService;

    public function __construct(CostCalculationService $costCalculationService)
    {
        $this->costCalculationService = $costCalculationService;
    }

    /**
     * Calculate product cost
     */
    public function calculate(Request $request, int $productId)
    {
        $validated = $request->validate([
            'method' => 'string|in:fifo,lifo,weighted_average,specific_id',
        ]);

        $result = $this->costCalculationService->calculateProductCost(
            app(TenantContext::class)->id(),
            $productId,
            $validated['method'] ?? null
        );

        if (!$result) {
            return response()->json(['message' => 'No cost components found'], 404);
        }

        return response()->json($result);
    }

    /**
     * Get current cost for product
     */
    public function getCurrentCost(Request $request, int $productId)
    {
        $cost = $this->costCalculationService->getCurrentCost(
            app(TenantContext::class)->id(),
            $productId
        );

        if (!$cost) {
            return response()->json(['message' => 'No cost record found'], 404);
        }

        return response()->json($cost);
    }

    /**
     * Get cost history for product
     */
    public function getHistory(Request $request, int $productId)
    {
        $limit = $request->input('limit', 12);
        
        return response()->json($this->costCalculationService->getCostHistory(
            app(TenantContext::class)->id(),
            $productId,
            $limit
        ));
    }

    /**
     * Add cost component
     */
    public function addComponent(Request $request, int $productId)
    {
        $validated = $request->validate([
            'component_type' => 'required|string|in:raw_material,direct_labor,manufacturing_overhead,packaging,shipping_in,duties_taxes',
            'description' => 'nullable|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'is_allocated' => 'boolean',
            'allocation_base' => 'nullable|string',
            'allocation_percentage' => 'nullable|numeric|min:0|max:100',
            'source_document' => 'nullable|string|max:50',
            'source_id' => 'nullable|integer',
            'notes' => 'nullable|string|max:500',
        ]);

        $component = $this->costCalculationService->addCostComponent(
            app(TenantContext::class)->id(),
            $productId,
            $validated
        );

        return response()->json($component, 201);
    }

    /**
     * Remove cost component
     */
    public function removeComponent(Request $request, int $componentId)
    {
        $success = $this->costCalculationService->removeCostComponent(
            app(TenantContext::class)->id(),
            $componentId
        );

        if (!$success) {
            return response()->json(['message' => 'Component not found'], 404);
        }

        return response()->json(['message' => 'Component removed']);
    }

    /**
     * Allocate overheads
     */
    public function allocateOverheads(Request $request)
    {
        $validated = $request->validate([
            'period' => 'nullable|string|date_format:Y-m',
        ]);

        $allocations = $this->costCalculationService->allocateOverheads(
            app(TenantContext::class)->id(),
            $validated['period'] ?? null
        );

        return response()->json($allocations);
    }

    /**
     * Recalculate all costs
     */
    public function recalculateAll(Request $request)
    {
        $validated = $request->validate([
            'period' => 'nullable|string|date_format:Y-m',
        ]);

        $results = $this->costCalculationService->recalculateAll(
            app(TenantContext::class)->id(),
            $validated['period'] ?? null
        );

        return response()->json($results);
    }
}
