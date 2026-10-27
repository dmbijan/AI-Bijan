<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Notifikasi bayaran TnG (BAB 88 / T-82) — dipindah daripada B-POS
 * `TngSoundboxService`.
 *
 * Aliran: notifikasi masuk → padan dengan jualan TnG belum bayar yang
 * jumlahnya SAMA (first-come-first-served) → jika tiada, simpan sebagai
 * `unclaimed` untuk padanan manual.
 *
 * ⚠️ `unique(tenant_id, reference)` = idempotency webhook (isu S-5 B-POS:
 * notifikasi berulang boleh melunaskan dua jualan).
 */
class POSTngNotification extends Model
{
    use BelongsToTenant;

    public const STATUS_MATCHED = 'matched';
    public const STATUS_UNCLAIMED = 'unclaimed';
    public const STATUS_IGNORED = 'ignored';

    protected $table = 'pos_tng_notifications';

    protected $fillable = [
        'tenant_id',
        'amount',
        'reference',
        'raw_message',
        'status',
        'sale_id',
        'matched_by',
        'received_at',
        'matched_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'received_at' => 'datetime',
        'matched_at' => 'datetime',
    ];

    protected $attributes = ['status' => self::STATUS_UNCLAIMED];

    public function sudahPadan(): bool
    {
        return $this->status === self::STATUS_MATCHED;
    }
}
