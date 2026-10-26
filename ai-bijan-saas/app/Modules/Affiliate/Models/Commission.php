<?php

namespace App\Modules\Affiliate\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'affiliate_commissions';

    protected $fillable = [
        'tenant_id',
        'affiliate_id',
        'referral_id',
        'order_id',
        'invoice_id',
        'commission_code',
        'type',
        'status',
        'base_amount',
        'commission_rate',
        'commission_amount',
        'tax_amount',
        'net_amount',
        'currency',
        'description',
        'notes',
        'approved_by',
        'approved_at',
        'paid_at',
        'payout_id',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the commission.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the affiliate who earned the commission.
     */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * Get the referral that generated this commission.
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    /**
     * Get the order that generated this commission.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Marketplace\Models\Order::class, 'order_id');
    }

    /**
     * Get the invoice that generated this commission.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Invoicing\Models\Invoice::class, 'invoice_id');
    }

    /**
     * Get the payout for this commission.
     */
    public function payout(): BelongsTo
    {
        return $this->belongsTo(AffiliatePayout::class, 'payout_id');
    }

    /**
     * Scope for pending commissions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved commissions.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for paid commissions.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Generate unique commission code.
     */
    public static function generateCode(): string
    {
        do {
            $code = 'COM-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (self::where('commission_code', $code)->exists());

        return $code;
    }

    /**
     * Approve commission.
     */
    public function approve(int $userId): self
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        // Update affiliate totals
        $affiliate = $this->affiliate;
        $affiliate->increment('total_commissions', $this->commission_amount);
        $affiliate->increment('pending_balance', $this->net_amount);
        $affiliate->increment('approved_commissions');

        return $this;
    }

    /**
     * Reject commission.
     */
    public function reject(string $reason = ''): self
    {
        $this->update([
            'status' => 'rejected',
            'notes' => $reason,
        ]);

        // Update affiliate rejected count
        $affiliate = $this->affiliate;
        $affiliate->increment('rejected_commissions');

        return $this;
    }

    /**
     * Mark as paid.
     */
    public function markAsPaid(?string $payoutId = null): self
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payout_id' => $payoutId,
        ]);

        // Deduct from affiliate pending balance
        $affiliate = $this->affiliate;
        $affiliate->decrement('pending_balance', $this->net_amount);
        $affiliate->increment('total_payouts', $this->net_amount);

        return $this;
    }

    /**
     * Calculate tax amount.
     */
    public function calculateTax(float $taxRate = null): float
    {
        $taxRate = $taxRate ?? config('affiliate.commission.tax_rate', 0);
        $this->tax_amount = round($this->commission_amount * ($taxRate / 100), 2);
        $this->net_amount = $this->commission_amount - $this->tax_amount;
        
        return $this->net_amount;
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending Approval',
            'approved' => 'Approved',
            'paid' => 'Paid',
            'rejected' => 'Rejected',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'sale' => 'Sale Commission',
            'subscription' => 'Subscription Commission',
            'upgrade' => 'Upgrade Commission',
            'renewal' => 'Renewal Commission',
            'first_purchase' => 'First Purchase Bonus',
            default => ucfirst($this->type),
        };
    }
}
