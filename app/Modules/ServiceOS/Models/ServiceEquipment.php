<?php

namespace App\Modules\ServiceOS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Peralatan perkhidmatan (kamera, pelamin, sound system, dll).
 */
class ServiceEquipment extends Model
{
    use BelongsToTenant;

    protected $table = 'service_equipments';

    protected $fillable = [
        'tenant_id',
        'asset_id',
        'code',
        'name',
        'category',
        'description',
        'quantity_total',
        'replacement_value',
        'requires_maintenance',
        'is_active',
    ];

    protected $casts = [
        'quantity_total' => 'integer',
        'replacement_value' => 'decimal:2',
        'requires_maintenance' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function reservations(): HasMany
    {
        return $this->hasMany(ServiceEquipmentReservation::class, 'service_equipment_id');
    }

    /** Kuantiti yang masih bebas untuk tempoh tertentu. */
    public function bakiUntukTempoh(\DateTimeInterface $dari, \DateTimeInterface $hingga): int
    {
        $digunakan = $this->reservations()
            ->whereIn('status', [ServiceEquipmentReservation::STATUS_RESERVED, ServiceEquipmentReservation::STATUS_CHECKED_OUT])
            ->where('reserved_from', '<', $hingga)
            ->where('reserved_to', '>', $dari)
            ->sum('quantity');

        return max(0, (int) $this->quantity_total - (int) $digunakan);
    }
}
