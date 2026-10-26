<?php

namespace App\Modules\MediaStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Transkripsi audio/video.
 */
class MediaTranscript extends Model
{
    use BelongsToTenant;

    protected $table = 'media_transcripts';

    protected $fillable = [
        'tenant_id',
        'asset_id',
        'language',
        'segments',
        'text',
        'confidence',
        'keywords',
    ];

    protected $casts = [
        'segments' => 'array',
        'keywords' => 'array',
        'confidence' => 'decimal:4',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'asset_id');
    }
}
