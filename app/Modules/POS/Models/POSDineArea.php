<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Zon tempat duduk (BAB 88 / T-76) — cth "Dewan Utama", "Beranda". */
class POSDineArea extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_dine_areas';

    protected $fillable = ['tenant_id', 'name', 'code', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    protected $attributes = ['is_active' => true];

    public function tables(): HasMany
    {
        return $this->hasMany(POSDineTable::class, 'dine_area_id');
    }
}
