<?php

namespace App\Modules\OmniOrder\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Pesanan omnichannel — satu baris gilir untuk semua saluran jualan.
 *
 * Berasal daripada Luxana (luxana.com.my): TikTok Shop, Shopee,
 * WooCommerce, Shopify, Fluent Forms digabung dalam satu papan pemuka.
 */
class OmniOrder extends Model
{
    use BelongsToTenant;

    protected $table = 'omni_orders';

    public const CHANNEL_TIKTOK = 'tiktok_shop';

    public const CHANNEL_SHOPEE = 'shopee';

    public const CHANNEL_WOOCOMMERCE = 'woocommerce';

    public const CHANNEL_SHOPIFY = 'shopify';

    public const CHANNEL_LAZADA = 'lazada';

    public const CHANNEL_FLUENT = 'fluent_forms';

    public const CHANNEL_MANUAL = 'manual';

    public const STATUS_NEW = 'new';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_PACKED = 'packed';

    public const STATUS_SHIPPED = 'shipped';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_RETURNED = 'returned';

    public const PAYMENT_COD = 'cod';

    public const PAYMENT_BANK = 'bank';

    public const PAYMENT_PREPAID = 'prepaid';

    protected $fillable = [
        'tenant_id',
        'order_no',
        'external_order_id',
        'channel',
        'status',
        'customer_name',
        'customer_phone',
        'customer_address',
        'customer_city',
        'customer_state',
        'customer_postcode',
        'total_sen',
        'payment_method',
        'payment_status',
        'courier',
        'tracking_no',
        'awb_url',
        'items',
        'is_duplicate',
        'duplicate_of_id',
        'is_high_risk',
        'risk_score',
        'risk_reason',
        'is_high_value',
        'delivery_failure_reason',
        'cod_payout_status',
        'cod_payout_at',
        'approved_at',
        'packed_at',
        'shipped_at',
        'delivered_at',
        'payload',
        'created_by',
    ];

    protected $casts = [
        'items' => 'array',
        'payload' => 'array',
        'total_sen' => 'integer',
        'is_duplicate' => 'boolean',
        'is_high_risk' => 'boolean',
        'risk_score' => 'integer',
        'is_high_value' => 'boolean',
        'approved_at' => 'datetime',
        'packed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cod_payout_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_NEW,
        'payment_method' => self::PAYMENT_PREPAID,
        'payment_status' => 'unpaid',
        'is_duplicate' => false,
        'is_high_risk' => false,
        'risk_score' => 0,
        'is_high_value' => false,
        'total_sen' => 0,
    ];

    public function jumlah(): float
    {
        return $this->total_sen / 100;
    }

    public function cod(): bool
    {
        return $this->payment_method === self::PAYMENT_COD;
    }

    public function bolehHantar(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_PACKED], true);
    }

    public static function senaraiSaluran(): array
    {
        return [
            self::CHANNEL_TIKTOK => 'TikTok Shop',
            self::CHANNEL_SHOPEE => 'Shopee',
            self::CHANNEL_WOOCOMMERCE => 'WooCommerce',
            self::CHANNEL_SHOPIFY => 'Shopify',
            self::CHANNEL_LAZADA => 'Lazada',
            self::CHANNEL_FLUENT => 'Fluent Forms',
            self::CHANNEL_MANUAL => 'Manual',
        ];
    }
}
