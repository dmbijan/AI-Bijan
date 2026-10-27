<?php

namespace App\Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Komisen reseller — satu rekod per bulan per rujukan.
 *
 * KITARAN HAYAT
 * ─────────────
 *   pending   ← dicipta apabila bayaran pelanggan berjaya
 *   approved  ← selepas `mature_at` (earned_at + 30 hari) dan tiada churn
 *   paid      ← dimasukkan ke dalam `reseller_payouts`
 *   reversed  ← churn/refund SEBELUM matang (bukan selepas approved)
 *
 * ⚠️ `approved` TIDAK boleh dibatalkan oleh churn — ia sudah menjadi hak.
 * Sebarang pembalikan selepas `approved` perlu kelulusan manual + audit.
 */
class ResellerCommission extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PAID = 'paid';
    public const STATUS_REVERSED = 'reversed';

    protected $table = 'reseller_commissions';

    protected $fillable = [
        'reseller_id',
        'reseller_referral_id',
        'tenant_id',
        'billing_charge_id',
        'period',
        'base_amount',
        'commission_amount',
        'currency',
        'status',
        'earned_at',
        'mature_at',
        'approved_at',
        'paid_at',
        'reversed_at',
        'reversal_reason',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'earned_at' => 'datetime',
        'mature_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class, 'reseller_id');
    }

    public function matang(): bool
    {
        return $this->status === self::STATUS_PENDING
            && $this->mature_at !== null
            && $this->mature_at->isPast();
    }

    public function bolehDibatalkan(): bool
    {
        // Hanya `pending` boleh dibalikkan oleh churn.
        return $this->status === self::STATUS_PENDING;
    }
}
