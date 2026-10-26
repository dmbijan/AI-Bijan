<?php

namespace App\Modules\Orders\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnifiedOrderItem extends Model
{
    use BelongsToTenant;

    protected $table = 'unified_order_items';

    protected $fillable = [
        'tenant_id',
        'unified_order_id',
        'product_id',
        'sku',
        'name',
        'description',
        'variant',
        'quantity',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'line_total',
        'unit_cost',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    protected $attributes = [
        'quantity' => 1,
        'unit_price' => 0,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'line_total' => 0,
        'unit_cost' => 0,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(UnifiedOrder::class, 'unified_order_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Item::class, 'product_id');
    }

    /**
     * Kira semula line_total daripada komponen.
     */
    public function kiraLineTotal(): void
    {
        $this->line_total = round(
            ((float) $this->quantity * (float) $this->unit_price)
            - (float) $this->discount_amount
            + (float) $this->tax_amount,
            2
        );
    }

    /**
     * Jumlah kos baris.
     */
    public function jumlahKos(): float
    {
        return round((float) $this->quantity * (float) $this->unit_cost, 2);
    }

    /**
     * Margin baris.
     */
    public function margin(): float
    {
        return round((float) $this->line_total - $this->jumlahKos(), 2);
    }
}
