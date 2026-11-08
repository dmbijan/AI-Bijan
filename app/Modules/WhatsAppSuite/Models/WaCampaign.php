<?php

namespace App\Modules\WhatsAppSuite\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Kempen broadcast WhatsApp — blast pukal dengan anti-ban.
 *
 * Berasal daripada WasapBot MY (Bulk Messaging, Advance Anti-Banned Delay).
 */
class WaCampaign extends Model
{
    use BelongsToTenant;

    protected $table = 'wa_campaigns';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_RUNNING = 'running';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'wa_device_id',
        'name',
        'message',
        'media_url',
        'target_labels',
        'recipients',
        'status',
        'scheduled_at',
        'started_at',
        'completed_at',
        'delay_min_seconds',
        'delay_max_seconds',
        'pause_every',
        'pause_duration_seconds',
        'total_recipients',
        'sent_count',
        'failed_count',
        'skipped_count',
        'created_by',
    ];

    protected $casts = [
        'target_labels' => 'array',
        'recipients' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'delay_min_seconds' => 'integer',
        'delay_max_seconds' => 'integer',
        'pause_every' => 'integer',
        'pause_duration_seconds' => 'integer',
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'skipped_count' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'delay_min_seconds' => 30,
        'delay_max_seconds' => 120,
        'pause_every' => 20,
        'pause_duration_seconds' => 300,
        'total_recipients' => 0,
        'sent_count' => 0,
        'failed_count' => 0,
        'skipped_count' => 0,
    ];

    public function peratus(): float
    {
        if ($this->total_recipients === 0) {
            return 0.0;
        }

        return round(($this->sent_count / $this->total_recipients) * 100, 2);
    }

    public function sedangBerjalan(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }
}
