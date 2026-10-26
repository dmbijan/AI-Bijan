<?php

namespace App\Modules\SCM\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderLine extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_lines';

    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'item_code',
        'item_name',
        'description',
        'quantity_ordered',
        'unit_price',
        'uom',
        'quantity_received',
        'line_total',
        'is_taxable',
        'tax_rate',
        'tax_amount',
        'delivery_status',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
