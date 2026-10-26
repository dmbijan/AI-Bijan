<?php

namespace App\Modules\Logistics\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Logistics\Models\Warehouse;

class Shipment extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'logistics_shipments';

    protected $fillable = [
        'tenant_id',
        'shipment_number',
        'order_type', // sales, purchase, transfer
        'order_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'recipient_name',
        'recipient_phone',
        'recipient_email',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',
        'courier',
        'service_type',
        'tracking_number',
        'consignment_note',
        'weight_kg',
        'length_cm',
        'width_cm',
        'height_cm',
        'volume_cm3',
        'package_type',
        'number_of_packages',
        'declared_value',
        'currency',
        'shipping_cost',
        'insurance_cost',
        'cost_allocation_method',
        'status', // created, label_generated, picked_up, in_transit, out_for_delivery, delivered, failed, returned, cancelled
        'estimated_delivery',
        'delivered_at',
        'signed_by',
        'proof_of_delivery',
        'notes',
        'labels',
        'customs_documents',
    ];

    protected $casts = [
        'weight_kg' => 'decimal:2',
        'length_cm' => 'decimal:2',
        'width_cm' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'volume_cm3' => 'decimal:2',
        'declared_value' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'insurance_cost' => 'decimal:2',
        'estimated_delivery' => 'datetime',
        'delivered_at' => 'datetime',
        'labels' => 'array',
        'customs_documents' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Order types
     */
    const ORDER_SALES = 'sales';
    const ORDER_PURCHASE = 'purchase';
    const ORDER_TRANSFER = 'transfer';

    /**
     * Package types
     */
    const PACKAGE_PARCEL = 'parcel';
    const PACKAGE_ENVELOPE = 'envelope';
    const PACKAGE_PALLET = 'pallet';
    const PACKAGE_CUSTOM = 'custom';

    /**
     * Status workflow
     */
    const STATUS_CREATED = 'created';
    const STATUS_LABEL_GENERATED = 'label_generated';
    const STATUS_PICKED_UP = 'picked_up';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_FAILED = 'failed';
    const STATUS_RETURNED = 'returned';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Relationship: Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Relationship: From warehouse
     */
    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Relationship: To warehouse (for transfers/purchases)
     */
    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Scope: Active shipments (not delivered/cancelled)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_CREATED,
            self::STATUS_LABEL_GENERATED,
            self::STATUS_PICKED_UP,
            self::STATUS_IN_TRANSIT,
            self::STATUS_OUT_FOR_DELIVERY,
        ]);
    }

    /**
     * Scope: Delivered shipments
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    /**
     * Scope: By courier
     */
    public function scopeByCourier($query, $courier)
    {
        return $query->where('courier', $courier);
    }

    /**
     * Scope: By date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    /**
     * Generate shipment number
     */
    public static function generateShipmentNumber()
    {
        $prefix = config('logistics.shipment.tracking_prefix', 'SHIP');
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return sprintf('%s-%s-%05d', $prefix, $year, $count);
    }

    /**
     * Calculate volume in cm³
     */
    public function calculateVolume()
    {
        if ($this->length_cm && $this->width_cm && $this->height_cm) {
            $this->volume_cm3 = $this->length_cm * $this->width_cm * $this->height_cm;
        }
        return $this;
    }

    /**
     * Get shipping cost per kg
     */
    public function getCostPerKg()
    {
        return $this->weight_kg > 0 ? ($this->shipping_cost / $this->weight_kg) : 0;
    }

    /**
     * Check if shipment is in transit
     */
    public function isInTransit()
    {
        return in_array($this->status, [
            self::STATUS_PICKED_UP,
            self::STATUS_IN_TRANSIT,
            self::STATUS_OUT_FOR_DELIVERY,
        ]);
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_CREATED => 'Created',
            self::STATUS_LABEL_GENERATED => 'Label Generated',
            self::STATUS_PICKED_UP => 'Picked Up',
            self::STATUS_IN_TRANSIT => 'In Transit',
            self::STATUS_OUT_FOR_DELIVERY => 'Out for Delivery',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_FAILED => 'Delivery Failed',
            self::STATUS_RETURNED => 'Returned',
            self::STATUS_CANCELLED => 'Cancelled',
            default => $this->status,
        };
    }

    /**
     * Get order type label
     */
    public function getOrderTypeLabelAttribute()
    {
        return match($this->order_type) {
            self::ORDER_SALES => 'Sales Shipment',
            self::ORDER_PURCHASE => 'Purchase Shipment',
            self::ORDER_TRANSFER => 'Internal Transfer',
            default => $this->order_type,
        };
    }
}
