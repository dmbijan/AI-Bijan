<?php

namespace App\Modules\Expense\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Baris tuntutan perbelanjaan.
 */
class ExpenseClaimItem extends Model
{
    use BelongsToTenant;

    protected $table = 'expense_claim_items';

    protected $fillable = [
        'tenant_id',
        'expense_claim_id',
        'expense_category_id',
        'line_number',
        'item_date',
        'description',
        'vendor_name',
        'quantity',
        'unit_price',
        'tax_amount',
        'total_amount',
        'approved_amount',
        'has_receipt',
        'receipt_required',
        'is_billable',
        'cost_center',
        'project_code',
    ];

    protected $casts = [
        'line_number' => 'integer',
        'item_date' => 'date',
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'has_receipt' => 'boolean',
        'receipt_required' => 'boolean',
        'is_billable' => 'boolean',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_claim_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /**
     * Adakah baris ini lengkap untuk kelulusan?
     */
    public function lengkap(): bool
    {
        if ($this->receipt_required && ! $this->has_receipt) {
            return false;
        }

        return (float) $this->total_amount > 0;
    }
}
