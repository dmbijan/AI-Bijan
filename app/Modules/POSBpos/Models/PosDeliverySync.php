<?php

namespace App\Modules\POSBpos\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Baris gilir sync penghantaran — rakaman pesanan yang perlu diselaraskan
 * apabila sambungan pulih (offline-first).
 *
 * Berasal daripada B-POS ciri `delivery_sync`.
 */
class PosDeliverySync extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_delivery_syncs';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SYNCED = 'synced';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CONFLICT = 'conflict';

    protected $fillable = [
        'tenant_id',
        'client_uuid',
        'pos_sale_id',
        'payload',
        'status',
        'attempts',
        'last_error',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempts' => 'integer',
        'synced_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'attempts' => 0,
    ];
}
