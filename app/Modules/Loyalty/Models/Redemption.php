<?php

namespace App\Modules\Loyalty\Models;

use App\Modules\Loyalty\Models\Member;
use App\Modules\Loyalty\Models\Reward;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Redemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'member_id',
        'reward_id',
        'points_spent',
        'status',
        'redemption_code',
        'fulfilled_at',
        'notes',
        'properties',
    ];

    protected $casts = [
        'points_spent' => 'integer',
        'fulfilled_at' => 'datetime',
        'properties' => 'array',
    ];

    /**
     * Get the tenant that owns the redemption.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the member who redeemed.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Get the reward that was redeemed.
     */
    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by pending redemptions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope by recent redemptions.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Mark as fulfilled.
     */
    public function markAsFulfilled(): void
    {
        $this->update([
            'status' => 'fulfilled',
            'fulfilled_at' => now(),
        ]);

        // Increment total redemptions on reward
        $this->reward->increment('total_redemptions');
    }

    /**
     * Mark as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        $labels = [
            'pending' => 'Pending',
            'fulfilled' => 'Fulfilled',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
        ];

        return $labels[$this->status] ?? ucfirst($this->status);
    }
}
