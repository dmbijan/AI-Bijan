<?php

namespace App\Modules\HRMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'hrms_attendance';

    protected $fillable = [
        'employee_id',
        'date',
        'check_in_time',
        'check_out_time',
        'total_hours',
        'status',
        'minutes_late',
        'overtime_hours',
        'check_in_method',
        'check_in_location',
        'check_out_location',
        'check_in_photo',
        'check_out_photo',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'total_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
    ];

    /**
     * Get the employee.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Calculate total hours worked if not set.
     */
    public function calculateTotalHours(): float
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return 0.0;
        }

        $checkIn = strtotime($this->check_in_time);
        $checkOut = strtotime($this->check_out_time);

        if ($checkOut <= $checkIn) {
            $checkOut += 86400; // Add one day for overnight shifts
        }

        $hours = ($checkOut - $checkIn) / 3600;
        
        $this->update(['total_hours' => round($hours, 2)]);
        return $hours;
    }

    /**
     * Check if employee has checked in.
     */
    public function hasCheckedIn(): bool
    {
        return !empty($this->check_in_time);
    }

    /**
     * Check if employee has checked out.
     */
    public function hasCheckedOut(): bool
    {
        return !empty($this->check_out_time);
    }

    /**
     * Scope by date range.
     */
    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope present records.
     */
    public function scopePresent($query)
    {
        return $query->whereIn('status', ['present', 'late']);
    }
}
