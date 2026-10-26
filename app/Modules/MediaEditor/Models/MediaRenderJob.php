<?php

namespace App\Modules\MediaEditor\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaRenderJob extends Model
{
    use BelongsToTenant;

    protected $table = 'media_render_jobs';

    public const STATUS_QUEUED = 'queued';
    public const STATUS_RENDERING = 'rendering';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'media_project_id',
        'status',
        'output_format',
        'resolution',
        'output_url',
        'error_message',
        'duration_ms',
        'file_size_kb',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'duration_ms' => 'integer',
        'file_size_kb' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_QUEUED,
        'output_format' => 'mp4',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(MediaProject::class, 'media_project_id');
    }

    public function selesai(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
