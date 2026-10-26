<?php

namespace App\Modules\Fleet\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Amaran armada — breakdown, bahan api rendah, tingkah laku memandu.
 */
class FleetAlert extends Model
{
    use BelongsToTenant;

    protected $table = 'fleet_alerts';

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'driver_id',
        'type',
        'severity',
        'title',
        'message',
        'context',
        'triggered_at',
        'acknowledged_at',
        'acknowledged_by',
        'resolved_at',
    ];

    protected $casts = [
        'context' => 'array',
        'triggered_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected $attributes = [
        'severity' => 'medium',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(FleetVehicle::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(FleetDriver::class, 'driver_id');
    }

    public function belumSelesai(): bool
    {
        return $this->resolved_at === null;
    }
}
