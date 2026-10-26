<?php

namespace App\Modules\Expense\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Baris perincian perbelanjaan (banyak baris per perbelanjaan).
 */
class ExpenseItem extends Model
{
    use BelongsToTenant;

    protected $table = 'expense_items';

    protected $fillable = [
        'tenant_id',
        'expense_id',
        'expense_category_id',
        'line_number',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'cost_center',
        'department',
        'project_code',
    ];

    protected $casts = [
        'line_number' => 'integer',
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * Kira jumlah baris = (qty × harga) − diskaun + cukai
     */
    public function kiraJumlah(): float
    {
        $kasar = (float) $this->quantity * (float) $this->unit_price;

        return round($kasar - (float) $this->discount_amount + (float) $this->tax_amount, 2);
    }
}
