<?php

namespace App\Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationLine extends Model
{
    protected $table = 'quotation_lines';
    
    protected $fillable = [
        'quotation_id',
        'item_id',
        'item_code',
        'item_name',
        'description',
        'quantity',
        'unit_price',
        'discount_percentage',
        'line_total',
        'uom',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    /**
     * Get quotation that owns the line.
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * Calculate line total.
     */
    public function calculateTotal(): float
    {
        $baseTotal = $this->quantity * $this->unit_price;
        $discountAmount = $baseTotal * ($this->discount_percentage / 100);
        
        return $baseTotal - $discountAmount;
    }
}
