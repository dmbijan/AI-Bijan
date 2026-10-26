<?php

namespace App\Modules\ADE\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Coordination task — satu unit kerja dalam orchestration.
 *
 * Peranan (role) menentukan ejen subtype:
 *   implementer  — tulis kod
 *   tester       — jalankan ujian
 *   reviewer     — semak diff
 *   researcher   — kaji
 */
class AdeCoordinationTask extends Model
{
    use BelongsToTenant;

    public const STATUS_PENDING = 'pending';
    public const STATUS_RUNNING = 'running';
    public const STATUS_DONE = 'done';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SKIPPED = 'skipped';

    public const PERANAN = ['implementer', 'tester', 'reviewer', 'researcher'];

    protected $table = 'ade_coordination_tasks';

    protected $fillable = [
        'tenant_id', 'ade_coordination_id', 'ade_sandbox_id', 'title',
        'description', 'role', 'status', 'input', 'output', 'score',
        'duration_ms', 'error_message', 'order',
    ];

    protected $casts = [
        'input' => 'array',
        'output' => 'array',
        'score' => 'float',
        'duration_ms' => 'integer',
        'order' => 'integer',
    ];

    protected $attributes = [
        'role' => 'implementer',
        'status' => self::STATUS_PENDING,
        'order' => 0,
    ];

    public function coordination(): BelongsTo
    {
        return $this->belongsTo(AdeCoordination::class, 'ade_coordination_id');
    }

    public function sandbox(): BelongsTo
    {
        return $this->belongsTo(AdeSandbox::class, 'ade_sandbox_id');
    }

    public function berjaya(): bool
    {
        return $this->status === self::STATUS_DONE;
    }
}
