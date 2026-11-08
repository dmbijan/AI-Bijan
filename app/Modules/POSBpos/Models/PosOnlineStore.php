<?php

namespace App\Modules\POSBpos\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Kedai online — menu awam dengan pesanan pelanggan sebelum ke kaunter.
 *
 * Berasal daripada B-POS ciri `online_store`.
 */
class PosOnlineStore extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_online_stores';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_LIVE = 'live';

    public const STATUS_PAUSED = 'paused';

    protected $fillable = [
        'tenant_id',
        'register_id',
        'name',
        'slug',
        'description',
        'logo_url',
        'banner_url',
        'status',
        'allow_pickup',
        'allow_delivery',
        'minimum_order_sen',
        'delivery_fee_sen',
        'opening_hours',
        'settings',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'allow_pickup' => 'boolean',
        'allow_delivery' => 'boolean',
        'minimum_order_sen' => 'integer',
        'delivery_fee_sen' => 'integer',
        'opening_hours' => 'array',
        'settings' => 'array',
        'published_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'allow_pickup' => true,
        'allow_delivery' => false,
        'minimum_order_sen' => 0,
        'delivery_fee_sen' => 0,
    ];

    public function aktif(): bool
    {
        return $this->status === self::STATUS_LIVE;
    }
}
