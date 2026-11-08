<?php

namespace App\Modules\WhatsAppSuite\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Peranti WhatsApp — satu nombor/akaun yang disambung.
 *
 * Berasal daripada WasapBot MY (wasapbot.my).
 */
class WaDevice extends Model
{
    use BelongsToTenant;

    protected $table = 'wa_devices';

    public const STATUS_CONNECTED = 'connected';

    public const STATUS_DISCONNECTED = 'disconnected';

    public const STATUS_PAIRING = 'pairing';

    public const STATUS_BANNED = 'banned';

    protected $fillable = [
        'tenant_id',
        'name',
        'phone_number',
        'session_id',
        'status',
        'qr_code',
        'paired_at',
        'last_seen_at',
        'warmup_enabled',
        'warmup_started_at',
        'warmup_stage',
        'daily_sent_count',
        'daily_limit',
        'settings',
    ];

    protected $casts = [
        'paired_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'warmup_enabled' => 'boolean',
        'warmup_started_at' => 'datetime',
        'warmup_stage' => 'integer',
        'daily_sent_count' => 'integer',
        'daily_limit' => 'integer',
        'settings' => 'array',
    ];

    protected $attributes = [
        'status' => self::STATUS_DISCONNECTED,
        'warmup_enabled' => false,
        'warmup_stage' => 0,
        'daily_sent_count' => 0,
        'daily_limit' => 200,
    ];

    public function bersambung(): bool
    {
        return $this->status === self::STATUS_CONNECTED;
    }

    public function bolehHantar(): bool
    {
        return $this->bersambung() && $this->daily_sent_count < $this->daily_limit;
    }
}
