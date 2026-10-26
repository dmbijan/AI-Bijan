<?php

namespace App\Modules\Invoicing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class RecurringBilling extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'recurring_billings';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'billing_type', // subscription, retainer, usage-based, milestone
        'frequency', // daily, weekly, monthly, quarterly, annually
        'interval', // every N periods
        'start_date',
        'end_date',
        'next_billing_date',
        'amount',
        'tax_percentage',
        'grand_total',
        'currency',
        'auto_send_invoice', // Auto-generate invoice on billing date
        'auto_send_reminder', // Auto-send reminder before due date
        'reminder_days_before',
        'status', // active, paused, completed, cancelled
        'total_invoices_generated',
        'last_invoice_date',
        'last_billing_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_billing_date' => 'date',
        'amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'currency' => 'string',
        'auto_send_invoice' => 'boolean',
        'auto_send_reminder' => 'boolean',
        'reminder_days_before' => 'integer',
        'total_invoices_generated' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($billing) {
            if (empty($billing->currency)) {
                $billing->currency = config('invoicing.currency.default', 'MYR');
            }
            if (empty($billing->tax_percentage)) {
                $billing->tax_percentage = config('invoicing.tax.sst_rate', 0);
            }
            if (empty($billing->grand_total)) {
                $billing->grand_total = $billing->amount * (1 + $billing->tax_percentage / 100);
            }
            if (empty($billing->frequency)) {
                $billing->frequency = 'monthly';
            }
            if (empty($billing->interval)) {
                $billing->interval = 1;
            }
            if (empty($billing->status)) {
                $billing->status = 'active';
            }
            if (empty($billing->auto_send_invoice)) {
                $billing->auto_send_invoice = false;
            }
            if (empty($billing->reminder_days_before)) {
                $billing->reminder_days_before = 7;
            }
        });
    }

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Models\Customer::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDueForBilling($query)
    {
        return $query->where('status', 'active')
            ->where('next_billing_date', '<=', now()->toDateString());
    }

    // Methods
    public function calculateNextBillingDate(): void
    {
        $date = $this->last_billing_date ? 
            \Carbon\Carbon::parse($this->last_billing_date)->addPeriods($this->interval) :
            \Carbon\Carbon::parse($this->start_date);

        $this->next_billing_date = $date->toDateString();
        $this->save();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['active', 'paused']);
    }

    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->save();
    }

    public function pause(): void
    {
        $this->status = 'paused';
        $this->save();
    }

    public function resume(): void
    {
        $this->status = 'active';
        $this->calculateNextBillingDate();
        $this->save();
    }

    public function isCompleted(): bool
    {
        return $this->end_date && now()->greaterThan(\Carbon\Carbon::parse($this->end_date));
    }
}
