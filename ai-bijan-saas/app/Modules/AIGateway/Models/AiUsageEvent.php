<?php

namespace App\Modules\AIGateway\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class AiUsageEvent extends Model
{
    use BelongsToTenant;

    protected $table = 'ai_usage_events';

    public $timestamps = true;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'agent_key',
        'source_module',
        'provider_model_id',
        'model_display_name',
        'tier',
        'task_type',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'credits_charged',
        'cost_rm',
        'latency_ms',
        'status',
        'error_message',
        'correlation_id',
        'metadata',
    ];

    protected $casts = [
        'input_tokens' => 'integer',
        'output_tokens' => 'integer',
        'total_tokens' => 'integer',
        'credits_charged' => 'integer',
        'cost_rm' => 'decimal:4',
        'latency_ms' => 'integer',
        'metadata' => 'array',
    ];

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_CACHED = 'cached';
    const STATUS_BLOCKED = 'blocked';

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    public function scopeBetween($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeByModel($query, string $modelId)
    {
        return $query->where('provider_model_id', $modelId);
    }

    public function scopeByAgent($query, string $agentKey)
    {
        return $query->where('agent_key', $agentKey);
    }
}
