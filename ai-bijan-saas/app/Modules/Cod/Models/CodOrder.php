<?php

namespace App\Modules\Cod\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pesanan COD — pesanan yang bayarnya dicollected semasa penghantaran.
 */
class CodOrder extends Model
{
    use BelongsToTenant;

    protected $table = 'cod_orders';

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_DISPATCHED = 'dispatched';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_COLLECTED = 'collected';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    /** Status di mana wang sudah ditUBSahkan kepada pengendali. */
    public const STATUS_SET = [
        self::STATUS_COLLECTED,
        self::STATUS_REFUNDED,
    ];

    protected $fillable = [
        'tenant_id',
        'marketplace_order_id',
        'cod_no',
        'reference_no',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'postcode',
        'state',
        'city',
        'country',
        'order_amount',
        'shipping_amount',
        'surcharge',
        'total_amount',
        'amount_to_collect',
        'amount_collected',
        'amount_refunded',
        'currency',
        'status',
        'courier',
        'tracking_number',
        'dispatched_at',
        'delivered_at',
        'collected_at',
        'failed_at',
        'failure_reason',
        'address_verified',
        'address_issue',
        'serviceable',
        'created_by',
        'collected_by',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'order_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'surcharge' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_to_collect' => 'decimal:2',
        'amount_collected' => 'decimal:2',
        'amount_refunded' => 'decimal:2',
        'address_verified' => 'boolean',
        'serviceable' => 'boolean',
        'dispatched_at' => 'datetime',
        'delivered_at' => 'datetime',
        'collected_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'country' => 'MY',
        'currency' => 'MYR',
        'shipping_amount' => 0,
        'surcharge' => 0,
        'amount_collected' => 0,
        'amount_refunded' => 0,
        'address_verified' => false,
        'serviceable' => true,
    ];

    public function histories(): HasMany
    {
        return $this->hasMany(CodStatusHistory::class, 'cod_order_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Bolehkah pesanan ini dihantar?
     *
     * Syarat: status confirmed, alamat disahkan, dan kawasan boleh diliputi.
     */
    public function bolehDihantar(): bool
    {
        return in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_PENDING], true)
            && $this->address_verified
            && $this->serviceable;
    }

    /**
     * Adakah COD gagal ( Causes utama kehilangan revenue)?
     */
    public function gagal(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Baki belum dikutip.
     */
    public function baki(): float
    {
        return round((float) $this->amount_to_collect - (float) $this->amount_collected, 2);
    }
}
