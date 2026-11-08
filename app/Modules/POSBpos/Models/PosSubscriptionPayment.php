<?php

namespace App\Modules\POSBpos\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pembayaran langganan — rekod bayaran bil cawangan melalui CHIP.
 *
 * Berasal daripada B-POS ciri `subscription_payments`.
 */
class PosSubscriptionPayment extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_subscription_payments';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'tenant_id',
        'client_uuid',
        'pos_branch_invoice_id',
        'amount_sen',
        'currency',
        'status',
        'gateway',
        'gateway_reference',
        'paid_at',
        'refunded_at',
        'payload',
    ];

    protected $casts = [
        'amount_sen' => 'integer',
        'paid_at' => 'datetime',
        'refunded_at' => 'datetime',
        'payload' => 'array',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'currency' => 'MYR',
        'gateway' => 'chip',
    ];

    public function invois(): BelongsTo
    {
        return $this->belongsTo(PosBranchInvoice::class, 'pos_branch_invoice_id');
    }

    public function berjaya(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }
}
