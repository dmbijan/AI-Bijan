<?php

namespace App\Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRunDetail extends Model
{
    protected $table = 'payroll_run_details';

    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'basic_salary',
        'fixed_allowance',
        'overtime_pay',
        'other_earnings',
        'gross_pay',
        'epf_ee',
        'socso_ee',
        'eis_ee',
        'pcb',
        'other_deductions',
        'net_pay',
        'epf_er',
        'socso_er',
        'eis_er',
        'hrdf',
        'statutory_year',
        'calculation_notes',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'fixed_allowance' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'other_earnings' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'epf_ee' => 'decimal:2',
        'socso_ee' => 'decimal:2',
        'eis_ee' => 'decimal:2',
        'pcb' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'epf_er' => 'decimal:2',
        'socso_er' => 'decimal:2',
        'eis_er' => 'decimal:2',
        'hrdf' => 'decimal:2',
        'calculation_notes' => 'array',
    ];

    /**
     * Get the payroll run this detail belongs to.
     */
    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    /**
     * Get the employee this detail belongs to.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate net pay from gross and deductions.
     */
    public function calculateNetPay(): float
    {
        $totalDeductions = $this->epf_ee + $this->socso_ee + $this->eis_ee + $this->pcb + $this->other_deductions;
        return round($this->gross_pay - $totalDeductions, 2);
    }

    /**
     * Get total employer cost for this employee.
     */
    public function getEmployerCostAttribute(): float
    {
        return round(
            $this->basic_salary + 
            $this->fixed_allowance + 
            $this->overtime_pay + 
            $this->epf_er + 
            $this->socso_er + 
            $this->eis_er + 
            $this->hrdf,
            2
        );
    }
}
