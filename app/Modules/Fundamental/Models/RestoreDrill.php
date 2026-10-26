<?php

namespace App\Modules\Fundamental\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Latihan pemulihan — rekod bukti bahawa backup boleh dipulihkan.
 */
class RestoreDrill extends Model
{
    use BelongsToTenant;

    public const STATUS_PLANNED = 'planned';
    public const STATUS_RUNNING = 'running';
    public const STATUS_PASSED = 'passed';
    public const STATUS_FAILED = 'failed';

    public const TYPE_FULL = 'full_restore';
    public const TYPE_TENANT = 'tenant_restore';
    public const TYPE_POINT_IN_TIME = 'point_in_time';

    protected $table = 'restore_drills';

    protected $fillable = [
        'tenant_id',
        'backup_snapshot_id',
        'drill_type',
        'status',
        'rpo_actual_minutes',
        'rto_actual_minutes',
        'rpo_target_minutes',
        'rto_target_minutes',
        'passed',
        'findings',
        'performed_by_user_id',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'rpo_actual_minutes' => 'integer',
        'rto_actual_minutes' => 'integer',
        'rpo_target_minutes' => 'integer',
        'rto_target_minutes' => 'integer',
        'passed' => 'boolean',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(BackupSnapshot::class, 'backup_snapshot_id');
    }
}
