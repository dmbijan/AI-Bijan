<?php

namespace App\Modules\Reminder\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderDelivery extends Model
{
    use BelongsToTenant;

    protected $table = 'reminder_deliveries';

    public const CHANNEL_IN_APP = 'in_app';
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_WHATSAPP = 'whatsapp';
    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_PUSH = 'push';

    public const STATUS_SENT = 'sent';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_FAILED = 'failed';
    public const STATUS_BOUNCED = 'bounced';

    protected $fillable = [
        'tenant_id',
        'reminder_id',
        'channel',
        'status',
        'recipient',
        'content',
        'error_message',
        'duration_ms',
    ];

    protected $casts = [
        'duration_ms' => 'integer',
    ];

    protected $attributes = [
        'channel' => self::CHANNEL_IN_APP,
        'status' => self::STATUS_SENT,
    ];

    public function reminder(): BelongsTo
    {
        return $this->belongsTo(Reminder::class, 'reminder_id');
    }
}
