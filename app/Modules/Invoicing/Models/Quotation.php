<?php

namespace App\Modules\Invoicing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Modules\Invoicing\Models\QuotationLine;

class Quotation extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'quotations';

    protected $fillable = [
        'tenant_id',
        'quotation_number',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'issue_date',
        'expiry_date',
        'validity_days',
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
        'accepted_at',
        'rejected_at',
        'converted_to_order_id',
        'converted_at',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'converted_at' => 'datetime',
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

        static::creating(function ($quotation) {
            if (empty($quotation->quotation_number)) {
                $quotation->quotation_number = self::generateQuotationNumber();
            }
            if (empty($quotation->currency)) {
                $quotation->currency = config('invoicing.currency.default', 'MYR');
            }
            if (empty($quotation->exchange_rate)) {
                $quotation->exchange_rate = 1.0;
            }

            // ⚠️ 27 Okt 2026: jadual `quotations` mempunyai DUA set nama untuk
            // konsep sama (warisan migration lama). `quotation_date` &
            // `valid_until` ialah NOT NULL tanpa default — mesti diisi, atau
            // setiap cipta sebut harga gagal dengan "NOT NULL constraint failed".
            if (empty($quotation->quotation_date)) {
                $quotation->quotation_date = $quotation->issue_date ?: now()->toDateString();
            }
            if (empty($quotation->issue_date)) {
                $quotation->issue_date = $quotation->quotation_date;
            }

            if (empty($quotation->valid_until)) {
                $hari = (int) ($quotation->validity_days ?: 30);
                $asas = $quotation->quotation_date ?: now();
                $quotation->valid_until = \Carbon\Carbon::parse($asas)->addDays($hari)->toDateString();
            }
            if (empty($quotation->expiry_date)) {
                $quotation->expiry_date = $quotation->valid_until;
            }
            if (empty($quotation->validity_days)) {
                $quotation->validity_days = 30;
            }
        });
    }

    public static function generateQuotationNumber(): string
    {
        $year = date('Y');
        $prefix = 'QT';
        
        $lastQuotation = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastQuotation ? intval(substr($lastQuotation->quotation_number, -5)) + 1 : 1;
        
        return sprintf('%s-%s-%05d', $prefix, $year, $sequence);
    }

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Models\Customer::class, 'customer_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(QuotationLine::class);
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'converted_to_order_id');
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

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now()->toDateString())
            ->whereIn('status', ['pending', 'accepted']);
    }

    // Methods
    public function isExpired(): bool
    {
        return $this->expiry_date && now()->greaterThan($this->expiry_date);
    }

    public function canConvertToOrder(): bool
    {
        return $this->status === 'accepted' && !$this->converted_to_order_id;
    }

    public function recalculateTotals(): void
    {
        $subtotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;

        foreach ($this->lines as $line) {
            $lineAmount = $line->quantity * $line->unit_price;
            $subtotal += $lineAmount;
            $discountTotal += $lineAmount * ($line->discount_percentage ?? 0) / 100;
        }

        $netAmount = $subtotal - $discountTotal;
        
        // Calculate tax based on tenant tax settings
        $taxRate = config('invoicing.tax.sst_rate', 0); // Default 0% (SST 6% or 8%)
        $taxTotal = $netAmount * ($taxRate / 100);

        $this->subtotal = $subtotal;
        $this->discount_total = $discountTotal;
        $this->tax_total = $taxTotal;
        $this->grand_total = $netAmount + $taxTotal + ($this->shipping_total ?? 0);
        
        $this->save();
    }
}
