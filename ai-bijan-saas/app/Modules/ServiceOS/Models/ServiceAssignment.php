<?php

namespace App\Modules\ServiceOS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Penugasan kru ke projek / sesi tertentu.
 *
 * Kekangan unik: (tenant, sesi, kru, peranan) — elak kru ditugaskan
 * dua kali dalam peranan sama pada sesi yang sama.
 */
class ServiceAssignment extends Model
{
    use BelongsToTenant;

    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_ASSIGNED,
        self::STATUS_CONFIRMED,
        self::STATUS_DECLINED,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    protected $table = 'service_assignments';

    protected $fillable = [
        'tenant_id',
        'service_project_id',
        'service_session_id',
        'crew_id',
        'role',
        'status',
        'start_at',
        'end_at',
        'agreed_rate',
        'notes',
        'confirmed_at',
        'completed_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'agreed_rate' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ServiceProject::class, 'service_project_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ServiceSession::class, 'service_session_id');
    }

    public function crew(): BelongsTo
    {
        return $this->belongsTo(ServiceCrew::class, 'crew_id');
    }

    /** Adakah penugasan ini bertindih masa dengan penugasan lain? */
    public function bertindihDengan(self $lain): bool
    {
        if (! $this->start_at || ! $this->end_at || ! $lain->start_at || ! $lain->end_at) {
            return false;
        }

        return $this->start_at->lt($lain->end_at) && $this->end_at->gt($lain->start_at);
    }
}
