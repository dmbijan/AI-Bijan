<?php

namespace App\Modules\POSBpos\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Kad hadiah — nilai prabayar yang boleh ditebus semasa checkout POS.
 *
 * Berasal daripada B-POS (dmbijan/bpos) ciri `gift_cards`.
 */
class PosGiftCard extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_gift_cards';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REDEEMED = 'redeemed';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_VOID = 'void';

    protected $fillable = [
        'tenant_id',
        'code',
        'initial_balance_sen',
        'balance_sen',
        'currency',
        'status',
        'issued_to_customer_id',
        'issued_sale_id',
        'note',
        'expires_at',
        'redeemed_at',
        'created_by',
    ];

    protected $casts = [
        'initial_balance_sen' => 'integer',
        'balance_sen' => 'integer',
        'expires_at' => 'datetime',
        'redeemed_at' => 'datetime',
    ];

    protected $attributes = [
        'currency' => 'MYR',
        'status' => self::STATUS_ACTIVE,
        'balance_sen' => 0,
        'initial_balance_sen' => 0,
    ];

    public function baki(): float
    {
        return $this->balance_sen / 100;
    }

    public function bolehTebus(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->balance_sen > 0
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
