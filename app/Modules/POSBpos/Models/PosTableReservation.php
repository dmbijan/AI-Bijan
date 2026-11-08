<?php

namespace App\Modules\POSBpos\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Modules\POS\Models\POSDineTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Tempahan meja — pelanggan tempah meja sebelum datang.
 *
 * Berasal daripada B-POS ciri `table_reservations`.
 */
class PosTableReservation extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_table_reservations';

    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_SEATED = 'seated';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    protected $fillable = [
        'tenant_id',
        'reservation_no',
        'dine_table_id',
        'customer_id',
        'guest_name',
        'guest_phone',
        'guest_email',
        'party_size',
        'reserved_at',
        'duration_minutes',
        'status',
        'note',
        'confirmed_at',
        'seated_at',
        'completed_at',
        'cancelled_at',
        'cancel_reason',
        'created_by',
    ];

    protected $casts = [
        'party_size' => 'integer',
        'duration_minutes' => 'integer',
        'reserved_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'seated_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'party_size' => 2,
        'duration_minutes' => 90,
    ];

    public function meja(): BelongsTo
    {
        return $this->belongsTo(POSDineTable::class, 'dine_table_id');
    }
}
