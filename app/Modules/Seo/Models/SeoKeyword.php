<?php

namespace App\Modules\Seo\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SeoKeyword extends Model
{
    use BelongsToTenant;

    protected $table = 'seo_keywords';

    public const INTENT_INFORMATIONAL = 'informational';
    public const INTENT_COMMERCIAL = 'commercial';
    public const INTENT_TRANSACTIONAL = 'transactional';
    public const INTENT_NAVIGATIONAL = 'navigational';

    protected $fillable = [
        'tenant_id',
        'keyword',
        'intent',
        'source',
        'search_volume',
        'difficulty',
        'current_rank',
        'target_url',
        'is_tracked',
        'related_keywords',
    ];

    protected $casts = [
        'related_keywords' => 'array',
        'search_volume' => 'integer',
        'difficulty' => 'integer',
        'current_rank' => 'float',
        'is_tracked' => 'boolean',
    ];

    protected $attributes = [
        'intent' => self::INTENT_INFORMATIONAL,
        'source' => 'manual',
        'is_tracked' => false,
    ];
}
