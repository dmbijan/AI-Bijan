<?php

namespace App\Modules\Logistics\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Logistics\Models\StockMove;
use App\Modules\Logistics\Models\Shipment;

class Warehouse extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'logistics_warehouses';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'type',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'phone',
        'email',
        'manager_name',
        'manager_phone',
        'is_active',
        'is_default',
        'latitude',
        'longitude',
        'settings',
        'notes',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
    ];

    /**
     * Warehouse types
     */
    const TYPE_MAIN = 'main';
    const TYPE_WAREHOUSE = 'warehouse';
    const TYPE_STORE = 'store';
    const TYPE_DISTRIBUTION_CENTER = 'distribution_center';
    const TYPE_POPUP = 'popup';

    /**
     * Relationship: Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Relationship: Stock moves
     */
    public function stockMoves()
    {
        return $this->hasMany(StockMove::class, 'warehouse_id');
    }

    /**
     * Relationship: Outbound shipments
     */
    public function outboundShipments()
    {
        return $this->hasMany(Shipment::class, 'outbound_warehouse_id');
    }

    /**
     * Relationship: Inbound shipments
     */
    public function inboundShipments()
    {
        return $this->hasMany(Shipment::class, 'inbound_warehouse_id');
    }

    /**
     * Scope: Active warehouses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Default warehouse
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Get total stock quantity in warehouse
     */
    public function getTotalStockAttribute()
    {
        return StockMove::where('warehouse_id', $this->id)
            ->sum('quantity');
    }

    /**
     * Check if warehouse can accept more stock
     */
    public function hasCapacity($quantity = 1)
    {
        $maxCapacity = $this->settings['max_capacity'] ?? 999999;
        return ($this->total_stock + $quantity) <= $maxCapacity;
    }

    /**
     * Format full address
     */
    public function getFullAddressAttribute()
    {
        return implode(', ', array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country,
        ]));
    }
}
