<?php

namespace App\Modules\Invoicing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class SalesOrder extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'sales_orders';

    protected $fillable = [
        'tenant_id',
        'order_number',
        'quotation_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'billing_address',
        'shipping_address',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'subtotal',
        'tax_total',
        'discount_total',
        'shipping_total',
        'grand_total',
        'currency',
        'exchange_rate',
        'notes',
        'terms_conditions',
        'status',
        'fulfilled_at',
        'cancelled_at',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'fulfilled_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'currency' => 'string',
        'exchange_rate' => 'decimal:6',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = self::generateOrderNumber();
            }
            if (empty($order->currency)) {
                $order->currency = config('invoicing.currency.default', 'MYR');
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $year = date('Y');
        $prefix = 'SO';
        
        $lastOrder = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastOrder ? intval(substr($lastOrder->order_number, -5)) + 1 : 1;
        
        return sprintf('%s-%s-%05d', $prefix, $year, $sequence);
    }

    // Relationships
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Models\Customer::class, 'customer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'sales_order_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAwaitingDelivery($query)
    {
        return $query->whereIn('status', ['confirmed', 'processing'])
            ->whereNull('actual_delivery_date');
    }

    // Methods
    public function canBeFulfilled(): bool
    {
        return in_array($this->status, ['confirmed', 'processing', 'shipped']) && !$this->fulfilled_at;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed', 'processing']) && !$this->fulfilled_at;
    }

    public function recalculateTotals(): void
    {
        $subtotal = 0;
        $discountTotal = 0;

        foreach ($this->items as $item) {
            $lineAmount = $item->quantity * $item->unit_price;
            $subtotal += $lineAmount;
            $discountTotal += $lineAmount * ($item->discount_percentage ?? 0) / 100;
        }

        $netAmount = $subtotal - $discountTotal;
        $taxRate = config('invoicing.tax.sst_rate', 0);
        $taxTotal = $netAmount * ($taxRate / 100);

        $this->subtotal = $subtotal;
        $this->discount_total = $discountTotal;
        $this->tax_total = $taxTotal;
        $this->grand_total = $netAmount + $taxTotal + ($this->shipping_total ?? 0);
        
        $this->save();
    }
}
