<?php

namespace App\Modules\COGS\Services;

use App\Modules\COGS\Models\ProductCost;
use Illuminate\Support\Facades\DB;

class MarginAnalysisService
{
    /**
     * Calculate gross margin for a product sale
     */
    public function calculateMargin(int $tenantId, int $productId, float $sellingPrice): array
    {
        $productCost = ProductCost::where('tenant_id', $tenantId)
            ->forProduct($productId)
            ->finalized()
            ->orderBy('cost_date', 'desc')
            ->first();

        if (!$productCost || $productCost->total_unit_cost <= 0) {
            return [
                'success' => false,
                'error' => 'No finalized cost record found for this product',
            ];
        }

        $grossMargin = $sellingPrice - $productCost->total_unit_cost;
        $grossMarginPercentage = ($grossMargin / $sellingPrice) * 100;

        // Check against thresholds
        $minMargin = config('cogs.margin_analysis.min_gross_margin', 20);
        $targetMargin = config('cogs.margin_analysis.target_gross_margin', 35);
        $alertThreshold = config('cogs.margin_analysis.alert_threshold', 10);

        $status = 'healthy';
        $message = sprintf(
            'Gross margin %.2f%% meets target of %.2f%%',
            $grossMarginPercentage,
            $targetMargin
        );

        if ($grossMarginPercentage < ($targetMargin - $alertThreshold)) {
            $status = 'critical';
            $message = sprintf(
                'WARNING: Gross margin %.2f%% is below critical threshold (%.2f%% - %.2f%%)',
                $grossMarginPercentage,
                $targetMargin,
                $alertThreshold
            );
        } elseif ($grossMarginPercentage < $targetMargin) {
            $status = 'warning';
            $message = sprintf(
                'NOTE: Gross margin %.2f%% is below target of %.2f%%',
                $grossMarginPercentage,
                $targetMargin
            );
        }

        return [
            'success' => true,
            'product_id' => $productId,
            'selling_price' => round($sellingPrice, 2),
            'unit_cost' => round($productCost->total_unit_cost, 2),
            'gross_margin' => round($grossMargin, 2),
            'gross_margin_percentage' => round($grossMarginPercentage, 2),
            'target_margin' => $targetMargin,
            'min_margin' => $minMargin,
            'status' => $status,
            'message' => $message,
        ];
    }

    /**
     * Get margin analysis for all products
     */
    public function getAllProductMargins(int $tenantId, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = DB::table('cogs_product_costs as pc')
            ->join('products as p', 'pc.product_id', '=', 'p.id')
            ->where('pc.tenant_id', $tenantId)
            ->where('pc.status', ProductCost::STATUS_FINALIZED)
            ->select(
                'p.id as product_id',
                'p.name as product_name',
                'p.category_id',
                'pc.total_unit_cost',
                'pc.selling_price',
                'pc.gross_margin',
                'pc.gross_margin_percentage',
                'pc.cost_date',
                'pc.method'
            )
            ->latest('pc.cost_date');

        // Apply filters
        if (isset($filters['category_id'])) {
            $query->where('p.category_id', $filters['category_id']);
        }

        if (isset($filters['min_margin'])) {
            $query->where('pc.gross_margin_percentage', '>=', $filters['min_margin']);
        }

        if (isset($filters['max_margin'])) {
            $query->where('pc.gross_margin_percentage', '<=', $filters['max_margin']);
        }

        if (isset($filters['status']) && $filters['status'] === 'below_target') {
            $targetMargin = config('cogs.margin_analysis.target_gross_margin', 35);
            $query->where('pc.gross_margin_percentage', '<', $targetMargin);
        }

        return $query->paginate(50);
    }

    /**
     * Get margin by category
     */
    public function getMarginByCategory(int $tenantId): array
    {
        $categories = DB::table('cogs_product_costs as pc')
            ->join('products as p', 'pc.product_id', '=', 'p.id')
            ->join('product_categories as pcate', 'p.category_id', '=', 'pcate.id')
            ->where('pc.tenant_id', $tenantId)
            ->where('pc.status', ProductCost::STATUS_FINALIZED)
            ->select(
                'pcate.name as category_name',
                'pcate.id as category_id',
                DB::raw('COUNT(*) as product_count'),
                DB::raw('AVG(pc.gross_margin_percentage) as avg_margin'),
                DB::raw('MIN(pc.gross_margin_percentage) as min_margin'),
                DB::raw('MAX(pc.gross_margin_percentage) as max_margin'),
                DB::raw('SUM((pc.selling_price - pc.total_unit_cost) * SUM(p.stock_quantity)) as total_profit')
            )
            ->groupBy('pcate.id', 'pcate.name')
            ->orderBy('avg_margin', 'desc')
            ->get();

        return $categories->toArray();
    }

    /**
     * Get margin trend over time
     */
    public function getMarginTrend(int $tenantId, int $productId, int $months = 6): array
    {
        $startDate = now()->subMonths($months)->format('Y-m-d');

        $trends = DB::table('cogs_product_costs')
            ->where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('status', ProductCost::STATUS_FINALIZED)
            ->where('cost_date', '>=', $startDate)
            ->select(
                'cost_date',
                'selling_price',
                'total_unit_cost',
                'gross_margin',
                'gross_margin_percentage'
            )
            ->orderBy('cost_date', 'asc')
            ->get();

        return $trends->toArray();
    }

    /**
     * Identify low-margin products
     */
    public function getLowMarginProducts(int $tenantId, float $threshold = null): \Illuminate\Support\Collection
    {
        $threshold = $threshold ?? config('cogs.margin_analysis.min_gross_margin', 20);

        return DB::table('cogs_product_costs as pc')
            ->join('products as p', 'pc.product_id', '=', 'p.id')
            ->where('pc.tenant_id', $tenantId)
            ->where('pc.status', ProductCost::STATUS_FINALIZED)
            ->where('pc.gross_margin_percentage', '<', $threshold)
            ->select(
                'p.id as product_id',
                'p.name as product_name',
                'pc.gross_margin_percentage',
                'pc.gross_margin',
                'pc.total_unit_cost',
                'pc.selling_price',
                'pc.cost_date'
            )
            ->orderBy('gross_margin_percentage', 'asc')
            ->get();
    }

    /**
     * Calculate break-even point
     */
    public function calculateBreakEven(int $tenantId, int $productId, float $fixedCosts = null): array
    {
        $productCost = ProductCost::where('tenant_id', $tenantId)
            ->forProduct($productId)
            ->finalized()
            ->orderBy('cost_date', 'desc')
            ->first();

        if (!$productCost) {
            return [
                'success' => false,
                'error' => 'No cost data available',
            ];
        }

        $fixedCosts = $fixedCosts ?? config('cogs.margin_analysis.default_fixed_costs', 10000);
        $sellingPrice = $productCost->selling_price;
        $variableCost = $productCost->total_unit_cost;

        if ($sellingPrice <= $variableCost) {
            return [
                'success' => false,
                'error' => 'Selling price must be higher than variable cost',
            ];
        }

        $contributionMargin = $sellingPrice - $variableCost;
        $breakEvenUnits = ceil($fixedCosts / $contributionMargin);
        $breakEvenRevenue = $breakEvenUnits * $sellingPrice;

        return [
            'success' => true,
            'fixed_costs' => round($fixedCosts, 2),
            'selling_price' => round($sellingPrice, 2),
            'variable_cost' => round($variableCost, 2),
            'contribution_margin' => round($contributionMargin, 2),
            'break_even_units' => $breakEvenUnits,
            'break_even_revenue' => round($breakEvenRevenue, 2),
        ];
    }

    /**
     * Generate margin report
     */
    public function generateMarginReport(int $tenantId, string $startDate, string $endDate): array
    {
        // Total sales revenue
        $totalRevenue = DB::table('sales_orders as so')
            ->join('sales_order_items as soi', 'so.id', '=', 'soi.sales_order_id')
            ->where('so.tenant_id', $tenantId)
            ->whereBetween('so.order_date', [$startDate, $endDate])
            ->sum('soi.line_total') ?? 0;

        // Total COGS
        $totalCOGS = 0;
        $products = DB::table('sales_order_items as soi')
            ->join('sales_orders as so', 'so.id', '=', 'soi.sales_order_id')
            ->where('so.tenant_id', $tenantId)
            ->whereBetween('so.order_date', [$startDate, $endDate])
            ->distinct()
            ->pluck('soi.product_id');

        foreach ($products as $productId) {
            $cost = ProductCost::latestForProduct($tenantId, $productId);
            if ($cost) {
                $qty = DB::table('sales_order_items')
                    ->where('product_id', $productId)
                    ->sum('quantity');
                $totalCOGS += $cost->total_unit_cost * $qty;
            }
        }

        $grossProfit = $totalRevenue - $totalCOGS;
        $grossMarginPercentage = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

        return [
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'total_revenue' => round($totalRevenue, 2),
            'total_cogs' => round($totalCOGS, 2),
            'gross_profit' => round($grossProfit, 2),
            'gross_margin_percentage' => round($grossMarginPercentage, 2),
            'product_count' => $products->count(),
        ];
    }

    /**
     * Get profitability ranking
     */
    public function getProfitabilityRanking(int $tenantId, int $limit = 20): array
    {
        return DB::table('cogs_product_costs as pc')
            ->join('products as p', 'pc.product_id', '=', 'p.id')
            ->leftJoin('sales_order_items as soi', 'pc.product_id', '=', 'soi.product_id')
            ->leftJoin('sales_orders as so', 'soi.sales_order_id', '=', 'so.id')
            ->where('pc.tenant_id', $tenantId)
            ->where('pc.status', ProductCost::STATUS_FINALIZED)
            ->whereYear('so.order_date', date('Y'))
            ->select(
                'p.id as product_id',
                'p.name as product_name',
                'p.category_id',
                DB::raw('SUM(soi.quantity) as units_sold'),
                DB::raw('SUM(soi.line_total) as total_revenue'),
                DB::raw('SUM(soi.quantity * pc.total_unit_cost) as total_cogs'),
                DB::raw('SUM(soi.line_total) - SUM(soi.quantity * pc.total_unit_cost) as gross_profit'),
                'pc.gross_margin_percentage'
            )
            ->groupBy('p.id', 'p.name', 'p.category_id', 'pc.gross_margin_percentage')
            ->havingRaw('SUM(soi.quantity) > 0')
            ->orderBy('gross_profit', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
