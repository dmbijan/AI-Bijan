<?php

namespace App\Modules\Orders\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStockReservation extends Model
{
    use BelongsToTenant;

    protected $table = 'order_stock_reservations';

    public const STATUS_RESERVED = 'reserved';
    public const STATUS_RELEASED = 'released';
    public const STATUS_COMMITTED = 'committed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'unified_order_id',
        'product_id',
        'sku',
        'quantity',
        'reserved_quantity',
        'status',
        'expires_at',
        'committed_at',
        'released_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'quantity' => 'decimal:2',
        'reserved_quantity' => 'decimal:2',
        'expires_at' => 'datetime',
        'committed_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_RESERVED,
        'quantity' => 0,
        'reserved_quantity' => 0,
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(UnifiedOrder::class, 'unified_order_id');
    }

    public function aktif(): bool
    {
        return $this->status === self::STATUS_RESERVED;
    }

    public function sudahLuput(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
