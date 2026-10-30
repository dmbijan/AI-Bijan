<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use App\Modules\CRM\Models\Customer;
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
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'sale_date' => 'date',
        'completed_at' => 'datetime',
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
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the cashier who made this sale.
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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

        return 'SALE-'.$year.'-'.str_pad($number, 6, '0', STR_PAD_LEFT);
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
