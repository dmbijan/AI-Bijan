<?php

namespace App\Modules\POSBpos\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Meja dalam food court — boleh dikongsi antara gerai.
 *
 * Berasal daripada B-POS ciri `food_court_tables`.
 */
class PosFoodCourtTable extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_food_court_tables';

    public const STATUS_FREE = 'free';

    public const STATUS_OCCUPIED = 'occupied';

    public const STATUS_RESERVED = 'reserved';

    public const STATUS_CLEANING = 'cleaning';

    protected $fillable = [
        'tenant_id',
        'food_court_id',
        'table_no',
        'seats',
        'status',
        'current_dine_order_id',
        'pairing_code',
        'qr_string',
        'updated_by',
    ];

    protected $casts = [
        'seats' => 'integer',
        'status' => self::STATUS_FREE,
    ];

    protected $attributes = [
        'status' => self::STATUS_FREE,
        'seats' => 4,
    ];

    public function foodCourt(): BelongsTo
    {
        return $this->belongsTo(PosFoodCourt::class, 'food_court_id');
    }

    public function kosong(): bool
    {
        return $this->status === self::STATUS_FREE;
    }
}
