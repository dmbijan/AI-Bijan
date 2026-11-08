<?php

namespace App\Modules\WhatsAppSuite\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Mesej WhatsApp keluar/masuk — log penghantaran.
 *
 * Berasal daripada WasapBot MY.
 */
class WaMessage extends Model
{
    use BelongsToTenant;

    protected $table = 'wa_messages';

    public const DIRECTION_OUTGOING = 'outgoing';

    public const DIRECTION_INCOMING = 'incoming';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENT = 'sent';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_READ = 'read';

    public const STATUS_FAILED = 'failed';

    public const KIND_NOTIFICATION = 'notification';

    public const KIND_BROADCAST = 'broadcast';

    public const KIND_AUTO_REPLY = 'auto_reply';

    public const KIND_CHATBOT = 'chatbot';

    public const KIND_MANUAL = 'manual';

    protected $fillable = [
        'tenant_id',
        'wa_device_id',
        'wa_contact_id',
        'wa_campaign_id',
        'direction',
        'kind',
        'to_number',
        'from_number',
        'body',
        'media_url',
        'status',
        'external_id',
        'error_message',
        'sent_at',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    protected $attributes = [
        'direction' => self::DIRECTION_OUTGOING,
        'kind' => self::KIND_MANUAL,
        'status' => self::STATUS_QUEUED,
    ];

    public function berjaya(): bool
    {
        return in_array($this->status, [self::STATUS_SENT, self::STATUS_DELIVERED, self::STATUS_READ], true);
    }
}
