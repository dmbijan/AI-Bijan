<?php

namespace App\Modules\Booking\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingReminder extends Model
{
    use BelongsToTenant;

    protected $table = 'booking_reminders';

    protected $fillable = [
        'tenant_id',
        'appointment_id',
        'channel',
        'status',
        'scheduled_at',
        'sent_at',
        'template',
        'payload',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'payload' => 'array',
    ];

    /**
     * Get the tenant that owns the reminder.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the appointment for this reminder.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pending reminders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
