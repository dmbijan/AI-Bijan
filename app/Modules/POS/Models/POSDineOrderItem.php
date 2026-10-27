<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Baris pesanan dine-in (BAB 88 / T-76).
 *
 * `item_name`, `unit_price` dan `modifiers` ialah SNAPSHOT: perubahan harga
 * menu kemudian tidak mengubah sejarah pesanan (corak B-POS).
 */
class POSDineOrderItem extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_dine_order_items';

    protected $fillable = [
        'tenant_id',
        'dine_order_id',
        'item_id',
        'item_name',
        'unit_price',
        'quantity',
        'modifiers',
        'modifier_total',
        'line_total',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'modifiers' => 'array',
        'modifier_total' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    protected $attributes = [
        'quantity' => 1,
        'modifier_total' => 0,
        'line_total' => 0,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(POSDineOrder::class, 'dine_order_id');
    }
}
