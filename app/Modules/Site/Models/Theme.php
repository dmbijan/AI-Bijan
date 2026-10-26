<?php

namespace App\Modules\Site\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Theme extends Model
{
    use BelongsToTenant;

    protected $table = 'site_themes';

    protected $fillable = [
        'tenant_id',
        'site_id',
        'name',
        'slug',
        'version',
        'author',
        'description',
        'preview_image',
        'settings',
        'is_active',
        'is_default',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Get the tenant that owns the theme.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the site using this theme.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * Scope active themes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope default themes.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
