<?php

namespace App\Modules\Site\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use BelongsToTenant;

    protected $table = 'site_posts';

    protected $fillable = [
        'tenant_id',
        'site_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'status',
        'author_id',
        'published_at',
        'meta_title',
        'meta_description',
        'tags',
        'views_count',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'featured_image' => 'string',
        'published_at' => 'datetime',
        'tags' => 'array',
    ];

    /**
     * Get the tenant that owns the post.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the site this post belongs to.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * Get the author of this post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id');
    }

    /**
     * Get all comments for this post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(PostComment::class, 'post_id');
    }

    /**
     * Generate slug from title.
     */
    public static function generateSlug(string $title): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
    }

    /**
     * Scope published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by tag.
     */
    public function scopeByTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Check if post is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    /**
     * Record a view.
     */
    public function recordView(): void
    {
        $this->increment('views_count');
    }

    /**
     * Get full URL for this post.
     */
    public function getUrlAttribute(): string
    {
        $site = $this->site;
        $baseUrl = $site ? $site->getBaseUrl() : config('app.url');
        
        return rtrim($baseUrl, '/') . '/blog/' . ltrim($this->slug, '/');
    }
}
