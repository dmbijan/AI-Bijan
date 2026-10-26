<?php

namespace App\Modules\Blast\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu mesej blast — satu prospect, satu channel, satu penghantaran.
 */
class BlastMessage extends Model
{
    use BelongsToTenant;

    protected $table = 'blast_messages';

    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_BOUNCED = 'bounced';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SUPPRESSED = 'suppressed';
    public const STATUS_SKIPPED = 'skipped';
    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'prospect_id',
        'contact_id',
        'channel',
        'recipient',
        'subject',
        'body',
        'media_asset_id',
        'status',
        'provider_message_id',
        'error',
        'attempts',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'replied_at',
        'bounce_reason',
        'complaint_at',
        'latency_ms',
        'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'replied_at' => 'datetime',
        'complaint_at' => 'datetime',
        'metadata' => 'array',
        'attempts' => 'integer',
        'latency_ms' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(BlastCampaign::class, 'campaign_id');
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Prospecting\Models\Prospect::class, 'prospect_id');
    }

    public function isBerjaya(): bool
    {
        return in_array($this->status, [
            self::STATUS_SENT,
            self::STATUS_DELIVERED,
        ], true);
    }
}
