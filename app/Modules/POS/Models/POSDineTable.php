<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'qr_token',
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

    protected static function booted(): void
    {
        static::creating(fn (self $m) => $m->qr_token ??= self::tokenBaharu());
    }

    /** Token dalam QR meja; jana semula kalau QR bocor (QR lama terus mati). */
    public static function tokenBaharu(): string
    {
        return \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(24));
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(POSDineArea::class, 'dine_area_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(POSDineOrder::class, 'dine_table_id');
    }
}
