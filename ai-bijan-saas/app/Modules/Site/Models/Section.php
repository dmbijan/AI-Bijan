<?php

namespace App\Modules\Site\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Section extends Model
{
    use BelongsToTenant;

    protected $table = 'site_sections';

    protected $fillable = [
        'tenant_id',
        'page_id',
        'type',
        'name',
        'settings',
        'content',
        'styles',
        'order',
        'is_visible',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'content' => 'array',
        'styles' => 'array',
        'is_visible' => 'boolean',
    ];

    /**
     * Get the tenant that owns the section.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the page this section belongs to.
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class, 'page_id');
    }

    /**
     * Scope by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope visible sections.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    /**
     * Order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
