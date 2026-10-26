<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'hrms_shifts';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'start_time',
        'end_time',
        'break_start',
        'break_end',
        'working_hours',
        'working_days',
        'type',
        'is_active',
    ];

    protected $casts = [
        'working_days' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns the shift.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get employee shifts using this shift.
     */
    public function employeeShifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class, 'shift_id');
    }

    /**
     * Calculate break duration in hours.
     */
    public function getBreakDurationAttribute(): float
    {
        if (!$this->break_start || !$this->break_end) {
            return 0.0;
        }

        $start = strtotime($this->break_start);
        $end = strtotime($this->break_end);

        return ($end - $start) / 3600;
    }

    /**
     * Calculate net working hours (excluding break).
     */
    public function getNetWorkingHoursAttribute(): float
    {
        return max(0, $this->working_hours - $this->break_duration);
    }

    /**
     * Check if a given day of week is a working day.
     * 
     * @param int $dayOfWeek Day of week (1=Monday, 7=Sunday)
     */
    public function isWorkingDay(int $dayOfWeek): bool
    {
        return in_array($dayOfWeek, $this->working_days);
    }

    /**
     * Format working days as readable string.
     */
    public function getWorkingDaysFormattedAttribute(): string
    {
        $days = [
            1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu',
            5 => 'Fri', 6 => 'Sat', 7 => 'Sun',
        ];

        $working = collect($this->working_days)
            ->filter(fn($day) => isset($days[$day]))
            ->map(fn($day) => $days[$day])
            ->join(', ');

        return $working;
    }

    /**
     * Scope active shifts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
