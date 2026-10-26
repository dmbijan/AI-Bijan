<?php

namespace App\Modules\Loyalty\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Modules\Loyalty\Models\Member;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    use BelongsToTenant;
    use HasFactory;

    /**
     * ⚠️ Jadual sebenar ialah `loyalty_rewards` (lihat migration
     * `2026_10_20_001000_create_loyalty_tables.php`). Tanpa `$table`,
     * Eloquent meneka `rewards` → "no such table".
     */
    protected $table = 'loyalty_rewards';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'type',
        'cost_points',
        'value',
        'currency',
        'code',
        'redemption_limit_per_member',
        'total_redemptions',
        'max_redemptions',
        'start_date',
        'end_date',
        'terms_conditions',
        'image_url',
        'is_active',
        'properties',
    ];

    protected $casts = [
        'cost_points' => 'integer',
        'value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'properties' => 'array',
    ];

    /**
     * Get the tenant that owns the reward.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get all redemptions for this reward.
     */
    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }

    /**
     * Scope by active rewards.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', now());
            })
            ->where(function($q) {
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
     * Check if reward can be redeemed.
     */
    public function canRedeem(int $memberBalance): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->cost_points > $memberBalance) {
            return false;
        }

        if ($this->max_redemptions && $this->total_redemptions >= $this->max_redemptions) {
            return false;
        }

        return true;
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'discount' => 'Discount Voucher',
            'free_shipping' => 'Free Shipping',
            'product' => 'Free Product',
            'cashback' => 'Cashback',
            'experience' => 'Exclusive Experience',
        ];

        return $labels[$this->type] ?? ucfirst($this->type);
    }
}
