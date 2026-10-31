<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tender pembayaran (BAB 88 / T-79) — dipindah daripada B-POS `TransactionTender`.
 *
 * B-POS membuat tender sebagai ENTITI BERASINGAN (bukan kolum) supaya:
 *   • tunai + kad dalam satu jualan direkod sebagai dua baris,
 *   • `cash_received` (wang diterima) berbeza daripada `amount` (bahagian dibayar),
 *   • baki tunai boleh dikira semula untuk penutupan syif.
 *
 * Corak ini jauh lebih bersih daripada kolum `payment_method` berasingan.
 */
class POSSaleTender extends Model
{
    use BelongsToTenant;

    public const METHOD_CASH = 'cash';
    public const METHOD_CARD = 'card';
    public const METHOD_EWALLET = 'ewallet';
    public const METHOD_TNG = 'tng';
    public const METHOD_CREDIT = 'credit';
    public const METHOD_VOUCHER = 'voucher';

    public const METHOD_DUITNOW = 'duitnow';

    public const STATUS_PAID = 'paid';
    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED = 'failed';

    protected $table = 'pos_sale_tenders';

    protected $fillable = [
        'tenant_id',
        'sale_id',
        'method',
        'amount',
        'cash_received',
        'change_amount',
        'payment_status',
        'reference',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected $attributes = [
        'payment_status' => self::STATUS_PAID,
        'change_amount' => 0,
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(POSSale::class, 'sale_id');
    }

    public function tunai(): bool
    {
        return $this->method === self::METHOD_CASH;
    }
}
