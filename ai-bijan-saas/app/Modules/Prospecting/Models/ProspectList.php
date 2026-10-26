<?php

namespace App\Modules\Prospecting\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Senarai prospek — kumpulan mengikut ICP (ideal customer profile).
 */
class ProspectList extends Model
{
    use BelongsToTenant;

    protected $table = 'prospect_lists';

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'criteria',
        'icp',
    ];

    protected $casts = [
        'criteria' => 'array',
        'icp' => 'array',
    ];

    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class, 'list_id');
    }
}
