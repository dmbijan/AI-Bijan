<?php

namespace App\Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatutoryRate extends Model
{
    protected $table = 'statutory_rates';

    protected $fillable = [
        'tenant_id',
        'year',
        'epf_employee_rate',
        'epf_employer_base_rate',
        'epf_employer_reduced_rate',
        'epf_salary_threshold',
        'socso_max_annual_ee',
        'socso_max_annual_er',
        'eis_rate',
        'eis_max_annual',
        'hrdf_rate',
        'pcb_use_mptg_table',
        'effective_from',
        'effective_to',
        'is_active',
        'source_reference',
    ];

    protected $casts = [
        'epf_employee_rate' => 'decimal:2',
        'epf_employer_base_rate' => 'decimal:2',
        'epf_employer_reduced_rate' => 'decimal:2',
        'epf_salary_threshold' => 'integer',
        'socso_max_annual_ee' => 'decimal:2',
        'socso_max_annual_er' => 'decimal:2',
        'eis_rate' => 'decimal:2',
        'eis_max_annual' => 'decimal:2',
        'hrdf_rate' => 'decimal:2',
        'pcb_use_mptg_table' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns this statutory rate.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Scope for active rates only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for a specific year.
     */
    public function scopeForYear($query, string $year)
    {
        return $query->where('year', $year);
    }
}
