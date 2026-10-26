<?php

namespace App\Modules\Fleet\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Sejarah lokasi untuk pengesanan tingkah laku memandu.
 */
class FleetLocationHistory extends Model
{
    use BelongsToTenant;

    protected $table = 'fleet_location_histories';

    protected $fillable = [
        'tenant_id',
        'vehicle_id',
        'latitude',
        'longitude',
        'speed_kmh',
        'harsh_braking',
        'harsh_acceleration',
        'harsh_cornering',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'speed_kmh' => 'decimal:2',
        'harsh_braking' => 'boolean',
        'harsh_acceleration' => 'boolean',
        'harsh_cornering' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    protected $attributes = [
        'harsh_braking' => false,
        'harsh_acceleration' => false,
        'harsh_cornering' => false,
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(FleetVehicle::class, 'vehicle_id');
    }
}
