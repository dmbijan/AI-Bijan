<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Meja dalam satu zon (BAB 88 / T-76). */
class POSDineTable extends Model
{
    use BelongsToTenant;

    public const STATUS_FREE = 'free';
    public const STATUS_OCCUPIED = 'occupied';
    public const STATUS_RESERVED = 'reserved';

    protected $table = 'pos_dine_tables';

    protected $fillable = [
        'tenant_id',
        'dine_area_id',
        'name',
        'code',
        'seats',
        'status',
        'is_active',
    ];

    protected $casts = [
        'seats' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'seats' => 4,
        'status' => self::STATUS_FREE,
        'is_active' => true,
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(POSDineArea::class, 'dine_area_id');
    }
}
