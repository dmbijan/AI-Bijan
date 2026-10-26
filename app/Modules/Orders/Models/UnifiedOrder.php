<?php

namespace App\Modules\Orders\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pesanan unified — satu tempat untuk SEMUA pesanan (marketplace, POS, site, booking, COD).
 *
 * Ini menjawab soalan yang paling kerap ditanya tenant:
 * "Berapa jualan saya hari ini, dari mana?"
 */
class UnifiedOrder extends Model
{
    use BelongsToTenant;

    protected $table = 'unified_orders';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_PAID = 'paid';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PACKED = 'packed';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';
    public const STATUS_ON_HOLD = 'on_hold';
    public const STATUS_FAILED = 'failed';

    /** Status di mana pesanan sudah "selesai" (tidak boleh ubah tanpa force). */
    public const STATUS_TERMINAL = [
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
        self::STATUS_REFUNDED,
    ];

    /** Status di mana stok sudah dikuatas. */
    public const STATUS_STOCK_COMMITTED = [
        self::STATUS_SHIPPED,
        self::STATUS_OUT_FOR_DELIVERY,
        self::STATUS_DELIVERED,
    ];

    /** Status di mana stok telah direka. */
    public const STATUS_STOCK_RELEASED = [
        self::STATUS_CANCELLED,
        self::STATUS_REFUNDED,
    ];

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_PARTIAL = 'partial';
    public const PAYMENT_REFUNDED = 'refunded';
    public const PAYMENT_FAILED = 'failed';

    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_MARKETPLACE = 'marketplace';
    public const SOURCE_POS = 'pos';
    public const SOURCE_SITE = 'site';
    public const SOURCE_BOOKING = 'booking';
    public const SOURCE_COD = 'cod';
    public const SOURCE_WHOLESALE = 'wholesale';

    protected $fillable = [
        'tenant_id',
        'order_no',
        'source_order_no',
        'source',
        'source_platform',
        'source_id',
        'channel_id',
        'customer_id',
        'status',
        'source_status',
        'payment_method',
        'payment_status',
        'fulfillment_method',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_amount',
        'total_amount',
        'paid_amount',
        'refunded_amount',
        'cost_amount',
        'currency',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'billing_address',
        'meta',
        'placed_at',
        'confirmed_at',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'refunded_at',
        'source_synced_at',
        'line_count',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'meta' => 'array',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'cost_amount' => 'decimal:2',
        'line_count' => 'integer',
        'placed_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'source_synced_at' => 'datetime',
    ];

    protected $attributes = [
        'source' => self::SOURCE_MANUAL,
        'status' => self::STATUS_PENDING,
        'payment_status' => self::PAYMENT_UNPAID,
        'currency' => 'MYR',
        'subtotal' => 0,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 0,
        'paid_amount' => 0,
        'refunded_amount' => 0,
        'cost_amount' => 0,
        'line_count' => 0,
    ];

    public function items(): HasMany
    {
        return $this->hasMany(UnifiedOrderItem::class, 'unified_order_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(OrderStockReservation::class, 'unified_order_id');
    }

    public function timeline(): HasMany
    {
        return $this->hasMany(OrderTimelineEvent::class, 'unified_order_id');
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(OrderSyncLog::class, 'unified_order_id');
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, self::STATUS_TERMINAL, true);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    public function bakiBayaran(): float
    {
        return round((float) $this->total_amount - (float) $this->paid_amount, 2);
    }

    public function margin(): float
    {
        return round((float) $this->total_amount - (float) $this->cost_amount, 2);
    }

    public function marginPercent(): float
    {
        $total = (float) $this->total_amount;

        return $total > 0 ? round($this->margin() / $total * 100, 2) : 0.0;
    }

    /**
     * Adakah pesanan ini perlu tindakan AI (contoh: tak ada respons > 24 jam).
     */
    public function perluTindakan(): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        return $this->placed_at !== null && $this->placed_at->lt(now()->subDay());
    }
}
