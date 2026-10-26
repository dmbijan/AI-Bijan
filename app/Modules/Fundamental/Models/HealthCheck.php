<?php

namespace App\Modules\Fundamental\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Health check — status satu komponen (DB, cache, queue, storage, API luar).
 */
class HealthCheck extends Model
{
    use BelongsToTenant;

    public const STATUS_HEALTHY = 'healthy';
    public const STATUS_DEGRADED = 'degraded';
    public const STATUS_UNHEALTHY = 'unhealthy';
    public const STATUS_UNKNOWN = 'unknown';

    public const COMPONENTS = [
        'database',
        'cache',
        'queue',
        'storage',
        'mail',
        'ai_gateway',
        'payments',
        'whatsapp',
        'scheduler',
    ];

    protected $table = 'health_checks';

    protected $fillable = [
        'tenant_id',
        'check_key',
        'name',
        'component',
        'status',
        'duration_ms',
        'message',
        'context',
        'checked_at',
    ];

    protected $casts = [
        'duration_ms' => 'integer',
        'context' => 'array',
        'checked_at' => 'datetime',
    ];

    public function isHealthy(): bool
    {
        return $this->status === self::STATUS_HEALTHY;
    }
}
