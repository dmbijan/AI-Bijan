<?php

namespace App\Modules\ServiceOS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tugas / checklist dalam projek perkhidmatan.
 */
class ServiceTask extends Model
{
    use BelongsToTenant;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_DONE = 'done';
    public const STATUS_SKIPPED = 'skipped';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_PROGRESS,
        self::STATUS_BLOCKED,
        self::STATUS_DONE,
        self::STATUS_SKIPPED,
    ];

    public const PHASE_PREPARATION = 'preparation';
    public const PHASE_EXECUTION = 'execution';
    public const PHASE_POST = 'post';
    public const PHASE_ADMIN = 'admin';

    public const PHASES = [
        self::PHASE_PREPARATION,
        self::PHASE_EXECUTION,
        self::PHASE_POST,
        self::PHASE_ADMIN,
    ];

    protected $table = 'service_tasks';

    protected $fillable = [
        'tenant_id',
        'service_project_id',
        'service_session_id',
        'assignee_user_id',
        'assignee_crew_id',
        'title',
        'description',
        'phase',
        'status',
        'priority',
        'due_at',
        'completed_at',
        'sequence',
        'is_mandatory',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'completed_at' => 'datetime',
        'sequence' => 'integer',
        'is_mandatory' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ServiceProject::class, 'service_project_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ServiceSession::class, 'service_session_id');
    }

    public function assigneeCrew(): BelongsTo
    {
        return $this->belongsTo(ServiceCrew::class, 'assignee_crew_id');
    }

    /** Adakah tugas ini lewat? */
    public function lewat(): bool
    {
        if ($this->status === self::STATUS_DONE || $this->status === self::STATUS_SKIPPED) {
            return false;
        }

        return $this->due_at !== null && $this->due_at->isPast();
    }
}
