<?php

namespace App\Modules\VirtualOffice\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'vo_schedules';

    protected $fillable = [
        'tenant_id',
        'agent_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'type',
        'is_recurring',
        'recurrence_pattern',
        'location',
        'meeting_link',
        'status',
        'attendees',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'recurrence_pattern' => 'array',
        'attendees' => 'array',
    ];

    /**
     * Get the tenant that owns the schedule.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the agent associated with this schedule.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    /**
     * Get attendees for this schedule.
     */
    public function attendees(): HasMany
    {
        return $this->hasMany(ScheduleAttendee::class);
    }

    /**
     * Check if schedule is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_time->isFuture() && $this->status === 'scheduled';
    }

    /**
     * Check if schedule is in progress.
     */
    public function isInProgress(): bool
    {
        $now = now();
        return $this->start_time->lte($now) && $this->end_time->gte($now) && $this->status === 'scheduled';
    }

    /**
     * Mark schedule as completed.
     */
    public function complete(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Cancel schedule.
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Get schedules within a date range.
     */
    public static function findByRange(int $tenantId, string $startDate, string $endDate): \Illuminate\Database\Eloquent\Builder
    {
        return static::where('tenant_id', $tenantId)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->orderBy('start_time')
            ->get();
    }
}
