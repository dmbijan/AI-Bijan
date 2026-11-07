<?php

namespace App\Modules\OpenSEO\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Projek SEO — satu laman/janaan yang dioptimumkan.
 *
 * inspired oleh https://github.com/every-app/open-seo
 *
 * Melengkapkan modul `Seo` (metadata & audit) dengan intelCompetitive:
 * kedudukan SERP, celah kata kunci, backlink dan analisis pesaing.
 */
class SeoProject extends Model
{
    use BelongsToTenant;

    protected $table = 'seo_projects';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'domain',
        'base_url',
        'language',
        'locale',
        'country',
        'device',
        'status',
        'target_keywords',
        'competitors',
        'settings',
        'last_crawled_at',
    ];

    protected $casts = [
        'target_keywords' => 'array',
        'competitors' => 'array',
        'settings' => 'array',
        'last_crawled_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_ACTIVE,
        'language' => 'ms',
        'locale' => 'ms-MY',
        'country' => 'MY',
        'device' => 'desktop',
    ];

    /**
     * Peranti yang disokong untuk carian.
     *
     * @return array<int, string>
     */
    public static function peranti(): array
    {
        return ['desktop', 'mobile', 'tablet'];
    }
}
