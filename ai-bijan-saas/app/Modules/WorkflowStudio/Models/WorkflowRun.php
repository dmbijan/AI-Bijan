<?php

namespace App\Modules\WorkflowStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowRun extends Model
{
    use BelongsToTenant;

    protected $table = 'studio_workflow_runs';

    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'workflow_id',
        'status',
        'trigger_type',
        'trigger_payload',
        'node_results',
        'current_node_id',
        'nodes_executed',
        'nodes_failed',
        'duration_ms',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'trigger_payload' => 'array',
        'node_results' => 'array',
        'nodes_executed' => 'integer',
        'nodes_failed' => 'integer',
        'duration_ms' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_RUNNING,
        'trigger_type' => 'manual',
        'nodes_executed' => 0,
        'nodes_failed' => 0,
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    public function berjaya(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
