<?php

namespace App\Modules\Seo\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeoProfile extends Model
{
    use BelongsToTenant;

    protected $table = 'seo_profiles';

    protected $fillable = [
        'tenant_id',
        'site_name',
        'site_url',
        'default_title_template',
        'default_description_template',
        'default_keyword',
        'og_image_url',
        'twitter_handle',
        'twitter_card_type',
        'robots_txt',
        'google_site_verification',
        'bing_site_verification',
        'locale',
        'timezone',
        'auto_sitemap',
        'auto_robots',
        'is_indexable',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'auto_sitemap' => 'boolean',
        'auto_robots' => 'boolean',
        'is_indexable' => 'boolean',
    ];

    protected $attributes = [
        'default_title_template' => '{page_title} | {site_name}',
        'twitter_card_type' => 'summary_large_image',
        'locale' => 'en-MY',
        'timezone' => 'Asia/Kuala_Lumpur',
        'auto_sitemap' => true,
        'auto_robots' => true,
        'is_indexable' => true,
    ];

    public function metadata(): HasMany
    {
        return $this->hasMany(SeoMetadata::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Render templat tajuk dengan placeholder.
     */
    public function renderTitle(string $pageTitle): string
    {
        return str_replace(
            ['{page_title}', '{site_name}'],
            [$pageTitle, $this->site_name ?? ''],
            $this->default_title_template
        );
    }

    /**
     * robots.txt lalai (bukan robots_txt tersuai).
     */
    public function robotsLalai(): string
    {
        if ($this->robots_txt) {
            return $this->robots_txt;
        }

        $baris = ["User-agent: *"];

        if (! $this->is_indexable) {
            $baris[] = 'Disallow: /';
        } else {
            $baris[] = 'Disallow: /admin';
            $baris[] = 'Disallow: /api/';
            $baris[] = 'Allow: /';
        }

        if ($this->site_url) {
            $baris[] = '';
            $baris[] = 'Sitemap: ' . rtrim($this->site_url, '/') . '/sitemap.xml';
        }

        return implode("\n", $baris);
    }
}
