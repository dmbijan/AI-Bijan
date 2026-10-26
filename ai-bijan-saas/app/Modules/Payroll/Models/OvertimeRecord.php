<?php

namespace App\Modules\Payroll\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRecord extends Model
{
    use BelongsToTenant;

    protected $table = 'overtime_records';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'date',
        'day_type',
        'hours_worked',
        'hourly_rate',
        'multiplier',
        'ot_pay',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
        'hours_worked' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'multiplier' => 'decimal:2',
        'ot_pay' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns this overtime record.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the employee who worked overtime.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the approver.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Scope for approved overtime records.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for pending overtime records.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get day type label.
     */
    public function getDayTypeLabelAttribute(): string
    {
        return match ($this->day_type) {
            'regular' => 'Hari Biasa (1.5x)',
            'rest_day' => 'Hari Rehat (2x)',
            'public_holiday' => 'Hari Kelepasan Am (3x)',
            default => $this->day_type,
        };
    }
}
