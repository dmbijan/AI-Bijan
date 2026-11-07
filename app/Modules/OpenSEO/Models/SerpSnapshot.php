<?php

namespace App\Modules\OpenSEO\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu catatan kedudukan SERP untuk satu kata kunci.
 *
 * Menyimpan sejarah kedudukan supaya carta Trent dapat dibina.
 */
class SerpSnapshot extends Model
{
    use BelongsToTenant;

    protected $table = 'serp_snapshots';

    protected $fillable = [
        'tenant_id',
        'seo_project_id',
        'keyword',
        'position',
        'previous_position',
        'url',
        'title',
        'description',
        'search_volume',
        'difficulty',
        'device',
        'country',
        'serp_features',
        'competitors',
        'checked_at',
    ];

    protected $casts = [
        'serp_features' => 'array',
        'competitors' => 'array',
        'position' => 'integer',
        'previous_position' => 'integer',
        'search_volume' => 'integer',
        'difficulty' => 'integer',
        'checked_at' => 'datetime',
    ];

    protected $attributes = [
        'position' => 0,
        'device' => 'desktop',
        'country' => 'MY',
    ];

    public function seoProject(): BelongsTo
    {
        return $this->belongsTo(SeoProject::class, 'seo_project_id');
    }

    /**
     * Adakah kedudukan improved berbanding semasa sebelum?
     */
    public function membaik(): bool
    {
        if ($this->previous_position === null || $this->previous_position === 0) {
            return $this->position > 0;
        }

        return $this->position < $this->previous_position;
    }
}
