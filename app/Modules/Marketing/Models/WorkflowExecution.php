<?php

namespace App\Modules\Marketing\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pelaksanaan aliran kerja pemasaran.
 *
 * ⚠️ 27 Okt 2026: `$fillable` ada `tenant_id` tetapi kolum tiada dalam DB →
 * tiada skop tenant. Kolum ditambah oleh migrasi 2026_10_27_000400;
 * trait dipasang di sini.
 */
class WorkflowExecution extends Model
{
    use BelongsToTenant;
    protected $table = 'marketing_workflow_executions';

    protected $fillable = [
        'tenant_id',
        'workflow_id',
        'contact_id',
        'deal_id',
        'current_step',
        'status',
        'started_at',
        'completed_at',
        'error_message',
        'context_data',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'current_step' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'error_message' => 'string',
        'context_data' => 'array',
    ];

    /**
     * Get the tenant that owns the execution.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the workflow being executed.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    /**
     * Get the contact this execution is for.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    /**
     * Get the deal this execution is for.
     */
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope active executions.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['running', 'pending', 'waiting']);
    }
}
