<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class POSSalePayment extends Model
{
    use HasFactory;

    protected $table = 'pos_sale_payments';

    protected $fillable = [
        'sale_id',
        'payment_method',
        'amount',
        'reference_number',
        'payment_details',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
    ];

    /**
     * Get the sale this payment belongs to.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(POSSale::class, 'sale_id');
    }
}
