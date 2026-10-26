<?php

namespace App\Modules\Affiliate\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'affiliate_referrals';

    protected $fillable = [
        'tenant_id',
        'affiliate_id',
        'referral_code_id',
        'referred_user_id',
        'referred_contact_id',
        'referral_code',
        'status',
        'converted_at',
        'conversion_type',
        'conversion_value',
        'commission_earned',
        'notes',
    ];

    protected $casts = [
        'conversion_value' => 'decimal:2',
        'commission_earned' => 'decimal:2',
        'converted_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the referral.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the affiliate who referred.
     */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * Get the referral code used.
     */
    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class, 'referral_code_id');
    }

    /**
     * Get the referred user.
     */
    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'referred_user_id');
    }

    /**
     * Get the referred CRM contact.
     */
    public function referredContact(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Models\Contact::class, 'referred_contact_id');
    }

    /**
     * Scope for converted referrals.
     */
    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    /**
     * Scope for pending referrals.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Convert referral to commission.
     */
    public function convert(string $conversionType, float $conversionValue): self
    {
        $this->update([
            'status' => 'converted',
            'converted_at' => now(),
            'conversion_type' => $conversionType,
            'conversion_value' => $conversionValue,
        ]);

        // Create commission record
        app(CommissionService::class)->createCommission(
            $this->affiliate_id,
            $this->tenant_id,
            $this->referral_code,
            $conversionType,
            $conversionValue,
            $this->id
        );

        return $this;
    }
}
