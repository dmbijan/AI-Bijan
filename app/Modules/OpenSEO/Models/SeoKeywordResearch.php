<?php

namespace App\Modules\OpenSEO\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Kata kunci SEO — disalin daripada modul `Seo` untuk intel Competitive.
 */
class SeoKeywordResearch extends Model
{
    use BelongsToTenant;

    protected $table = 'seo_keyword_research';

    protected $fillable = [
        'tenant_id',
        'seo_project_id',
        'keyword',
        'intent',
        'search_volume',
        'difficulty',
        'cpc',
        'competition',
        'current_position',
        'best_position',
        'volume_trend',
        'difficulty_trend',
        'priority_score',
        'content_gap',
        'notes',
    ];

    protected $casts = [
        'search_volume' => 'integer',
        'difficulty' => 'integer',
        'cpc' => 'float',
        'current_position' => 'integer',
        'best_position' => 'integer',
        'volume_trend' => 'float',
        'difficulty_trend' => 'float',
        'priority_score' => 'float',
        'content_gap' => 'boolean',
    ];

    protected $attributes = [
        'intent' => 'informational',
        'current_position' => 0,
        'best_position' => 0,
        'volume_trend' => 0,
        'difficulty_trend' => 0,
        'priority_score' => 0,
        'content_gap' => false,
    ];

    public function seoProject(): BelongsTo
    {
        return $this->belongsTo(SeoProject::class, 'seo_project_id');
    }

    /**
     * Adakah kata kunci ini bernilai tinggi dan belum TERMSUK?
     */
    public function wiggle(): bool
    {
        return $this->current_position === 0
            && $this->search_volume > 0
            && $this->difficulty <= 40;
    }
}
