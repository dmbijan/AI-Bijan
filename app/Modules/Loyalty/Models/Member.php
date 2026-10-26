<?php

namespace App\Modules\Loyalty\Models;

use App\Models\Concerns\BelongsToTenant;

use App\Modules\Loyalty\Models\Tier;
use App\Modules\Loyalty\Models\PointsLedger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use BelongsToTenant;

    protected $table = 'loyalty_members';
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'crm_contact_id',
        'member_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'avatar_url',
        'tier_id',
        'total_points_earned',
        'total_points_redeemed',
        'total_points_expired',
        'current_balance',
        'lifetime_value',
        'purchase_count',
        'last_purchase_date',
        'last_activity_date',
        'status',
        'notes',
        'properties',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'last_purchase_date' => 'date',
        'last_activity_date' => 'datetime',
        'total_points_earned' => 'integer',
        'total_points_redeemed' => 'integer',
        'total_points_expired' => 'integer',
        'current_balance' => 'integer',
        'lifetime_value' => 'decimal:2',
        'purchase_count' => 'integer',
        'properties' => 'array',
    ];

    /**
     * Get the tenant that owns the member.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the CRM contact linked to this member.
     */
    public function crmContact(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Models\Contact::class, 'crm_contact_id');
    }

    /**
     * Get the tier of the member.
     */
    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class);
    }

    /**
     * Get all points ledger entries for this member.
     */
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(PointsLedger::class);
    }

    /**
     * Scope by active members.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope by tier.
     */
    public function scopeByTier($query, int $tierId)
    {
        return $query->where('tier_id', $tierId);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by high value members (top X%).
     */
    public function scopeHighValue($query, float $percentile = 0.9)
    {
        $threshold = self::where('tenant_id', $this->tenant_id)
            ->orderByDesc('lifetime_value')
            ->value('lifetime_value');

        return $query->where('lifetime_value', '>=', $threshold);
    }

    /**
     * Check if member is eligible for tier upgrade.
     */
    public function canUpgradeTo(Tier $tier): bool
    {
        return $this->current_balance >= $tier->min_points && !$tier->is_default;
    }

    /**
     * Get tier name accessor.
     */
    public function getTierNameAttribute(): ?string
    {
        return $this->tier ? $this->tier->name : null;
    }

    /**
     * Get tier color accessor.
     */
    public function getTierColorAttribute(): ?string
    {
        return $this->tier ? $this->tier->color : null;
    }

    /**
     * Get point multiplier from tier.
     */
    public function getPointMultiplierAttribute(): float
    {
        return $this->tier ? ($this->tier->point_multiplier ?? 1.0) : 1.0;
    }

    /**
     * Generate unique member code.
     */
    public static function generateCode(): string
    {
        do {
            $code = 'LOY-MEM-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        } while (self::where('member_code', $code)->exists());

        return $code;
    }

    /**
     * Update member activity.
     */
    public function updateActivity(?float $purchaseAmount = null): void
    {
        $this->last_activity_date = now();
        
        if ($purchaseAmount !== null) {
            $this->purchase_count = ($this->purchase_count ?? 0) + 1;
            $this->lifetime_value = ($this->lifetime_value ?? 0) + $purchaseAmount;
            $this->last_purchase_date = now();
        }

        $this->save();
    }
}
