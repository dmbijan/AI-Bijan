<?php

namespace App\Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bayaran kepada reseller.
 *
 * `bank_snapshot` menyimpan maklumat bank PADA MASA BAYAR — rekod ini tidak
 * boleh berubah walaupun reseller menukar akaun kemudian (jejak audit).
 */
class ResellerPayout extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PAID = 'paid';
    public const STATUS_FAILED = 'failed';

    protected $table = 'reseller_payouts';

    protected $fillable = [
        'reseller_id',
        'payout_reference',
        'amount',
        'currency',
        'status',
        'bank_snapshot',
        'commission_count',
        'period_from',
        'period_to',
        'paid_at',
        'proof_path',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bank_snapshot' => 'array',
        'commission_count' => 'integer',
        'paid_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($p) {
            if (empty($p->payout_reference)) {
                $p->payout_reference = sprintf(
                    'RPO-%s-%04d',
                    now()->format('Ym'),
                    (static::whereYear('created_at', now()->year)->count() + 1)
                );
            }
        });
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class, 'reseller_id');
    }
}
