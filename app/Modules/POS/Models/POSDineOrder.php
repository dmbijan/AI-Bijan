<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pesanan dine-in (BAB 88 / T-76) — dipindah daripada B-POS `DineOrder`.
 *
 * Aliran: submitted → accepted (jadi jualan) | rejected
 *
 * ⚠️ PERBEZAAN daripada B-POS: B-POS menyimpan `access_token` uuid KEKAL di
 * baris pesanan (bocor jika DB dibaca). Di sini hanya HASH + masa luput.
 */
class POSDineOrder extends Model
{
    use BelongsToTenant;

    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAY_AT_COUNTER = 'pay_at_counter';
    public const PAY_ONLINE = 'pay_online';

    protected $table = 'pos_dine_orders';

    protected $fillable = [
        'tenant_id',
        'dine_table_id',
        'sale_id',
        'customer_id',
        'cashier_id',
        'order_no',
        'status',
        'payment_option',
        'access_token_hash',
        'access_expires_at',
        'subtotal',
        'item_count',
        'notes',
        'rejection_reason',
        'submitted_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'item_count' => 'integer',
        'submitted_at' => 'datetime',
        'access_expires_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_SUBMITTED,
        'payment_option' => self::PAY_AT_COUNTER,
        'subtotal' => 0,
        'item_count' => 0,
    ];

    protected $hidden = ['access_token_hash'];

    public function table(): BelongsTo
    {
        return $this->belongsTo(POSDineTable::class, 'dine_table_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(POSDineOrderItem::class, 'dine_order_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(POSSale::class, 'sale_id');
    }

    public function menunggu(): bool
    {
        return $this->status === self::STATUS_SUBMITTED;
    }

    /** Scope tenant (corak sama seperti POSRegister/POSSale). */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }
}
