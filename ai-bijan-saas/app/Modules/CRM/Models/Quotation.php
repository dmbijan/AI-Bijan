<?php

namespace App\Modules\CRM\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use BelongsToTenant;

    protected $table = 'quotations';
    
    protected $fillable = [
        'tenant_id',
        'quotation_number',
        'opportunity_id',
        'customer_id',
        'quotation_date',
        'valid_until',
        'status',
        'subtotal',
        'discount_amount',
        'discount_percentage',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'currency',
        'payment_terms_days',
        'payment_due_date',
        'shipping_address',
        'shipping_method',
        'terms_conditions',
        'notes',
        'approved_by',
        'approved_at',
        'order_id',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'payment_terms_days' => 'integer',
        'payment_due_date' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Get tenant that owns the quotation.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get associated opportunity.
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    /**
     * Get associated customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get approval user.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Get quotation line items.
     */
    public function lines(): HasMany
    {
        return $this->hasMany(QuotationLine::class);
    }

    /**
     * Generate quotation number automatically.
     */
    public static function generateQuotationNumber(): string
    {
        $year = date('Y');
        $lastNumber = static::whereYear('created_at', $year)
            ->max('id');
        $number = ($lastNumber ?? 0) + 1;
        
        return sprintf('QT-%s-%04d', $year, $number);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if quotation is valid.
     */
    public function isValid(): bool
    {
        return $this->status === 'sent' && $this->valid_until >= now();
    }

    /**
     * Check if quotation is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Recalculate totals.
     */
    public function recalculateTotals(): void
    {
        $subtotal = $this->lines->sum(function ($line) {
            return $line->line_total;
        });
        
        $this->subtotal = $subtotal;
        $this->discount_amount = $subtotal * ($this->discount_percentage / 100);
        $this->tax_amount = ($subtotal - $this->discount_amount) * 0.1; // 10% SST default
        
        $this->total_amount = $subtotal - $this->discount_amount + $this->tax_amount + $this->shipping_cost;
        
        $this->save();
    }
}
