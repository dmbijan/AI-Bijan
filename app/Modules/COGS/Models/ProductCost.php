<?php

namespace App\Modules\COGS\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class ProductCost extends Model
{
    use BelongsToTenant;

    protected $table = 'cogs_product_costs';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'cost_date',
        'method', // fifo, lifo, weighted_average, specific_id
        'raw_material_cost',
        'direct_labor_cost',
        'manufacturing_overhead_cost',
        'packaging_cost',
        'shipping_in_cost',
        'duties_taxes_cost',
        'total_direct_cost',
        'allocated_overhead',
        'total_unit_cost',
        'quantity_produced',
        'selling_price',
        'gross_margin',
        'gross_margin_percentage',
        'status', // draft, finalized, archived
        'notes',
    ];

    protected $casts = [
        'cost_date' => 'date',
        'raw_material_cost' => 'decimal:2',
        'direct_labor_cost' => 'decimal:2',
        'manufacturing_overhead_cost' => 'decimal:2',
        'packaging_cost' => 'decimal:2',
        'shipping_in_cost' => 'decimal:2',
        'duties_taxes_cost' => 'decimal:2',
        'total_direct_cost' => 'decimal:2',
        'allocated_overhead' => 'decimal:2',
        'total_unit_cost' => 'decimal:2',
        'quantity_produced' => 'integer',
        'selling_price' => 'decimal:2',
        'gross_margin' => 'decimal:2',
        'gross_margin_percentage' => 'decimal:2',
    ];

    /**
     * Costing methods
     */
    const METHOD_FIFO = 'fifo';
    const METHOD_LIFO = 'lifo';
    const METHOD_WEIGHTED_AVERAGE = 'weighted_average';
    const METHOD_SPECIFIC_ID = 'specific_id';

    /**
     * Statuses
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_FINALIZED = 'finalized';
    const STATUS_ARCHIVED = 'archived';

    /**
     * Scope by product
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope by date range
     */
    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('cost_date', [$startDate, $endDate]);
    }

    /**
     * Scope finalized only
     */
    public function scopeFinalized($query)
    {
        return $query->where('status', self::STATUS_FINALIZED);
    }

    /**
     * Get latest cost for product
     */
    public function scopeLatestForProduct($query, int $productId)
    {
        return $query->forProduct($productId)
            ->finalized()
            ->orderBy('cost_date', 'desc')
            ->first();
    }

    /**
     * Calculate totals before save
     */
    public function calculateTotals(): void
    {
        $this->total_direct_cost = $this->raw_material_cost 
            + $this->direct_labor_cost 
            + $this->manufacturing_overhead_cost 
            + $this->packaging_cost 
            + $this->shipping_in_cost 
            + $this->duties_taxes_cost;

        if ($this->quantity_produced > 0) {
            $this->total_unit_cost = ($this->total_direct_cost + $this->allocated_overhead) / $this->quantity_produced;
        }

        if ($this->selling_price > 0 && $this->total_unit_cost > 0) {
            $this->gross_margin = $this->selling_price - $this->total_unit_cost;
            $this->gross_margin_percentage = ($this->gross_margin / $this->selling_price) * 100;
        }
    }

    /**
     * Get product relationship
     */
    public function product()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Product::class, 'product_id');
    }
}
