<?php

namespace App\Modules\ContentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ContentPlatformMetric extends Model
{
    use BelongsToTenant;

    protected $table = 'content_platform_metrics';

    protected $fillable = [
        'tenant_id',
        'content_platform_id',
        'metric_date',
        'followers',
        'followers_gained',
        'impressions',
        'reach',
        'engagements',
        'clicks',
        'posts_published',
        'comments_received',
        'negative_comments',
        'engagement_rate',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'metric_date' => 'date',
        'followers' => 'integer',
        'followers_gained' => 'integer',
        'impressions' => 'integer',
        'reach' => 'integer',
        'engagements' => 'integer',
        'clicks' => 'integer',
        'posts_published' => 'integer',
        'comments_received' => 'integer',
        'negative_comments' => 'integer',
        'engagement_rate' => 'float',
    ];

    protected $attributes = [
        'followers' => 0,
        'followers_gained' => 0,
        'impressions' => 0,
        'reach' => 0,
        'engagements' => 0,
        'clicks' => 0,
        'posts_published' => 0,
        'comments_received' => 0,
        'negative_comments' => 0,
        'engagement_rate' => 0,
    ];
}
