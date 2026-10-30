<?php

namespace App\Modules\POS\Models;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class POSSaleItem extends Model
{
    use HasFactory;

    protected $table = 'pos_sale_items';

    protected $fillable = [
        'sale_id',
        'product_id',
        'product_name',
        'product_sku',
        'quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_amount',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    /**
     * Get the sale this item belongs to.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(POSSale::class, 'sale_id');
    }

    /**
     * Get the product if available.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'product_id');
    }
}
