<?php

namespace App\Modules\Projects\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timesheet extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'timesheets';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'task_id',
        'user_id',
        'work_date',
        'hours',
        'note',
        'billable',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'work_date' => 'date',
        'hours' => 'decimal:2',
        'billable' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the timesheet.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the task.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the user who logged the time.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the approver.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope billable entries.
     */
    public function scopeBillable($query)
    {
        return $query->where('billable', true);
    }

    /**
     * Check if timesheet is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'diluluskan';
    }

    /**
     * Calculate billable amount.
     */
    public function getBillableAmountAttribute(): float
    {
        if (!$this->billable || !$this->isApproved()) {
            return 0;
        }

        // Get rate from config or use default
        $rate = config('projects.timesheet.billable_rates.default', 100);
        
        // Could be enhanced to get specific role rate
        return round($this->hours * $rate, 2);
    }
}
