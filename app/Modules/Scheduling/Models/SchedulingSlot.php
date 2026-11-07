<?php

namespace App\Modules\Scheduling\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Slot masa tersedia bagi satu pautan penjadualan.
 */
class SchedulingSlot extends Model
{
    use BelongsToTenant;

    protected $table = 'scheduling_slots';

    protected $fillable = [
        'tenant_id',
        'scheduling_link_id',
        'provider_id',
        'starts_at',
        'ends_at',
        'is_available',
        'is_booked',
        'booking_id',
        'blocked_reason',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_available' => 'boolean',
        'is_booked' => 'boolean',
    ];

    protected $attributes = [
        'is_available' => true,
        'is_booked' => false,
    ];

    public function schedulingLink(): BelongsTo
    {
        return $this->belongsTo(SchedulingLink::class, 'scheduling_link_id');
    }
}
