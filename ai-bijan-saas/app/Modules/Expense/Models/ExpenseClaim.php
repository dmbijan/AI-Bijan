<?php

namespace App\Modules\Expense\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tuntutan perbelanjaan staff (reimbursement claim).
 *
 * Aliran status:
 *   draft → submitted → approved → reimbursed
 *                    ↘ rejected ↗ (draft)
 */
class ExpenseClaim extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_REIMBURSED = 'reimbursed';

    /** Peta peralihan status yang sah. */
    public const ALIRAN = [
        self::STATUS_DRAFT => [self::STATUS_SUBMITTED],
        self::STATUS_SUBMITTED => [self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_DRAFT],
        self::STATUS_APPROVED => [self::STATUS_REIMBURSED, self::STATUS_REJECTED],
        self::STATUS_REJECTED => [self::STATUS_DRAFT],
        self::STATUS_REIMBURSED => [],
    ];

    public const TYPE_REIMBURSEMENT = 'reimbursement';
    public const TYPE_MILEAGE = 'mileage';
    public const TYPE_PER_DIEM = 'per_diem';
    public const TYPE_PETTY_CASH = 'petty_cash';

    public const TYPES = [
        self::TYPE_REIMBURSEMENT,
        self::TYPE_MILEAGE,
        self::TYPE_PER_DIEM,
        self::TYPE_PETTY_CASH,
    ];

    protected $table = 'expense_claims';

    protected $fillable = [
        'tenant_id',
        'claim_no',
        'claimant_user_id',
        'approver_user_id',
        'delegate_user_id',
        'expense_id',
        'service_project_id',
        'title',
        'purpose',
        'status',
        'claim_type',
        'payment_method',
        'claim_date',
        'period_start',
        'period_end',
        'due_date',
        'currency',
        'total_amount',
        'approved_amount',
        'reimbursed_amount',
        'mileage_distance_km',
        'mileage_rate',
        'mileage_amount',
        'per_diem_days',
        'per_diem_rate',
        'per_diem_amount',
        'per_diem_location',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'reimbursed_at',
        'rejection_reason',
        'is_flagged',
        'flag_reason',
        'notes',
    ];

    protected $casts = [
        'claim_date' => 'date',
        'period_start' => 'date',
        'period_end' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'reimbursed_amount' => 'decimal:2',
        'mileage_distance_km' => 'decimal:2',
        'mileage_rate' => 'decimal:4',
        'mileage_amount' => 'decimal:2',
        'per_diem_days' => 'integer',
        'per_diem_rate' => 'decimal:2',
        'per_diem_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'reimbursed_at' => 'datetime',
        'is_flagged' => 'boolean',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ExpenseClaimItem::class, 'expense_claim_id')->orderBy('line_number');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ExpenseReceipt::class, 'expense_claim_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(ExpenseClaimApproval::class, 'expense_claim_id')->orderBy('step');
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }

    /**
     * Bolehkah status ditukar ke status baharu?
     */
    public function bolehTukarKe(string $status): bool
    {
        return in_array($status, self::ALIRAN[$this->status] ?? [], true);
    }

    /**
     * Baki belum dibayar balik.
     */
    public function baki(): float
    {
        return round((float) $this->approved_amount - (float) $this->reimbursed_amount, 2);
    }

    /**
     * Adakah semua baris yang perlu resit sudah ada resitnya?
     */
    public function lengkapDenganResit(): bool
    {
        foreach ($this->items as $item) {
            if ($item->receipt_required && ! $item->has_receipt) {
                return false;
            }
        }

        return true;
    }
}
