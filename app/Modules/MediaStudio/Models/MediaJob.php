<?php

namespace App\Modules\MediaStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Job media — understanding, generation, translation, training.
 */
class MediaJob extends Model
{
    use BelongsToTenant;

    protected $table = 'media_jobs';

    public const KIND_UNDERSTANDING = 'understanding';
    public const KIND_GENERATION = 'generation';
    public const KIND_TRANSLATION = 'translation';
    public const KIND_LIPSYNC = 'lipsync';
    public const KIND_TRAINING = 'training';

    public const STATUS_QUEUED = 'queued';
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCEEDED = 'succeeded';
    public const STATUS_FAILED = 'failed';
    public const STATUS_AWAITING_APPROVAL = 'awaiting_approval';

    protected $fillable = [
        'tenant_id',
        'kind',
        'status',
        'provider',
        'provider_job_id',
        'progress',
        'input_asset_id',
        'output_asset_id',
        'persona_id',
        'params',
        'error',
        'autonomy_level',
        'approval_required',
        'approved_by',
        'approved_at',
        'started_at',
        'finished_at',
        'cost_credits',
        'requested_by',
    ];

    protected $casts = [
        'params' => 'array',
        'progress' => 'integer',
        'approval_required' => 'boolean',
        'autonomy_level' => 'integer',
        'cost_credits' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function inputAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'input_asset_id');
    }

    public function outputAsset(): BelongsTo
    {
        return $this->belongsTo(MediaAsset::class, 'output_asset_id');
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(MediaPersona::class, 'persona_id');
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUCCEEDED,
            self::STATUS_FAILED,
        ], true);
    }
}
