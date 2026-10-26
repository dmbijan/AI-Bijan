<?php

namespace App\Modules\Seo\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeoMetadata extends Model
{
    use BelongsToTenant;

    protected $table = 'seo_metadata';

    public const TYPE_PRODUCT = 'product';
    public const TYPE_PAGE = 'page';
    public const TYPE_ARTICLE = 'article';
    public const TYPE_VIDEO = 'video';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_BLOG_POST = 'blog_post';

    protected $fillable = [
        'tenant_id',
        'object_type',
        'object_id',
        'slug',
        'title',
        'description',
        'keywords',
        'canonical_url',
        'og_title',
        'og_description',
        'og_image_url',
        'og_type',
        'twitter_title',
        'twitter_description',
        'twitter_image_url',
        'json_ld',
        'hreflang',
        'no_index',
        'no_follow',
        'seo_score',
        'audit_findings',
        'is_published',
        'view_count',
    ];

    protected $casts = [
        'json_ld' => 'array',
        'hreflang' => 'array',
        'audit_findings' => 'array',
        'no_index' => 'boolean',
        'no_follow' => 'boolean',
        'is_published' => 'boolean',
        'view_count' => 'integer',
        'seo_score' => 'float',
    ];

    protected $attributes = [
        'og_type' => 'website',
        'no_index' => false,
        'no_follow' => false,
        'seo_score' => 0,
        'is_published' => false,
        'view_count' => 0,
    ];

    public function issues(): HasMany
    {
        return $this->hasMany(SeoAuditIssue::class, 'seo_metadata_id');
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(SeoSuggestion::class, 'seo_metadata_id');
    }

    /**
     * Meta tag lengkap untuk halaman.
     */
    public function metaTags(): array
    {
        $tags = [];

        if ($this->title) {
            $tags['title'] = $this->title;
        }

        if ($this->description) {
            $tags['description'] = $this->description;
        }

        if ($this->keywords) {
            $tags['keywords'] = $this->keywords;
        }

        if ($this->canonical_url) {
            $tags['canonical'] = $this->canonical_url;
        }

        if ($this->no_index || $this->no_follow) {
            $robots = [];
            if ($this->no_index) {
                $robots[] = 'noindex';
            }
            if ($this->no_follow) {
                $robots[] = 'nofollow';
            }
            $tags['robots'] = implode(', ', $robots);
        }

        return $tags;
    }

    /**
     * Tag Open Graph.
     */
    public function openGraphTags(): array
    {
        $tags = [
            'og:title' => $this->og_title ?: $this->title,
            'og:description' => $this->og_description ?: $this->description,
            'og:type' => $this->og_type,
        ];

        if ($this->og_image_url) {
            $tags['og:image'] = $this->og_image_url;
        }

        if ($this->url) {
            $tags['og:url'] = $this->url;
        }

        return array_filter($tags);
    }

    /**
     * Tag Twitter Card.
     */
    public function twitterTags(?string $handle = null): array
    {
        $tags = [
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $this->twitter_title ?: $this->title,
            'twitter:description' => $this->twitter_description ?: $this->description,
        ];

        if ($this->twitter_image_url ?: $this->og_image_url) {
            $tags['twitter:image'] = $this->twitter_image_url ?: $this->og_image_url;
        }

        if ($handle) {
            $tags['twitter:site'] = $handle;
        }

        return array_filter($tags);
    }
}
