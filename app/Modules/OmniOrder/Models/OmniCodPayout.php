<?php

namespace App\Modules\OmniOrder\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Jejak pembayaran COD — memantau payout kurier untuk pesanan COD.
 *
 * Berasal daripada Luxana "COD Tracker".
 */
class OmniCodPayout extends Model
{
    use BelongsToTenant;

    protected $table = 'omni_cod_payouts';

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_TRANSIT = 'in_transit';

    public const STATUS_RECEIVED = 'received';

    public const STATUS_DISPUTED = 'disputed';

    public const STATUS_LOST = 'lost';

    protected $fillable = [
        'tenant_id',
        'omni_order_id',
        'omni_courier_id',
        'expected_sen',
        'received_sen',
        'status',
        'payout_reference',
        'expected_at',
        'received_at',
        'dispute_reason',
        'resolved_at',
        'notes',
    ];

    protected $casts = [
        'expected_sen' => 'integer',
        'received_sen' => 'integer',
        'expected_at' => 'datetime',
        'received_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'expected_sen' => 0,
        'received_sen' => 0,
    ];

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(OmniOrder::class, 'omni_order_id');
    }

    public function beza(): float
    {
        return ($this->received_sen - $this->expected_sen) / 100;
    }

    public function tertunggak(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_TRANSIT], true)
            && $this->expected_at !== null
            && $this->expected_at->isPast();
    }
}
