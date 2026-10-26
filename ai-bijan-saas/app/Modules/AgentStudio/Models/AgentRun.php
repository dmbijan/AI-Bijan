<?php

namespace App\Modules\AgentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentRun extends Model
{
    use BelongsToTenant;

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_ESCALATED = 'escalated';

    protected $fillable = [
        'tenant_id',
        'agent_blueprint_id', 'agent_key', 'trigger_type', 'input', 'output',
        'status', 'duration_ms', 'cost_credits', 'tools_used', 'error_message', 'escalated',
    ];

    protected $casts = [
        'input' => 'array',
        'tools_used' => 'array',
        'duration_ms' => 'integer',
        'cost_credits' => 'integer',
        'escalated' => 'boolean',
    ];

    protected $attributes = [
        'trigger_type' => 'manual',
        'status' => self::STATUS_PENDING,
        'cost_credits' => 0,
        'escalated' => false,
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AgentBlueprint::class, 'agent_blueprint_id');
    }

    public function berjaya(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
