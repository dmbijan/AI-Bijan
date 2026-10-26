<?php

namespace App\Modules\Payroll\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use BelongsToTenant;

    protected $table = 'employees';

    protected $fillable = [
        'tenant_id',
        'name',
        'ic_no',
        'epf_no',
        'socso_no',
        'eis_no',
        'tax_no',
        'position',
        'department',
        'start_date',
        'end_date',
        'employment_type',
        'basic_salary',
        'fixed_allowance',
        'salary_currency',
        'bank_name',
        'bank_account_no',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'basic_salary' => 'decimal:2',
        'fixed_allowance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns this employee.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get overtime records for this employee.
     */
    public function overtimeRecords(): HasMany
    {
        return $this->hasMany(OvertimeRecord::class);
    }

    /**
     * Get payroll run details for this employee.
     */
    public function payrollDetails(): HasMany
    {
        return $this->hasMany(PayrollRunDetail::class);
    }

    /**
     * Check if employee is currently active.
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // If end_date is set and has passed, employee is no longer active
        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Calculate monthly hourly rate.
     * Formula: Basic Salary ÷ 208 hours (26 days × 8 hours)
     */
    public function getHourlyRateAttribute(): float
    {
        if ($this->basic_salary <= 0) {
            return 0;
        }

        return round($this->basic_salary / 208, 2);
    }

    /**
     * Get total overtime pay for a specific period.
     */
    public function getTotalOvertimeForPeriod($startDate, $endDate): float
    {
        return $this->overtimeRecords()
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->sum('ot_pay');
    }
}
