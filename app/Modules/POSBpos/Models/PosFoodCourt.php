<?php

namespace App\Modules\POSBpos\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Food court — kawasan berbilang gerai berkongsi tempat duduk.
 *
 * Berasal daripada B-POS ciri `food_courts`.
 */
class PosFoodCourt extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_food_courts';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'kv',
        'description',
        'capacity',
        'is_active',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected $attributes = [
        'is_active' => true,
        'capacity' => 0,
    ];

    public function meja(): HasMany
    {
        return $this->hasMany(PosFoodCourtTable::class, 'food_court_id');
    }
}
