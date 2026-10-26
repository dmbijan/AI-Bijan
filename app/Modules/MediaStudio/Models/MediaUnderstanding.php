<?php

namespace App\Modules\MediaStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hasil pemahaman video — ringkasan, tindakan, entiti, QA pairs.
 */
class MediaUnderstanding extends Model
{
    use BelongsToTenant;

    protected $table = 'media_understandings';

    protected $fillable = [
        'tenant_id',
        'asset_id',
        'summary',
        'actions',
        'entities',
        'timeline',
        'qa_pairs',
        'model',
        'language',
        'confidence',
    ];

    protected $casts = [
        'actions' => 'array',
        'entities' => 'array',
        'timeline' => 'array',
        'qa_pairs' => 'array',
        'confidence' => 'decimal:4',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'asset_id');
    }
}
