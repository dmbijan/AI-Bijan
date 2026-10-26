<?php

namespace App\Modules\Site\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use BelongsToTenant;

    protected $table = 'sites';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'logo_url',
        'favicon_url',
        'primary_domain',
        'secondary_domains',
        'theme',
        'status',
        'is_live',
        'maintenance_mode',
        'coming_soon_mode',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'og_image',
        'google_analytics_id',
        'google_tag_manager_id',
        'facebook_pixel_id',
        'custom_head_code',
        'custom_body_code',
        'language',
        'timezone',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'secondary_domains' => 'array',
        'is_live' => 'boolean',
        'maintenance_mode' => 'boolean',
        'coming_soon_mode' => 'boolean',
        'seo_keywords' => 'array',
        'og_image' => 'string',
        'custom_head_code' => 'string',
        'custom_body_code' => 'string',
    ];

    /**
     * Get the tenant that owns the site.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get all pages for this site.
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class, 'site_id');
    }

    /**
     * Get all blog posts for this site.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'site_id');
    }

    /**
     * Get all domains for this site.
     */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class, 'site_id');
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    }

    /**
     * Scope live sites.
     */
    public function scopeLive($query)
    {
        return $query->where('is_live', true);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if site has maintenance mode enabled.
     */
    public function isUnderMaintenance(): bool
    {
        return $this->maintenance_mode;
    }

    /**
     * Get full URL for primary domain.
     */
    public function getBaseUrl(): string
    {
        $domain = $this->primary_domain ?? config('app.url');
        return rtrim($domain, '/');
    }

    /**
     * Scope by theme.
     */
    public function scopeByTheme($query, string $theme)
    {
        return $query->where('theme', $theme);
    }
}
