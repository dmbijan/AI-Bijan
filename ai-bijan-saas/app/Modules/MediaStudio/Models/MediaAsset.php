<?php

namespace App\Modules\MediaStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Aset media — fail yang disimpan di Media Vault.
 */
class MediaAsset extends Model
{
    use BelongsToTenant;

    protected $table = 'media_assets';

    public const KIND_IMAGE = 'image';
    public const KIND_VIDEO = 'video';
    public const KIND_AUDIO = 'audio';
    public const KIND_DOCUMENT = 'document';

    public const SOURCE_UPLOAD = 'upload';
    public const SOURCE_GENERATED = 'generated';
    public const SOURCE_RECORDED = 'recorded';
    public const SOURCE_SCREEN = 'screen';

    protected $fillable = [
        'tenant_id',
        'kind',
        'storage_disk',
        'storage_path',
        'original_name',
        'mime',
        'size_bytes',
        'duration_seconds',
        'width',
        'height',
        'checksum',
        'source',
        'meta',
        'retention_expires_at',
        'deleted_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'retention_expires_at' => 'datetime',
        'deleted_at' => 'datetime',
        'size_bytes' => 'integer',
        'duration_seconds' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function jobs(): HasMany
    {
        return $this->hasMany(MediaJob::class, 'output_asset_id');
    }

    public function understanding()
    {
        return $this->hasOne(MediaUnderstanding::class, 'asset_id');
    }

    public function transcript()
    {
        return $this->hasOne(MediaTranscript::class, 'asset_id');
    }

    public function isExpired(): bool
    {
        return $this->retention_expires_at !== null && $this->retention_expires_at->isPast();
    }

    public function isVideo(): bool
    {
        return $this->kind === self::KIND_VIDEO;
    }
}
