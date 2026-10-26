<?php

namespace App\Modules\HRMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveAllocation extends Model
{
    use HasFactory;

    protected $table = 'hrms_leave_allocations';

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'total_days',
        'used_days',
        'carried_over_days',
        'year',
        'expiry_date',
    ];

    protected $casts = [
        'year' => 'integer',
        'expiry_date' => 'date',
    ];

    /**
     * Get the employee.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Get the leave type.
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    /**
     * Get remaining days.
     */
    public function getRemainingDaysAttribute(): int
    {
        return max(0, $this->total_days - $this->used_days);
    }

    /**
     * Check if allocation is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }
}
