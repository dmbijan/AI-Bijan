<?php

namespace App\Modules\Loyalty\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Modules\Loyalty\Models\Member;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsLedger extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * ⚠️ 27 Okt 2026: `$table` WAJIB — jadual sebenar `loyalty_points_ledger`,
     * bukan `points_ledgers`. Tanpanya → 500 "no such table".
     * Trait `BelongsToTenant` juga HILANG sebelum ini.
     */
    protected $table = 'loyalty_points_ledger';

    protected $fillable = [
        'tenant_id',
        'member_id',
        'reference_type',
        'reference_id',
        'points',
        'balance_after',
        'type',
        'description',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'points' => 'integer',
        'balance_after' => 'integer',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the tenant that owns the ledger entry.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the member that owns the ledger entry.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Scope by positive points (earned).
     */
    public function scopeEarned($query)
    {
        return $query->where('points', '>', 0);
    }

    /**
     * Scope by negative points (redeemed/expired).
     */
    public function scopeRedeemed($query)
    {
        return $query->where('points', '<', 0);
    }

    /**
     * Scope by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope by reference type.
     */
    public function scopeByReference($query, string $referenceType)
    {
        return $query->where('reference_type', $referenceType);
    }

    /**
     * Scope by expiring soon.
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '>=', now())
            ->where('expires_at', '<=', now()->addDays($days));
    }

    /**
     * Scope by expired.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }

    /**
     * Get type label.
     */
    public function getTypeLabelAttribute(): string
    {
        $labels = [
            'signup_bonus' => 'Signup Bonus',
            'purchase' => 'Purchase Earned',
            'birthday' => 'Birthday Bonus',
            'referral' => 'Referral Bonus',
            'review' => 'Review Bonus',
            'social_share' => 'Social Share Bonus',
            'promotion' => 'Promotion Bonus',
            'admin_grant' => 'Admin Grant',
            'redemption' => 'Reward Redemption',
            'refund' => 'Refund Deduction',
            'expiration' => 'Points Expired',
            'adjustment' => 'Manual Adjustment',
        ];

        return $labels[$this->type] ?? ucfirst($this->type);
    }

    /**
     * Check if this entry is expiring soon.
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->between(now(), now()->addDays($days));
    }

    /**
     * Check if this entry is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
