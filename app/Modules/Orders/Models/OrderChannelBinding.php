<?php

namespace App\Modules\Orders\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderChannelBinding extends Model
{
    use BelongsToTenant;

    protected $table = 'order_channel_bindings';

    public const PLATFORM_SHOPIFY = 'shopify';
    public const PLATFORM_LAZADA = 'lazada';
    public const PLATFORM_SHOPEE = 'shopee';
    public const PLATFORM_TIKTOK = 'tiktok';
    public const PLATFORM_AMAZON = 'amazon';
    public const PLATFORM_POS = 'pos';
    public const PLATFORM_SITE = 'site';
    public const PLATFORM_BOOKING = 'booking';

    protected $fillable = [
        'tenant_id',
        'platform',
        'display_name',
        'status_map',
        'payment_methods',
        'is_active',
        'stock_sync_enabled',
        'stock_push_enabled',
        'sync_interval_minutes',
        'last_pull_at',
        'last_push_at',
        'meta',
    ];

    protected $casts = [
        'status_map' => 'array',
        'payment_methods' => 'array',
        'meta' => 'array',
        'is_active' => 'boolean',
        'stock_sync_enabled' => 'boolean',
        'stock_push_enabled' => 'boolean',
        'sync_interval_minutes' => 'integer',
        'last_pull_at' => 'datetime',
        'last_push_at' => 'datetime',
    ];

    protected $attributes = [
        'is_active' => true,
        'stock_sync_enabled' => true,
        'stock_push_enabled' => false,
        'sync_interval_minutes' => 15,
    ];

    public function aktif(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * Petakan status platform → status kanonikal.
     */
    public function petakanStatus(?string $statusPlatform): ?string
    {
        if ($statusPlatform === null) {
            return null;
        }

        $peta = $this->status_map ?? [];

        return $peta[$statusPlatform] ?? null;
    }

    /**
     * Status kanonikal → status platform (mapping balikan).
     */
    public function statusPlatform(string $statusKanonikal): ?string
    {
        $peta = $this->status_map ?? [];

        foreach ($peta as $platform => $kanonikal) {
            if ($kanonikal === $statusKanonikal) {
                return $platform;
            }
        }

        return null;
    }
}
