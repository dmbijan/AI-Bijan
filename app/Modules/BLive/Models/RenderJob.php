<?php

namespace App\Modules\BLive\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Render job - satu kerjaan render avatar atau video.
 *
 * Enjin `simulated` menghasilkan manifest deterministik supaya aliran
 * penuh boleh diuji tanpa GPU. Enjin `http`/`metahuman` bercakap dengan
 * renderer luar melalui HTTP.
 */
class RenderJob extends Model
{
    use BelongsToTenant;

    protected $table = 'blive_render_jobs';

    public const TYPE_AVATAR_GENERATE = 'avatar_generate';

    public const TYPE_PREVIEW = 'preview';

    public const TYPE_RECORDING = 'recording';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'avatar_id',
        'live_session_id',
        'job_type',
        'engine',
        'status',
        'input_payload',
        'output_payload',
        'error_message',
        'progress_percent',
        'duration_ms',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'input_payload' => 'array',
        'output_payload' => 'array',
        'progress_percent' => 'integer',
        'duration_ms' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'job_type' => self::TYPE_AVATAR_GENERATE,
        'engine' => 'simulated',
        'status' => self::STATUS_QUEUED,
        'progress_percent' => 0,
    ];

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    public function selesai(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
