<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashShift extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'cash_shifts';

    protected $fillable = [
        'tenant_id',
        'register_id',
        'user_id',
        'opened_by',
        'closed_by',
        'status',
        'opening_cash',
        'closing_cash',
        'actual_cash',
        'expected_cash',
        'cash_discrepancy',
        'cash_sales_total',
        'non_cash_sales_total',
        'transactions_count',
        'shift_date',
        'opened_at',
        'closed_at',
        'locked_at',
        'notes',
        'close_notes',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_discrepancy' => 'decimal:2',
        'cash_sales_total' => 'decimal:2',
        'non_cash_sales_total' => 'decimal:2',
        'transactions_count' => 'integer',
        'shift_date' => 'date',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    /**
     * Get the register this shift belongs to.
     */
    public function register(): BelongsTo
    {
        return $this->belongsTo(POSRegister::class, 'register_id');
    }

    /**
     * Get the user who opened this shift.
     */
    public function opener(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'opened_by');
    }

    /**
     * Get the user who closed this shift.
     */
    public function closer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'closed_by');
    }

    /**
     * Get the sales for this shift.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(POSSale::class, 'cash_shift_id');
    }

    /**
     * Juruwang yang memegang syif (BAB 88 / T-80).
     * `opened_by` ialah penyelia yang membuka; `user_id` ialah juruwang.
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /** Pergerakan tunai dalam syif ini (drop/expense/pickup). */
    public function cashMovements(): HasMany
    {
        return $this->hasMany(POSShiftCashMovement::class, 'cash_shift_id');
    }

    /** Jumlah bersih pergerakan tunai (negatif = wang keluar laci). */
    public function netCashMovement(): float
    {
        return (float) $this->cashMovements->sum(fn (POSShiftCashMovement $m) => $m->kesanPadaLaci());
    }

    /**
     * Scope by tenant.
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope open shifts only.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Check if shift has discrepancy.
     */
    public function hasDiscrepancy(float $maxAllowed = 10.00): bool
    {
        if (!$this->cash_discrepancy) {
            return false;
        }
        
        return abs($this->cash_discrepancy) > $maxAllowed;
    }
}
