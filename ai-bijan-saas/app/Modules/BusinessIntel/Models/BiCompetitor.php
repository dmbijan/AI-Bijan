<?php

namespace App\Modules\BusinessIntel\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiCompetitor extends Model
{
    use BelongsToTenant;

    protected $table = 'bi_competitors';

    public const THREAT_LOW = 'low';
    public const THREAT_MEDIUM = 'medium';
    public const THREAT_HIGH = 'high';

    protected $fillable = [
        'tenant_id',
        'name',
        'website',
        'industry',
        'country',
        'channels',
        'products',
        'pricing',
        'strengths',
        'weaknesses',
        'positioning',
        'threat_level',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'channels' => 'array',
        'products' => 'array',
        'pricing' => 'array',
        'meta' => 'array',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'country' => 'MY',
        'threat_level' => self::THREAT_MEDIUM,
        'is_active' => true,
    ];

    public function snapshots(): HasMany
    {
        return $this->hasMany(BiCompetitorSnapshot::class, 'bi_competitor_id');
    }
}
