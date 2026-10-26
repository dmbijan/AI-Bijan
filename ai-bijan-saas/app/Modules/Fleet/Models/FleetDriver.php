<?php

namespace App\Modules\Fleet\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pemandu armada.
 */
class FleetDriver extends Model
{
    use BelongsToTenant;

    protected $table = 'fleet_drivers';

    protected $fillable = [
        'tenant_id',
        'name',
        'phone',
        'license_no',
        'license_class',
        'license_expiry',
        'status',
        'safety_score',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'license_expiry' => 'date',
        'safety_score' => 'decimal:2',
    ];

    protected $attributes = [
        'status' => 'active',
        'safety_score' => 100,
    ];

    public function vehicles(): HasMany
    {
        return $this->hasMany(FleetVehicle::class, 'assigned_driver_id');
    }

    /**
     * Adakah lesen masih sah?
     */
    public function lesenSah(): bool
    {
        return $this->license_expiry === null || $this->license_expiry->isFuture();
    }
}
