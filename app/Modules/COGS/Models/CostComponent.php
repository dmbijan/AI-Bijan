<?php

namespace App\Modules\COGS\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class CostComponent extends Model
{
    use BelongsToTenant;

    protected $table = 'cogs_cost_components';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'component_type', // raw_material, direct_labor, manufacturing_overhead, packaging, shipping_in, duties_taxes
        'description',
        'quantity',
        'unit_price',
        'total_amount',
        'is_allocated', // For overhead allocation
        'allocation_base', // production_hours, direct_labor_cost, etc.
        'allocation_percentage',
        'source_document', // purchase_order, work_order, journal_entry
        'source_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'allocation_percentage' => 'decimal:4',
    ];

    /**
     * Component types
     */
    const TYPE_RAW_MATERIAL = 'raw_material';
    const TYPE_DIRECT_LABOR = 'direct_labor';
    const TYPE_MANUFACTURING_OVERHEAD = 'manufacturing_overhead';
    const TYPE_PACKAGING = 'packaging';
    const TYPE_SHIPPING_IN = 'shipping_in';
    const TYPE_DUTIES_TAXES = 'duties_taxes';

    /**
     * Get all components for a product
     */
    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Get total cost from components
     */
    public function scopeWithTotal($query)
    {
        return $query->selectRaw('*, (quantity * unit_price) as calculated_total')
            ->orderBy('component_type')
            ->orderBy('created_at');
    }

    /**
     * Get product relationship
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Item::class, 'product_id');
    }

    /**
     * Get source document
     */
    public function sourceDocument()
    {
        return $this->morphTo('source', 'source_type', 'source_id');
    }
}
