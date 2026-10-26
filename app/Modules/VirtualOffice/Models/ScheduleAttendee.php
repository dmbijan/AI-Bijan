<?php

namespace App\Modules\VirtualOffice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleAttendee extends Model
{
    use HasFactory;

    protected $table = 'vo_schedule_attendees';

    protected $fillable = [
        'schedule_id',
        'user_id',
        'response',
        'responded_at',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    /**
     * Get the schedule this attendee belongs to.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Check if attendee accepted.
     */
    public function isAccepted(): bool
    {
        return $this->response === 'accepted';
    }

    /**
     * Check if attendee declined.
     */
    public function isDeclined(): bool
    {
        return $this->response === 'declined';
    }
}
