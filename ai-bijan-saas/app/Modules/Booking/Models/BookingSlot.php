<?php

namespace App\Modules\Booking\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingSlot extends Model
{
    use BelongsToTenant;

    protected $table = 'booking_slots';

    protected $fillable = [
        'tenant_id',
        'service_id',
        'provider_id',
        'slot_date',
        'start_time',
        'end_time',
        'status',
        'capacity',
        'booked_count',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'slot_date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'capacity' => 'integer',
        'booked_count' => 'integer',
    ];

    /**
     * Get the tenant that owns the slot.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the service for this slot.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the provider for this slot.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id');
    }

    /**
     * Scope by date.
     */
    public function scopeOnDate($query, string $date)
    {
        return $query->where('slot_date', $date);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if slot is available.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->booked_count < $this->capacity;
    }

    /**
     * Increment booked count.
     */
    public function incrementBooked(): void
    {
        $this->increment('booked_count');
    }

    /**
     * Decrement booked count.
     */
    public function decrementBooked(): void
    {
        $this->decrement('booked_count');
    }
}
