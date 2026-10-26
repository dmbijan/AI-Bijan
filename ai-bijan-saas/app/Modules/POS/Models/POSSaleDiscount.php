<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class POSSaleDiscount extends Model
{
    use HasFactory;

    protected $table = 'pos_sale_discounts';

    protected $fillable = [
        'sale_id',
        'type',
        'code',
        'value',
        'amount',
        'approved_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the sale this discount belongs to.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(POSSale::class, 'sale_id');
    }

    /**
     * Get the approver.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }
}
