<?php

namespace App\Modules\OpenGenAI\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * satu penjanaan (generation run) — satu permintaan ke provider.
 *
 * Menyimpan prompt, output, metrik token dan status supaya audit,
 * kos dan penebusan semula (replay) boleh dilakukan.
 */
class GenAiJob extends Model
{
    use BelongsToTenant;

    protected $table = 'gen_ai_jobs';

    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const KEMAHRIRAN_TEXT = 'text';

    public const KEMAHRIRAN_IMAGE = 'image';

    public const KEMAHRIRAN_VIDEO = 'video';

    public const KEMAHRIRAN_AUDIO = 'audio';

    public const KEMAHRIRAN_CODE = 'code';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'provider_id',
        'model',
        'capability',
        'task_type',
        'status',
        'prompt',
        'system_prompt',
        'parameters',
        'output_text',
        'output_url',
        'output_meta',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'cost_rm',
        'duration_ms',
        'error_message',
        'correlation_id',
        'created_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'output_meta' => 'array',
        'prompt_tokens' => 'integer',
        'completion_tokens' => 'integer',
        'total_tokens' => 'integer',
        'cost_rm' => 'float',
        'duration_ms' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'capability' => self::KEMAHRIRAN_TEXT,
        'prompt_tokens' => 0,
        'completion_tokens' => 0,
        'total_tokens' => 0,
        'cost_rm' => 0,
        'duration_ms' => 0,
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(GenAiProvider::class, 'provider_id');
    }

    public function berjaya(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED;
    }

    public function sedangProses(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_RUNNING], true);
    }
}
