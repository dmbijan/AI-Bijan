<?php

namespace App\Modules\Affiliate\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Affiliate extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'affiliates';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'crm_contact_id',
        'affiliate_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company_name',
        'website',
        'social_media',
        'bio',
        'profile_image',
        'status',
        'tier',
        'commission_rate',
        'total_earnings',
        'total_commissions',
        'total_payouts',
        'pending_balance',
        'approved_commissions',
        'rejected_commissions',
        'total_clicks',
        'total_conversions',
        'conversion_rate',
        'average_order_value',
        'top_referring_domain',
        'payment_method',
        'payment_details',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'social_media' => 'array',
        'payment_details' => 'array',
        'total_earnings' => 'decimal:2',
        'total_commissions' => 'decimal:2',
        'total_payouts' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the affiliate.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the user associated with the affiliate.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the CRM contact associated with the affiliate.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Models\Contact::class, 'crm_contact_id');
    }

    /**
     * Get all referrals for this affiliate.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(ReferralCode::class, 'affiliate_id', 'id');
    }

    /**
     * Get all commissions earned by this affiliate.
     */
    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'affiliate_id', 'id');
    }

    /**
     * Get approved commissions only.
     */
    public function approvedCommissions(): HasMany
    {
        return $this->commissions()->where('status', 'approved');
    }

    /**
     * Get pending commissions only.
     */
    public function pendingCommissions(): HasMany
    {
        return $this->commissions()->where('status', 'pending');
    }

    /**
     * Get payout history.
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(AffiliatePayout::class, 'affiliate_id', 'id');
    }

    /**
     * Scope for active affiliates.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for pending affiliates.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for rejected affiliates.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Generate unique affiliate code.
     */
    public static function generateCode(): string
    {
        do {
            $code = 'AFF-' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('affiliate_code', $code)->exists());

        return $code;
    }

    /**
     * Calculate conversion rate.
     */
    public function calculateConversionRate(): float
    {
        if ($this->total_clicks === 0) {
            return 0.0;
        }

        return round(($this->total_conversions / $this->total_clicks) * 100, 2);
    }

    /**
     * Calculate average order value.
     */
    public function calculateAverageOrderValue(): float
    {
        if ($this->total_conversions === 0) {
            return 0.0;
        }

        return round($this->total_commissions / $this->total_conversions, 2);
    }

    /**
     * Check if affiliate has reached payout threshold.
     */
    public function canWithdraw(): bool
    {
        return $this->pending_balance >= config('affiliate.payout.min_withdrawal', 50.00);
    }

    /**
     * Get tier name.
     */
    public function getTierNameAttribute(): string
    {
        $tiers = config('affiliate.tiers', []);
        
        return $tiers[$this->tier]['name'] ?? ucfirst($this->tier);
    }

    /**
     * Get effective commission rate based on tier.
     */
    public function getEffectiveRate(): float
    {
        $baseRate = $this->commission_rate;
        $tierMultiplier = $this->getTierMultiplier();
        
        return $baseRate * $tierMultiplier;
    }

    /**
     * Get tier multiplier.
     */
    public function getTierMultiplier(): float
    {
        $tiers = config('affiliate.tiers', []);
        
        return $tiers[$this->tier]['commission_multiplier'] ?? 1.0;
    }

    /**
     * Evaluate tier upgrade eligibility.
     */
    public function evaluateTier(): ?string
    {
        $totalSales = $this->total_commissions;
        $currentTier = $this->tier;
        $newTier = null;

        foreach (config('affiliate.tiers', []) as $tierName => $tierConfig) {
            if ($tierConfig['min_sales'] <= $totalSales && $tierName !== $currentTier) {
                $newTier = $tierName;
            }
        }

        return $newTier;
    }

    /**
     * Promote affiliate to new tier.
     */
    public function promoteToTier(string $newTier): void
    {
        $this->update(['tier' => $newTier]);
    }

    /**
     * Demote affiliate to lower tier.
     */
    public function demoteToTier(string $newTier): void
    {
        $this->update(['tier' => $newTier]);
    }

    /**
     * Activate affiliate account.
     */
    public function activate(): void
    {
        $this->update([
            'status' => 'active',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Deactivate affiliate account.
     */
    public function deactivate(): void
    {
        $this->update(['status' => 'inactive']);
    }

    /**
     * Reject affiliate application.
     */
    public function reject(string $reason = ''): void
    {
        $this->update([
            'status' => 'rejected',
            'notes' => $reason,
        ]);
    }
}
