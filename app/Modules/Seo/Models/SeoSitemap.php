<?php

namespace App\Modules\Seo\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SeoSitemap extends Model
{
    use BelongsToTenant;

    protected $table = 'seo_sitemaps';

    protected $fillable = [
        'tenant_id',
        'object_type',
        'object_id',
        'url',
        'changefreq',
        'priority',
        'lastmod',
        'is_indexed',
        'indexed_at',
    ];

    protected $casts = [
        'lastmod' => 'datetime',
        'indexed_at' => 'datetime',
        'is_indexed' => 'boolean',
        'priority' => 'float',
    ];

    protected $attributes = [
        'changefreq' => 'weekly',
        'priority' => 0.50,
        'is_indexed' => false,
    ];
}
