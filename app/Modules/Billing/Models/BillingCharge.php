<?php

namespace App\Modules\Billing\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Satu rekod per cubaan bayaran (invois CHIP).
 *
 * `period_key` (YYYY-MM) + UNIQUE(tenant_id, kind, period_key) memastikan
 * satu tenant tidak boleh dicaj dua kali dalam bulan yang sama untuk langganan
 * biasa. Top-up AI kredit menggunakan `kind = topup` + masa dalam period_key.
 */
class BillingCharge extends Model
{
    use BelongsToTenant;

    public const STATUS_CREATED = 'created';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_EXPIRED = 'expired';

    public const KIND_SUBSCRIPTION = 'subscription';
    public const KIND_TOPUP = 'topup';

    protected $table = 'billing_charges';

    protected $fillable = [
        'tenant_id',
        'billing_subscription_id',
        'chip_purchase_id',
        'chip_checkout_url',
        'status',
        'kind',
        'period_key',
        'amount',
        'currency',
        'paid_at',
        'failed_at',
        'failure_reason',
        'chip_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'chip_payload' => 'array',
    ];

    public function sudahDibayar(): bool
    {
        return $this->status === self::STATUS_PAID;
    }
}
