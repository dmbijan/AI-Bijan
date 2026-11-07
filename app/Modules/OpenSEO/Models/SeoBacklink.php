<?php

namespace App\Modules\OpenSEO\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Backlink — satu pautan masuk yang menuju laman tenant.
 */
class SeoBacklink extends Model
{
    use BelongsToTenant;

    protected $table = 'seo_backlinks';

    protected $fillable = [
        'tenant_id',
        'seo_project_id',
        'source_url',
        'source_domain',
        'source_title',
        'target_url',
        'anchor_text',
        'domain_authority',
        'page_authority',
        'link_type',
        'is_dofollow',
        'first_seen_at',
        'last_seen_at',
        'is_lost',
        'status',
    ];

    protected $casts = [
        'domain_authority' => 'integer',
        'page_authority' => 'integer',
        'is_dofollow' => 'boolean',
        'is_lost' => 'boolean',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    protected $attributes = [
        'link_type' => 'text',
        'is_dofollow' => true,
        'is_lost' => false,
        'status' => 'active',
    ];

    public function seoProject(): BelongsTo
    {
        return $this->belongsTo(SeoProject::class, 'seo_project_id');
    }
}
