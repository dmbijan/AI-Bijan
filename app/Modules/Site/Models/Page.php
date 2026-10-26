<?php

namespace App\Modules\Site\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    use BelongsToTenant;

    protected $table = 'site_pages';

    protected $fillable = [
        'tenant_id',
        'site_id',
        'title',
        'slug',
        'template',
        'status',
        'content',
        'sections',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_image',
        'author_id',
        'published_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'sections' => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the page.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the site this page belongs to.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * Get the author of this page.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'author_id');
    }

    /**
     * Generate slug from title.
     */
    public static function generateSlug(string $title): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
    }

    /**
     * Scope published pages.
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
     * Scope by template.
     */
    public function scopeByTemplate($query, string $template)
    {
        return $query->where('template', $template);
    }

    /**
     * Check if page is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    /**
     * Get full URL for this page.
     */
    public function getUrlAttribute(): string
    {
        $site = $this->site;
        $baseUrl = $site ? $site->getBaseUrl() : config('app.url');
        
        // Home page uses root URL
        if ($this->slug === '/' || $this->slug === '') {
            return rtrim($baseUrl, '/');
        }

        return rtrim($baseUrl, '/') . '/' . ltrim($this->slug, '/');
    }
}
