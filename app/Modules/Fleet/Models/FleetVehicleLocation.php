<?php

namespace App\Modules\Fleet\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Lokasi terkini satu kenderaan.
 */
class FleetVehicleLocation extends Model
{
    use BelongsToTenant;

    protected $table = 'fleet_vehicle_locations';

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'latitude',
        'longitude',
        'speed_kmh',
        'heading',
        'battery_percent',
        'ignition_on',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'speed_kmh' => 'decimal:2',
        'heading' => 'decimal:2',
        'battery_percent' => 'decimal:2',
        'ignition_on' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(FleetVehicle::class, 'vehicle_id');
    }

    /**
     * Adakah lokasi ini segar (dalam 15 minit)?
     */
    public function segar(): bool
    {
        return $this->recorded_at !== null
            && $this->recorded_at->gt(now()->subMinutes(15));
    }
}
