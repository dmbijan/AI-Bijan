<?php

namespace App\Modules\Fundamental\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Peraturan amaran — nilai metrik melepasi ambang → amaran.
 */
class AlertRule extends Model
{
    use BelongsToTenant;

    public const OP_GT = 'gt';
    public const OP_GTE = 'gte';
    public const OP_LT = 'lt';
    public const OP_LTE = 'lte';
    public const OP_EQ = 'eq';

    public const OPERATORS = [
        self::OP_GT,
        self::OP_GTE,
        self::OP_LT,
        self::OP_LTE,
        self::OP_EQ,
    ];

    public const SEVERITY_INFO = 'info';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_CRITICAL = 'critical';

    protected $table = 'alert_rules';

    protected $fillable = [
        'tenant_id',
        'rule_key',
        'name',
        'metric_key',
        'operator',
        'threshold',
        'window_minutes',
        'severity',
        'channel',
        'message_template',
        'is_enabled',
        'last_triggered_at',
        'trigger_count',
    ];

    protected $casts = [
        'threshold' => 'double',
        'window_minutes' => 'integer',
        'is_enabled' => 'boolean',
        'last_triggered_at' => 'datetime',
        'trigger_count' => 'integer',
    ];

    /**
     * Adakah nilai melepasi ambang?
     */
    public function breach(float $nilai): bool
    {
        return match ($this->operator) {
            self::OP_GT => $nilai > (float) $this->threshold,
            self::OP_GTE => $nilai >= (float) $this->threshold,
            self::OP_LT => $nilai < (float) $this->threshold,
            self::OP_LTE => $nilai <= (float) $this->threshold,
            self::OP_EQ => abs($nilai - (float) $this->threshold) < 0.0001,
            default => false,
        };
    }
}
