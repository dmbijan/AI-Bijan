<?php

namespace App\Modules\COGS\Services;

use App\Modules\COGS\Models\ProductCost;
use App\Modules\COGS\Models\CostComponent;
use Illuminate\Support\Facades\DB;

class CostCalculationService
{
    /**
     * Calculate total COGS for a product
     */
    public function calculateProductCost(int $tenantId, int $productId, string $method = null): ?ProductCost
    {
        $method = $method ?? config('cogs.cost_calculation.default_method', 'weighted_average');
        
        // Get all cost components for this product
        $components = CostComponent::where('tenant_id', $tenantId)
            ->forProduct($productId)
            ->withTotal()
            ->get();

        if ($components->isEmpty()) {
            return null;
        }

        // Aggregate by type
        $costs = [
            'raw_material_cost' => 0,
            'direct_labor_cost' => 0,
            'manufacturing_overhead_cost' => 0,
            'packaging_cost' => 0,
            'shipping_in_cost' => 0,
            'duties_taxes_cost' => 0,
        ];

        foreach ($components as $component) {
            if (isset($costs[$component->component_type])) {
                $costs[$component->component_type] += $component->quantity * $component->unit_price;
            }
        }

        // Apply costing method (FIFO/LIFO/Weighted Average)
        if (in_array($method, ['fifo', 'lifo'])) {
            $costs = $this->applyInventoryMethod($tenantId, $productId, $method, $costs);
        }

        // Create product cost record
        $productCost = ProductCost::create([
            'tenant_id' => $tenantId,
            'product_id' => $productId,
            'cost_date' => now()->format('Y-m-d'),
            'method' => $method,
            'raw_material_cost' => $costs['raw_material_cost'],
            'direct_labor_cost' => $costs['direct_labor_cost'],
            'manufacturing_overhead_cost' => $costs['manufacturing_overhead_cost'],
            'packaging_cost' => $costs['packaging_cost'],
            'shipping_in_cost' => $costs['shipping_in_cost'],
            'duties_taxes_cost' => $costs['duties_taxes_cost'],
            'status' => ProductCost::STATUS_DRAFT,
        ]);

        $productCost->calculateTotals();
        $productCost->save();

        return $productCost;
    }

    /**
     * Apply FIFO or LIFO inventory valuation
     */
    protected function applyInventoryMethod(int $tenantId, int $productId, string $method, array $costs): array
    {
        // Get inventory transactions sorted by date
        $transactions = DB::table('inventory_transactions')
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->whereIn('transaction_type', ['purchase', 'production'])
            ->orderByRaw($method === 'fifo' ? 'created_at ASC' : 'created_at DESC')
            ->get();

        // Recalculate costs based on actual transaction prices
        foreach ($transactions as $transaction) {
            // Update unit price from actual transaction
            if (isset($costs['raw_material_cost'])) {
                $costs['raw_material_cost'] = $transaction->unit_cost * ($transaction->quantity ?? 1);
            }
        }

        return $costs;
    }

    /**
     * Allocate overhead costs to products
     */
    public function allocateOverheads(int $tenantId, string $period = null): array
    {
        $period = $period ?? now()->format('Y-m');
        $method = config('cogs.overhead_allocation.method', 'production_hours');

        // Get total overhead for period
        $totalOverhead = DB::table('journal_entries')
            ->where('tenant_id', $tenantId)
            ->whereYear('entry_date', explode('-', $period)[0])
            ->whereMonth('entry_date', explode('-', $period)[1])
            ->whereHas('account', function ($q) {
                $q->where('account_type', 'overhead');
            })
            ->sum('debit_amount') ?? 0;

        // Get allocation base (production hours per product)
        $allocationBase = DB::table('work_orders')
            ->where('tenant_id', $tenantId)
            ->whereMonth('scheduled_start', explode('-', $period)[1])
            ->selectRaw('product_id, SUM(estimated_hours) as total_hours')
            ->groupBy('product_id')
            ->get();

        $allocations = [];

        foreach ($allocationBase as $base) {
            $percentage = $base->total_hours > 0 
                ? ($base->total_hours / $allocationBase->sum('total_hours')) * 100 
                : 0;

            $allocatedAmount = ($percentage / 100) * $totalOverhead;

            $allocations[] = [
                'product_id' => $base->product_id,
                'allocation_percentage' => round($percentage, 2),
                'allocated_amount' => round($allocatedAmount, 2),
            ];
        }

        return $allocations;
    }

    /**
     * Add cost component to product
     */
    public function addCostComponent(int $tenantId, int $productId, array $data): CostComponent
    {
        $component = CostComponent::create(array_merge($data, [
            'tenant_id' => $tenantId,
            'product_id' => $productId,
            'total_amount' => ($data['quantity'] ?? 0) * ($data['unit_price'] ?? 0),
        ]));

        return $component;
    }

    /**
     * Remove cost component
     */
    public function removeCostComponent(int $tenantId, int $componentId): bool
    {
        return CostComponent::where('tenant_id', $tenantId)
            ->where('id', $componentId)
            ->delete() > 0;
    }

    /**
     * Recalculate all product costs
     */
    public function recalculateAll(int $tenantId, string $period = null): array
    {
        $products = DB::table('products')
            ->where('tenant_id', $tenantId)
            ->where('type', 'saleable')
            ->pluck('id');

        $results = [
            'calculated' => 0,
            'errors' => [],
        ];

        foreach ($products as $productId) {
            try {
                $result = $this->calculateProductCost($tenantId, $productId);
                if ($result) {
                    $results['calculated']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'product_id' => $productId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get current cost for product
     */
    public function getCurrentCost(int $tenantId, int $productId): ?ProductCost
    {
        return ProductCost::latestForProduct($tenantId, $productId);
    }

    /**
     * Get cost history for product
     */
    public function getCostHistory(int $tenantId, int $productId, int $limit = 12): \Illuminate\Pagination\LengthAwarePaginator
    {
        return ProductCost::where('tenant_id', $tenantId)
            ->forProduct($productId)
            ->finalized()
            ->orderBy('cost_date', 'desc')
            ->paginate($limit);
    }
}
