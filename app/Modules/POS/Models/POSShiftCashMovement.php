<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pergerakan tunai dalam syif (BAB 88 / T-80).
 *
 * Dua arah sahaja yang mengubah tunai laci:
 *   drop/expense  → wang KELUAR laci (negatif)
 *   pickup        → wang MASUK laci (positif) — cth bayaran balik baki
 */
class POSShiftCashMovement extends Model
{
    use BelongsToTenant;

    public const TYPE_DROP = 'drop';
    public const TYPE_PICKUP = 'pickup';
    public const TYPE_EXPENSE = 'expense';
    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $table = 'pos_shift_cash_movements';

    protected $fillable = [
        'tenant_id',
        'cash_shift_id',
        'type',
        'amount',
        'reason',
        'created_by',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class, 'cash_shift_id');
    }

    /** Kesan pada laci: drop/expense mengurangkan, pickup menambah. */
    public function kesanPadaLaci(): float
    {
        $nilai = (float) $this->amount;

        return in_array($this->type, [self::TYPE_DROP, self::TYPE_EXPENSE], true)
            ? -abs($nilai)
            : abs($nilai);
    }
}
