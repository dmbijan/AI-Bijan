<?php

namespace App\Modules\Marketplace\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use BelongsToTenant;

    protected $table = 'marketplace_orders';

    protected $fillable = [
        'tenant_id',
        'order_no',
        'vendor_id',
        'customer_id',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'discount_amount',
        'total_amount',
        'currency',
        'payment_method',
        'payment_status',
        'shipping_method',
        'shipping_address',
        'billing_address',
        'notes',
        'internal_notes',
        'tracking_number',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the order.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the vendor for this order.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    /**
     * Get the customer for this order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Models\Customer::class, 'customer_id');
    }

    /**
     * Get all items in this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * Generate order number.
     */
    public static function generateOrderNo(): string
    {
        $year = now()->format('Y');
        $lastNo = static::whereYear('created_at', $year)
            ->orderBy('order_no', 'desc')
            ->value('order_no');
        
        $number = $lastNo ? intval(substr($lastNo, -6)) + 1 : 1;
        return 'MKT-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by payment status.
     */
    public function scopeByPaymentStatus($query, string $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Check if order is paid.
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /**
     * Check if order is shipped.
     */
    public function isShipped(): bool
    {
        return in_array($this->status, ['shipped', 'delivered']);
    }

    /**
     * Calculate commission for vendor.
     */
    public function getCommissionAmount(): float
    {
        $vendor = $this->vendor;
        if (!$vendor) {
            return 0;
        }

        $rate = $vendor->getEffectiveCommissionRate();
        return round($this->subtotal * $rate, 2);
    }
}
