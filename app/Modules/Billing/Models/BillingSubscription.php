<?php

namespace App\Modules\Billing\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Langganan tenant. Satu tenant boleh ada banyak rekod sejarah, tetapi hanya
 * SATU yang `status = active` pada satu masa.
 */
class BillingSubscription extends Model
{
    use BelongsToTenant;

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_SUSPENDED = 'suspended';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    /** Perpindahan status sah SAHAJA — tolak yang lain sebelum simpan. */
    public const ALIRAN = [
        self::STATUS_PENDING => [self::STATUS_ACTIVE, self::STATUS_EXPIRED, self::STATUS_CANCELLED],
        self::STATUS_ACTIVE => [self::STATUS_PAST_DUE, self::STATUS_CANCELLED, self::STATUS_SUSPENDED],
        self::STATUS_PAST_DUE => [self::STATUS_ACTIVE, self::STATUS_SUSPENDED, self::STATUS_CANCELLED],
        self::STATUS_SUSPENDED => [self::STATUS_ACTIVE, self::STATUS_CANCELLED],
        self::STATUS_CANCELLED => [],
        self::STATUS_EXPIRED => [],
    ];

    protected $table = 'billing_subscriptions';

    protected $fillable = [
        'tenant_id',
        'plan_code',
        'chip_subscription_id',
        'status',
        'amount',
        'currency',
        'billing_cycle',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'cancel_at',
        'cancelled_at',
        'reseller_id',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'cancel_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function bolehPindah(string $ke): bool
    {
        return in_array($ke, self::ALIRAN[$this->status] ?? [], true);
    }

    public function aktif(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function charges(): HasMany
    {
        return $this->hasMany(BillingCharge::class, 'billing_subscription_id');
    }
}
