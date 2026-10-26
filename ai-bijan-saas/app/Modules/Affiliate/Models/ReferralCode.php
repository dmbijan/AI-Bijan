<?php

namespace App\Modules\Affiliate\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralCode extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'affiliate_referral_codes';

    protected $fillable = [
        'tenant_id',
        'affiliate_id',
        'referral_code',
        'name',
        'description',
        'type',
        'url',
        'status',
        'usage_count',
        'conversion_count',
        'total_clicks',
        'total_conversions',
        'conversion_rate',
        'start_date',
        'end_date',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
        'usage_count' => 'integer',
        'conversion_count' => 'integer',
        'total_clicks' => 'integer',
        'total_conversions' => 'integer',
        'conversion_rate' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the tenant that owns the referral code.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the affiliate that owns the referral code.
     */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * Get all referrals from this code.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'referral_code_id', 'id');
    }

    /**
     * Get all clicks on this referral link.
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(AffiliateClick::class, 'referral_code_id', 'id');
    }

    /**
     * Scope for active codes.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Generate unique referral code.
     */
    public static function generateCode(string $affiliateId): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('referral_code', $code)->exists());

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
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Increment click count.
     */
    public function incrementClicks(): void
    {
        $this->increment('total_clicks');
    }

    /**
     * Increment conversion count.
     */
    public function incrementConversions(): void
    {
        $this->increment('total_conversions');
        $this->calculateConversionRate();
        $this->save();
    }

    /**
     * Check if referral code is valid.
     */
    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->start_date && $this->start_date->isFuture()) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Deactivate expired codes.
     */
    public static function deactivateExpired(): int
    {
        return self::whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->where('status', 'active')
            ->update(['status' => 'expired']);
    }
}
