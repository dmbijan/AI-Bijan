<?php

namespace App\Modules\Fleet\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Perjalanan (trip) kenderaan.
 */
class FleetTrip extends Model
{
    use BelongsToTenant;

    protected $table = 'fleet_trips';

    public const STATUS_PLANNED = 'planned';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'driver_id',
        'shipment_id',
        'reference_no',
        'status',
        'started_at',
        'ended_at',
        'distance_km',
        'duration_minutes',
        'route_points',
        'meta',
    ];

    protected $casts = [
        'route_points' => 'array',
        'meta' => 'array',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'distance_km' => 'decimal:2',
        'duration_minutes' => 'integer',
    ];

    protected $attributes = [
        'status' => 'planned',
        'distance_km' => 0,
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(FleetVehicle::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(FleetDriver::class, 'driver_id');
    }

    public function dalamPerjalanan(): bool
    {
        return $this->status === 'in_progress';
    }
}
