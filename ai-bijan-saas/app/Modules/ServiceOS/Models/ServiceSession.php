<?php

namespace App\Modules\ServiceOS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Sesi kerja dalam projek — tarikh/masa sebenar kerja dijalankan.
 */
class ServiceSession extends Model
{
    use BelongsToTenant;

    public const STATUS_PLANNED = 'planned';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PLANNED,
        self::STATUS_CONFIRMED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public const TYPE_MAIN = 'main';
    public const TYPE_TRIAL = 'trial';
    public const TYPE_SETUP = 'setup';
    public const TYPE_TEARDOWN = 'teardown';
    public const TYPE_FOLLOWUP = 'followup';

    public const TYPES = [
        self::TYPE_MAIN,
        self::TYPE_TRIAL,
        self::TYPE_SETUP,
        self::TYPE_TEARDOWN,
        self::TYPE_FOLLOWUP,
    ];

    protected $table = 'service_sessions';

    protected $fillable = [
        'tenant_id',
        'service_project_id',
        'name',
        'session_type',
        'status',
        'start_at',
        'end_at',
        'actual_start_at',
        'actual_end_at',
        'venue',
        'sequence',
        'notes',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'actual_start_at' => 'datetime',
        'actual_end_at' => 'datetime',
        'sequence' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ServiceProject::class, 'service_project_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ServiceAssignment::class, 'service_session_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ServiceTask::class, 'service_session_id');
    }

    public function equipmentReservations(): HasMany
    {
        return $this->hasMany(ServiceEquipmentReservation::class, 'service_session_id');
    }

    /** Durasi sesi dalam minit. */
    public function durasiMinit(): int
    {
        if (! $this->start_at || ! $this->end_at) {
            return 0;
        }

        return (int) $this->start_at->diffInMinutes($this->end_at);
    }

    /** Adakah sesi bertindih dengan sesi lain? */
    public function bertindihDengan(self $lain): bool
    {
        if (! $this->start_at || ! $this->end_at || ! $lain->start_at || ! $lain->end_at) {
            return false;
        }

        return $this->start_at->lt($lain->end_at) && $this->end_at->gt($lain->start_at);
    }
}
