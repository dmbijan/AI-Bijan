<?php

namespace App\Modules\MediaEditor\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaProject extends Model
{
    use BelongsToTenant;

    protected $table = 'media_projects';

    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_CAROUSEL = 'carousel';
    public const TYPE_STORY = 'story';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_EDITING = 'editing';
    public const STATUS_RENDERING = 'rendering';
    public const STATUS_READY = 'ready';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'name',
        'project_type',
        'status',
        'canvas',
        'layers',
        'timeline',
        'brand_kit',
        'output_url',
        'thumbnail_url',
        'duration_seconds',
        'resolution',
        'meta',
        'created_by',
    ];

    protected $casts = [
        'canvas' => 'array',
        'layers' => 'array',
        'timeline' => 'array',
        'brand_kit' => 'array',
        'meta' => 'array',
        'duration_seconds' => 'integer',
    ];

    protected $attributes = [
        'project_type' => self::TYPE_IMAGE,
        'status' => self::STATUS_DRAFT,
        'resolution' => '1080x1080',
    ];

    public function renderJobs(): HasMany
    {
        return $this->hasMany(MediaRenderJob::class, 'media_project_id');
    }

    public function jumlahLapisan(): int
    {
        return count($this->layers ?? []);
    }

    public function sedia(): bool
    {
        return $this->status === self::STATUS_READY;
    }
}
