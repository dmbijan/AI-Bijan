<?php

namespace App\Modules\Fleet\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kenderaan dalam armada.
 */
class FleetVehicle extends Model
{
    use BelongsToTenant;

    protected $table = 'fleet_vehicles';

    protected $fillable = [
        'tenant_id',
        'plate_number',
        'make',
        'model',
        'color',
        'type',
        'odometer_km',
        'fuel_type',
        'fuel_level_percent',
        'status',
        'assigned_driver_id',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'odometer_km' => 'decimal:2',
        'fuel_level_percent' => 'decimal:2',
    ];

    protected $attributes = [
        'type' => 'van',
        'odometer_km' => 0,
        'status' => 'active',
        'fuel_level_percent' => 100,
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(FleetDriver::class, 'assigned_driver_id');
    }

    public function location(): HasMany
    {
        return $this->hasMany(FleetVehicleLocation::class, 'vehicle_id');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(FleetTrip::class, 'vehicle_id');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(FleetAlert::class, 'vehicle_id');
    }

    public function isAktif(): bool
    {
        return $this->status === 'active';
    }
}
