<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class POSSale extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'pos_sales';

    protected $fillable = [
        'tenant_id',
        'register_id',
        'cash_shift_id',
        'customer_id',
        'user_id',
        'sale_no',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'change_amount',
        'notes',
        'sale_date',
        'completed_at',
        // BAB 88 — ciri B-POS
        'service_charge_amount',
        'service_charge_rate',
        'payment_method',
        'payment_status',
        'client_uuid',
        'sync_fingerprint',
        'discount_approved_by',
        'discount_approved_at',
        'order_type',
        'kitchen_status',
        'kitchen_queued_at',
        'kitchen_started_at',
        'kitchen_ready_at',
        'kitchen_served_at',
        'dine_table_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'service_charge_amount' => 'decimal:2',
        'service_charge_rate' => 'decimal:2',
        'discount_approved_at' => 'datetime',
        'kitchen_queued_at' => 'datetime',
        'kitchen_started_at' => 'datetime',
        'kitchen_ready_at' => 'datetime',
        'kitchen_served_at' => 'datetime',
        'sale_date' => 'date',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'payment_status' => 'unpaid',
        'order_type' => 'takeaway',
        'service_charge_amount' => 0,
    ];

    /**
     * Get the register this sale belongs to.
     */
    public function register(): BelongsTo
    {
        return $this->belongsTo(POSRegister::class, 'register_id');
    }

    /**
     * Get the cash shift for this sale.
     */
    public function cashShift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class, 'cash_shift_id');
    }

    /**
     * Get the customer for this sale.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Models\Client::class, 'customer_id');
    }

    /**
     * Get the cashier who made this sale.
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Get the items in this sale.
     */
    public function items(): HasMany
    {
        return $this->hasMany(POSSaleItem::class, 'sale_id');
    }

    /**
     * Get the payments for this sale.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(POSSalePayment::class, 'sale_id');
    }

    /**
     * Get the discounts applied to this sale.
     */
    public function discounts(): HasMany
    {
        return $this->hasMany(POSSaleDiscount::class, 'sale_id');
    }

    /**
     * Tender pembayaran (BAB 88 / T-79) — satu jualan boleh ada 1–2 tender.
     */
    public function tenders(): HasMany
    {
        return $this->hasMany(POSSaleTender::class, 'sale_id');
    }

    /** Meja dine-in (BAB 88 / T-76). */
    public function dineTable(): BelongsTo
    {
        return $this->belongsTo(POSDineTable::class, 'dine_table_id');
    }

    /** Adakah jualan ini dibayar dengan lebih daripada satu kaedah? */
    public function pembayaranBerpisah(): bool
    {
        return $this->tenders()->count() > 1;
    }

    /** Baki yang belum dibayar (0 bila lunas). */
    public function bakiPerluBayar(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->amount_paid);
    }

    /**
     * Get the returns for this sale.
     */
    public function returns(): HasMany
    {
        return $this->hasMany(POSSaleReturn::class, 'sale_id');
    }

    /**
     * Scope by tenant.
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by date range.
     */
    public function scopeByDateRange($query, ?string $startDate, ?string $endDate)
    {
        if ($startDate) {
            $query->where('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('sale_date', '<=', $endDate);
        }
        return $query;
    }

    /**
     * Generate unique sale number.
     */
    public static function generateSaleNo(): string
    {
        $year = now()->format('Y');
        $lastNo = static::whereYear('created_at', $year)
            ->orderBy('sale_no', 'desc')
            ->value('sale_no');

        $number = $lastNo ? intval(substr($lastNo, -6)) + 1 : 1;

        return 'SALE-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate change amount.
     */
    public function calculateChange(): float
    {
        return max(0, $this->amount_paid - $this->total_amount);
    }

    /**
     * Check if sale is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->amount_paid >= $this->total_amount;
    }

    /**
     * Get total sales amount for a period.
     */
    public static function getTotalSalesForPeriod(int $tenantId, ?string $startDate, ?string $endDate): float
    {
        $query = static::forTenant($tenantId)
            ->where('status', 'completed');

        if ($startDate) {
            $query->where('sale_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('sale_date', '<=', $endDate);
        }

        return (float) $query->sum('total_amount');
    }
}
