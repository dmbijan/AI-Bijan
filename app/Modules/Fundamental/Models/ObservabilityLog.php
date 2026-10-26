<?php

namespace App\Modules\Fundamental\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Log observability terstruktur — Levels, channel, correlation ID.
 */
class ObservabilityLog extends Model
{
    use BelongsToTenant;

    public const LEVEL_DEBUG = 'debug';
    public const LEVEL_INFO = 'info';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';
    public const LEVEL_CRITICAL = 'critical';

    public const LEVELS = [
        self::LEVEL_DEBUG,
        self::LEVEL_INFO,
        self::LEVEL_WARNING,
        self::LEVEL_ERROR,
        self::LEVEL_CRITICAL,
    ];

    /** Channel — untuk penapis. */
    public const CHANNEL_APP = 'app';
    public const CHANNEL_API = 'api';
    public const CHANNEL_QUEUE = 'queue';
    public const CHANNEL_AI = 'ai';
    public const CHANNEL_PAYMENT = 'payment';
    public const CHANNEL_SECURITY = 'security';
    public const CHANNEL_WEBHOOK = 'webhook';

    protected $table = 'observability_logs';

    protected $fillable = [
        'tenant_id',
        'level',
        'channel',
        'event_name',
        'correlation_id',
        'request_id',
        'actor_user_id',
        'message',
        'context',
        'duration_ms',
        'service',
        'host',
    ];

    protected $casts = [
        'context' => 'array',
        'duration_ms' => 'integer',
    ];
}
