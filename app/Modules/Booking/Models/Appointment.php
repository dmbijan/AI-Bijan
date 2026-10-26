<?php

namespace App\Modules\Booking\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use BelongsToTenant;

    protected $table = 'bookings_appointments';

    protected $fillable = [
        'tenant_id',
        'appointment_no',
        'service_id',
        'provider_id',
        'slot_id',
        'customer_id',
        'user_id',
        'status',
        'start_time',
        'end_time',
        'actual_start_time',
        'actual_end_time',
        'notes',
        'internal_notes',
        'reminder_sent',
        'reminder_sent_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'rescheduled_from',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'reminder_sent' => 'boolean',
        'reminder_sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'rescheduled_from' => 'integer',
    ];

    /**
     * Get the tenant that owns the appointment.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the service for this appointment.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the provider for this appointment.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id');
    }

    /**
     * Get the slot for this appointment.
     */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(BookingSlot::class, 'slot_id');
    }

    /**
     * Get the customer for this appointment.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Models\Customer::class, 'customer_id');
    }

    /**
     * Get the user who created the appointment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Get all reminders for this appointment.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(BookingReminder::class, 'appointment_id');
    }

    /**
     * Generate appointment number.
     */
    public static function generateAppointmentNo(): string
    {
        $year = now()->format('Y');
        $lastNo = static::whereYear('created_at', $year)
            ->orderBy('appointment_no', 'desc')
            ->value('appointment_no');
        
        $number = $lastNo ? intval(substr($lastNo, -6)) + 1 : 1;
        return 'BK-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by date range.
     */
    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('start_time', [$startDate, $endDate]);
    }

    /**
     * Check if appointment is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_time->isFuture() && in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Check if appointment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->start_time->isPast() && !in_array($this->status, ['completed', 'cancelled', 'no_show']);
    }
}
