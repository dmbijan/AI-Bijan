<?php

namespace App\Modules\OmniOrder\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Sambungan saluran jualan — kredensial & status sambungan.
 *
 * Berasal daripada Luxana (TikTok Shop, Shopee, WooCommerce, Shopify...).
 */
class OmniChannelConnection extends Model
{
    use BelongsToTenant;

    protected $table = 'omni_channel_connections';

    public const STATUS_CONNECTED = 'connected';

    public const STATUS_DISCONNECTED = 'disconnected';

    public const STATUS_ERROR = 'error';

    protected $fillable = [
        'tenant_id',
        'channel',
        'name',
        'shop_id',
        'credentials',
        'webhook_secret',
        'status',
        'last_synced_at',
        'last_error',
        'auto_approve',
        'sync_interval_minutes',
        'settings',
    ];

    protected $casts = [
        'credentials' => 'array',
        'settings' => 'array',
        'auto_approve' => 'boolean',
        'sync_interval_minutes' => 'integer',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [
        'credentials',
        'webhook_secret',
    ];

    protected $attributes = [
        'auto_approve' => false,
        'sync_interval_minutes' => 15,
    ];

    public function bersambung(): bool
    {
        return $this->status === self::STATUS_CONNECTED;
    }
}
