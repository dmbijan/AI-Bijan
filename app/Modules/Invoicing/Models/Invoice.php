<?php

namespace App\Modules\Invoicing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class Invoice extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'invoices';

    protected $fillable = [
        'tenant_id',
        'invoice_number',
        'sales_order_id',
        'quotation_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_tax_id', // Tax ID / SST number
        'billing_address',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_total',
        'discount_total',
        'shipping_total',
        'grand_total',
        'amount_paid',
        'amount_outstanding',
        'currency',
        'exchange_rate',
        'notes',
        'terms_conditions',
        'status',
        'paid_at',
        'overdue_notified_at',
        // e-Invois fields
        'einvois_submission_status', // pending, submitted, accepted, rejected, cancelled
        'einvois_reference_no', // UUID from LHDN
        'einvois_submission_date',
        'einvois_rejection_reason',
        'einvois_cancelled_at',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'overdue_notified_at' => 'datetime',
        'einvois_submission_date' => 'datetime',
        'einvois_cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_outstanding' => 'decimal:2',
        'currency' => 'string',
        'exchange_rate' => 'decimal:6',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
            if (empty($invoice->currency)) {
                $invoice->currency = config('invoicing.currency.default', 'MYR');
            }
            if (empty($invoice->exchange_rate)) {
                $invoice->exchange_rate = 1.0;
            }
            if (empty($invoice->status)) {
                $invoice->status = 'draft';
            }
            if (empty($invoice->einvois_submission_status)) {
                $invoice->einvois_submission_status = 'pending';
            }
        });

        static::saving(function ($invoice) {
            // Auto-calculate amount outstanding
            $invoice->amount_outstanding = $invoice->grand_total - $invoice->amount_paid;
        });
    }

    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $prefix = 'INV';
        
        $lastInvoice = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastInvoice ? intval(substr($lastInvoice->invoice_number, -5)) + 1 : 1;
        
        return sprintf('%s-%s-%05d', $prefix, $year, $sequence);
    }

    // Relationships
    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id');
    }

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
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(\App\Modules\Finance\Models\Payment::class, 'invoice_id');
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

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->toDateString())
            ->where('amount_outstanding', '>', 0)
            ->whereIn('status', ['issued', 'partial']);
    }

    public function scopeDueToday($query)
    {
        return $query->where('due_date', now()->toDateString())
            ->where('amount_outstanding', '>', 0);
    }

    public function scopeEInvoisAccepted($query)
    {
        return $query->where('einvois_submission_status', 'accepted');
    }

    public function scopeEInvoisRejected($query)
    {
        return $query->where('einvois_submission_status', 'rejected');
    }

    // Methods
    public function isPaid(): bool
    {
        return $this->amount_outstanding <= 0;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && now()->greaterThan($this->due_date) && !$this->isPaid();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'issued']) && $this->amount_outstanding > 0;
    }

    public function canSendEInvois(): bool
    {
        return config('invoicing.einvois.enabled', false) 
            && $this->status === 'issued'
            && $this->einvois_submission_status === 'pending';
    }

    public function recordPayment(float $amount): void
    {
        $this->amount_paid += $amount;
        $this->amount_outstanding = $this->grand_total - $this->amount_paid;
        
        if ($this->isPaid()) {
            $this->status = 'paid';
            $this->paid_at = now();
        } else {
            $this->status = 'partial';
        }
        
        $this->save();
    }

    public function recalculateTotals(): void
    {
        $subtotal = 0;
        $discountTotal = 0;
        $taxTotal = 0;

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
