<?php

namespace App\Modules\Marketing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use BelongsToTenant;

    protected $table = 'marketing_workflows';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'trigger_type',
        'trigger_config',
        'steps',
        'is_active',
        'execution_count',
        'success_count',
        'failure_count',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'steps' => 'array',
        'is_active' => 'boolean',
        'execution_count' => 'integer',
        'success_count' => 'integer',
        'failure_count' => 'integer',
    ];

    /**
     * Get the tenant that owns the workflow.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get all workflow executions.
     */
    public function executions(): HasMany
    {
        return $this->hasMany(WorkflowExecution::class, 'workflow_id');
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    }

    /**
     * Scope active workflows.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by trigger type.
     */
    public function scopeByTrigger($query, string $triggerType)
    {
        return $query->where('trigger_type', $triggerType);
    }

    /**
     * Record execution.
     */
    public function recordExecution(bool $success): void
    {
        $this->increment('execution_count');
        if ($success) {
            $this->increment('success_count');
        } else {
            $this->increment('failure_count');
        }
    }
}
