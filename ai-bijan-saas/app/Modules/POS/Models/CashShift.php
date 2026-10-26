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
        'opened_by',
        'closed_by',
        'status',
        'opening_cash',
        'closing_cash',
        'expected_cash',
        'cash_discrepancy',
        'shift_date',
        'opened_at',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'cash_discrepancy' => 'decimal:2',
        'shift_date' => 'date',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
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
