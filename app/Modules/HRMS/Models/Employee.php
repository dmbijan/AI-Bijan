<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'hrms_employees';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_no',
        'ic_number',
        'passport_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'address_line1',
        'address_line2',
        'city',
        'state',
        'postcode',
        'country',
        'department_id',
        'designation_id',
        'employment_type',
        'date_joined',
        'contract_end_date',
        'probation_end_date',
        'status',
        'last_worked_date',
        'termination_reason',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'bank_name',
        'bank_account_no',
        'epf_no',
        'soeso_no',
        'eis_no',
        'pcb_ref',
        'metadata',
        'last_active_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_joined' => 'date',
        'contract_end_date' => 'date',
        'probation_end_date' => 'date',
        'last_worked_date' => 'date',
        'metadata' => 'array',
        'last_active_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the employee.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the associated user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the department of this employee.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the designation of this employee.
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Get current shift assignment.
     */
    public function currentShift(): BelongsTo
    {
        return $this->hasOne(EmployeeShift::class)
            ->whereNull('effective_to')
            ->orWhere('effective_to', '>=', now()->toDateString())
            ->orderBy('effective_from', 'desc');
    }

    /**
     * Get all shift assignments.
     */
    public function shifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class, 'employee_id');
    }

    /**
     * Get leave requests for this employee.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get attendance records.
     */
    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get overtime requests.
     */
    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    /**
     * Get employee documents.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    /**
     * Get full name attribute.
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . ($this->last_name ?? ''));
    }

    /**
     * Check if employee is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if employee is on probation.
     */
    public function isOnProbation(): bool
    {
        return $this->status === 'active' && 
               $this->probation_end_date && 
               $this->probation_end_date->isFuture();
    }

    /**
     * Check if contract is expiring soon (within 30 days).
     */
    public function isContractExpiring(): bool
    {
        return $this->contract_end_date && 
               $this->contract_end_date->isFuture() && 
               $this->contract_end_date->diffInDays(now()) <= 30;
    }

    /**
     * Calculate years of service.
     */
    public function getYearsOfServiceAttribute(): float
    {
        if (!$this->date_joined) {
            return 0.0;
        }
        return $this->date_joined->floatDiffInYears(now());
    }

    /**
     * Get age from date of birth.
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->date_of_birth) {
            return null;
        }
        return $this->date_of_birth->age;
    }

    /**
     * Scope active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope by department.
     */
    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    /**
     * Scope by employment type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('employment_type', $type);
    }

    /**
     * Generate next employee number.
     */
    public static function generateEmployeeNo(): string
    {
        $year = now()->format('Y');
        $lastNo = static::whereYear('created_at', $year)
            ->orderBy('employee_no', 'desc')
            ->value('employee_no');

        if ($lastNo) {
            $number = intval(substr($lastNo, -4)) + 1;
        } else {
            $number = 1;
        }

        return 'EMP-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
