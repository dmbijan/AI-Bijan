<?php

namespace App\Modules\Payroll\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    use BelongsToTenant;

    protected $table = 'payroll_runs';

    protected $fillable = [
        'tenant_id',
        'name',
        'period_start',
        'period_end',
        'pay_date',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'total_gross',
        'total_epf_ee',
        'total_socso_ee',
        'total_eis_ee',
        'total_pcb',
        'total_other_deductions',
        'total_net_pay',
        'total_epf_er',
        'total_socso_er',
        'total_eis_er',
        'total_hrdf',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'pay_date' => 'date',
        'approved_at' => 'datetime',
        'total_gross' => 'decimal:2',
        'total_epf_ee' => 'decimal:2',
        'total_socso_ee' => 'decimal:2',
        'total_eis_ee' => 'decimal:2',
        'total_pcb' => 'decimal:2',
        'total_other_deductions' => 'decimal:2',
        'total_net_pay' => 'decimal:2',
        'total_epf_er' => 'decimal:2',
        'total_socso_er' => 'decimal:2',
        'total_eis_er' => 'decimal:2',
        'total_hrdf' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns this payroll run.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the user who created this payroll run.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the user who approved this payroll run.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Get all employee details for this payroll run.
     */
    public function details(): HasMany
    {
        return $this->hasMany(PayrollRunDetail::class);
    }

    /**
     * Check if payroll run can be modified (only draft status).
     */
    public function canModify(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if payroll run is finalized.
     */
    public function isFinalized(): bool
    {
        return in_array($this->status, ['processed', 'cancelled']);
    }
}
