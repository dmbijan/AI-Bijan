<?php

namespace App\Modules\ContentForge\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Dokumen kandungan — artikel panjang, ulasan produk, senarai produk.
 *
 * Berasal daripada SEO Writing AI (seowriting.ai).
 */
class ContentDocument extends Model
{
    use BelongsToTenant;

    protected $table = 'content_documents';

    public const TYPE_ARTICLE = 'article';

    public const TYPE_PRODUCT_ROUNDUP = 'product_roundup';

    public const TYPE_PRODUCT_REVIEW = 'product_review';

    public const TYPE_HOW_TO = 'how_to';

    public const TYPE_PRODUCT_LISTING = 'product_listing';

    public const TYPE_LANDING = 'landing';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_GENERATING = 'generating';

    public const STATUS_GENERATED = 'generated';

    public const STATUS_OPTIMIZING = 'optimizing';

    public const STATUS_READY = 'ready';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'title',
        'slug',
        'type',
        'status',
        'language',
        'brief',
        'keywords',
        'target_word_count',
        'actual_word_count',
        'body_html',
        'body_markdown',
        'outline',
        'meta_title',
        'meta_description',
        'serp_analysis',
        'seo_score',
        'seo_issues',
        'generated_by',
        'model_id',
        'tokens_used',
        'cost_sen',
        'published_url',
        'published_at',
        'wordpress_site_id',
        'scheduled_publish_at',
        'created_by',
    ];

    protected $casts = [
        'keywords' => 'array',
        'outline' => 'array',
        'serp_analysis' => 'array',
        'seo_issues' => 'array',
        'target_word_count' => 'integer',
        'actual_word_count' => 'integer',
        'seo_score' => 'integer',
        'tokens_used' => 'integer',
        'cost_sen' => 'integer',
        'published_at' => 'datetime',
        'scheduled_publish_at' => 'datetime',
    ];

    protected $attributes = [
        'type' => self::TYPE_ARTICLE,
        'status' => self::STATUS_DRAFT,
        'language' => 'ms',
        'target_word_count' => 1500,
        'actual_word_count' => 0,
        'seo_score' => 0,
        'tokens_used' => 0,
        'cost_sen' => 0,
    ];

    public function sediaTerbit(): bool
    {
        return in_array($this->status, [self::STATUS_READY, self::STATUS_GENERATED], true);
    }
}
