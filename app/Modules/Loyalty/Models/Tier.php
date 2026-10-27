<?php

namespace App\Modules\Loyalty\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Modules\Loyalty\Models\Member;
use App\Modules\Loyalty\Models\PointsLedger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tier extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * ⚠️ 27 Okt 2026: `$table` WAJIB — nama jadual ialah `loyalty_tiers`,
     * bukan `tiers`. Tanpanya Eloquent meneka `tiers` → 500
     * "no such table: tiers". Trait `BelongsToTenant` juga HILANG sebelum ini,
     * menyebabkan tiada pengasingan tenant pada tahap data.
     */
    protected $table = 'loyalty_tiers';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'min_points',
        'max_points',
        'point_multiplier',
        'benefits',
        'is_default',
        'is_active',
        'demotion_grace_period',
    ];

    protected $casts = [
        'benefits' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'min_points' => 'integer',
        'max_points' => 'integer',
        'point_multiplier' => 'decimal:2',
        'demotion_grace_period' => 'integer',
    ];

    /**
     * Get the tenant that owns the tier.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get all members in this tier.
     */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    /**
     * Scope by active tiers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by default tier.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Check if a point value qualifies for this tier.
     */
    public function qualifiesFor(int $points): bool
    {
        return $points >= $this->min_points && ($this->max_points === null || $points < $this->max_points);
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    }

    /**
     * Get benefit labels as array.
     */
    public function getBenefitLabelsAttribute(): array
    {
        $labels = [
            'early_access' => 'Early Access to Sales',
            'birthday_bonus' => 'Birthday Bonus Points',
            'free_shipping' => 'Free Shipping',
            'priority_support' => 'Priority Support',
            'exclusive_events' => 'Exclusive Events',
            'personal_shopper' => 'Personal Shopper',
            'vip_gift' => 'VIP Gift Box',
        ];

        return array_map(fn($key) => $labels[$key] ?? $key, $this->benefits);
    }
}
