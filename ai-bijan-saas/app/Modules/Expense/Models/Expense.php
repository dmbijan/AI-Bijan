<?php

namespace App\Modules\Expense\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Induk perbelanjaan.
 *
 * Aliran status:
 *   draft → submitted → approved → posted → paid
 *                    ↘ rejected
 *   (voided boleh dari mana-mana selepas draft)
 *
 * ⚠️ `posted` bermaksud jurnal telah dihantar ke Finance.
 */
class Expense extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_POSTED = 'posted';
    public const STATUS_PAID = 'paid';
    public const STATUS_VOIDED = 'voided';

    /** Peta peralihan status yang sah. */
    public const ALIRAN = [
        self::STATUS_DRAFT => [self::STATUS_SUBMITTED, self::STATUS_VOIDED],
        self::STATUS_SUBMITTED => [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_DRAFT, self::STATUS_VOIDED],
        self::STATUS_APPROVED => [self::STATUS_POSTED, self::STATUS_VOIDED],
        self::STATUS_REJECTED => [self::STATUS_DRAFT, self::STATUS_VOIDED],
        self::STATUS_POSTED => [self::STATUS_PAID, self::STATUS_VOIDED],
        self::STATUS_PAID => [],
        self::STATUS_VOIDED => [],
    ];

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PARTIAL = 'partial';
    public const PAYMENT_PAID = 'paid';

    protected $table = 'expenses';

    protected $fillable = [
        'tenant_id',
        'expense_no',
        'expense_category_id',
        'supplier_id',
        'service_project_id',
        'created_by_user_id',
        'approved_by_user_id',
        'status',
        'payment_status',
        'payment_method',
        'vendor_name',
        'vendor_tax_id',
        'vendor_invoice_no',
        'expense_date',
        'due_date',
        'approved_at',
        'paid_at',
        'voided_at',
        'currency',
        'exchange_rate',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'amount_myr',
        'tax_type',
        'tax_rate',
        'tax_code',
        'tax_claimable',
        'is_billable',
        'is_recurring_child',
        'recurring_id',
        'is_flagged',
        'flag_reason',
        'notes',
        'internal_notes',
        'custom_fields',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'voided_at' => 'datetime',
        'exchange_rate' => 'decimal:6',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'amount_myr' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_claimable' => 'boolean',
        'is_billable' => 'boolean',
        'is_recurring_child' => 'boolean',
        'is_flagged' => 'boolean',
        'custom_fields' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseItem::class, 'expense_id')->orderBy('line_number');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ExpenseReceipt::class, 'expense_id');
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_id');
    }

    /**
     * Bolehkah status ditukar ke status baharu?
     */
    public function bolehTukarKe(string $status): bool
    {
        return in_array($status, self::ALIRAN[$this->status] ?? [], true);
    }

    /**
     * Adakah perbelanjaan ini sudah berkesan ke kewangan?
     */
    public function berkesan(): bool
    {
        return in_array($this->status, [self::STATUS_POSTED, self::STATUS_PAID], true);
    }

    /**
     * Baki belum dijelaskan.
     */
    public function baki(): float
    {
        return round((float) $this->total_amount - (float) $this->paid_amount, 2);
    }
}
