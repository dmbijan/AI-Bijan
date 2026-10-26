<?php

namespace App\Modules\Invoicing\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesOrderItem extends Model
{
    use HasFactory;

    protected $table = 'sales_order_items';

    protected $fillable = [
        'sales_order_id',
        'product_id',
        'product_name',
        'product_sku',
        'description',
        'quantity',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(\App\Modules\Inventory\Models\Product::class, 'product_id');
    }

    /**
     * Calculate line total based on quantity, price, discount and tax
     */
    public function calculateLineTotal(): void
    {
        $baseAmount = $this->quantity * $this->unit_price;
        
        // Apply discount
        $discountAmount = $baseAmount * ($this->discount_percentage ?? 0) / 100;
        $this->discount_amount = $discountAmount;
        
        $netAmount = $baseAmount - $discountAmount;
        
        // Apply tax
        $taxAmount = $netAmount * ($this->tax_percentage ?? 0) / 100;
        $this->tax_amount = $taxAmount;
        
        // Line total
        $this->line_total = $netAmount + $taxAmount;
        
        $this->save();
    }
}
