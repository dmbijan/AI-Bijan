<?php

namespace App\Modules\Affiliate\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AffiliatePayout extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'affiliate_payouts';

    protected $fillable = [
        'tenant_id',
        'affiliate_id',
        'payout_code',
        'amount',
        'currency',
        'payment_method',
        'payment_details',
        'status',
        'transaction_reference',
        'notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the payout.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the affiliate who received the payout.
     */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * Get all commissions included in this payout.
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'payout_id', 'id');
    }

    /**
     * Scope for pending payouts.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processed payouts.
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }

    /**
     * Generate unique payout code.
     */
    public static function generateCode(): string
    {
        do {
            $code = 'POUT-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (self::where('payout_code', $code)->exists());

        return $code;
    }

    /**
     * Process payout.
     */
    public function process(string $transactionRef, int $userId): self
    {
        $this->update([
            'status' => 'processed',
            'transaction_reference' => $transactionRef,
            'processed_by' => $userId,
            'processed_at' => now(),
        ]);

        // Update affiliate total payouts
        $affiliate = $this->affiliate;
        $affiliate->increment('total_payouts', $this->amount);

        return $this;
    }

    /**
     * Cancel payout.
     */
    public function cancel(string $reason = ''): self
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => $reason,
        ]);

        return $this;
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'processed' => 'Processed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }
}
