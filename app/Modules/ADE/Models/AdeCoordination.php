<?php

namespace App\Modules\ADE\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Coordination — satu nominally orchestrator yang memecah matlamat kepada
 * berbilang subtugas yang dijalankan serentak oleh sub-ejen.
 *
 * Corak `obra/superpowers`: subagent-driven-development — satu ejen per tugas,
 * semakan selepas setiap satu.
 */
class AdeCoordination extends Model
{
    use BelongsToTenant;

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_REVIEW = 'review';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'ade_coordinations';

    protected $fillable = [
        'tenant_id', 'name', 'objective', 'status', 'max_parallel',
        'task_count', 'completed_count', 'failed_count', 'duration_ms',
        'result', 'ade_sandbox_id', 'created_by',
    ];

    protected $casts = [
        'result' => 'array',
        'max_parallel' => 'integer',
        'task_count' => 'integer',
        'completed_count' => 'integer',
        'failed_count' => 'integer',
        'duration_ms' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'max_parallel' => 4,
        'task_count' => 0,
        'completed_count' => 0,
        'failed_count' => 0,
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(AdeCoordinationTask::class, 'ade_coordination_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(AdeReview::class, 'ade_coordination_id');
    }

    public function sandbox(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(AdeSandbox::class, 'ade_sandbox_id');
    }

    public function isSelesai(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED, self::STATUS_CANCELLED], true);
    }

    /**
     * Peratusrama berdasarkan kiraan sebenar task — bukan kolum yang boleh stale.
     */
    public function peratus(): float
    {
        $total = AdeCoordinationTask::where('ade_coordination_id', $this->id)->count();

        if ($total === 0) {
            return 0.0;
        }

        $done = AdeCoordinationTask::where('ade_coordination_id', $this->id)
            ->whereIn('status', ['done', 'failed', 'skipped'])
            ->count();

        return round(($done / $total) * 100, 2);
    }
}
