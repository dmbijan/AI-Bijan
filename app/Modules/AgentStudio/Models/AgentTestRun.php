<?php

namespace App\Modules\AgentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentTestRun extends Model
{
    use BelongsToTenant;

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_PASSED = 'passed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'agent_blueprint_id', 'name', 'prompt', 'expected_output', 'actual_output',
        'score', 'passed', 'metrics', 'duration_ms', 'tokens_used', 'status', 'cost_credits',
        'error_message',
    ];

    protected $casts = [
        'metrics' => 'array',
        'tokens_used' => 'array',
        'score' => 'float',
        'passed' => 'boolean',
        'duration_ms' => 'integer',
        'cost_credits' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'passed' => false,
        'cost_credits' => 0,
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AgentBlueprint::class, 'agent_blueprint_id');
    }

    public function isPassed(): bool
    {
        return (bool) $this->passed;
    }
}
