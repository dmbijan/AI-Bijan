<?php

namespace App\Modules\Logistics\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Modules\Logistics\Models\Warehouse;
use App\Modules\Logistics\Models\Shipment;

class StockMove extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'logistics_stock_moves';

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'product_id',
        'lot_number',
        'serial_number',
        'quantity',
        'unit_cost',
        'total_value',
        'move_type', // incoming, outgoing, transfer, adjustment, return
        'reference_type', // purchase_order, sales_order, transfer, adjustment, return
        'reference_id',
        'from_warehouse_id',
        'to_warehouse_id',
        'batch_number',
        'expiry_date',
        'moved_by',
        'approved_by',
        'notes',
        'status', // pending, completed, cancelled
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:4',
        'total_value' => 'decimal:2',
        'expiry_date' => 'date',
        'moved_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Move types
     */
    const TYPE_INCOMING = 'incoming';
    const TYPE_OUTGOING = 'outgoing';
    const TYPE_TRANSFER = 'transfer';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_RETURN = 'return';

    /**
     * Status
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Relationship: Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Relationship: Warehouse
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Relationship: Product
     */
    public function product()
    {
        return $this->belongsTo(\App\Models\Item::class, 'product_id');
    }

    /**
     * Relationship: From warehouse (for transfers)
     */
    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Relationship: To warehouse (for transfers)
     */
    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Relationship: Moved by user
     */
    public function movedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'moved_by');
    }

    /**
     * Relationship: Approved by user
     */
    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Relationship: Related shipment
     */
    public function shipment()
    {
        return $this->belongsTo(Shipment::class, 'reference_id')
            ->where('reference_type', 'stock_move');
    }

    /**
     * Scope: Incoming moves
     */
    public function scopeIncoming($query)
    {
        return $query->where('move_type', self::TYPE_INCOMING);
    }

    /**
     * Scope: Outgoing moves
     */
    public function scopeOutgoing($query)
    {
        return $query->where('move_type', self::TYPE_OUTGOING);
    }

    /**
     * Scope: Transfers
     */
    public function scopeTransfers($query)
    {
        return $query->where('move_type', self::TYPE_TRANSFER);
    }

    /**
     * Scope: Completed moves
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: By warehouse
     */
    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope: By date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('moved_at', [$from, $to]);
    }

    /**
     * Calculate total value
     */
    public function calculateTotalValue()
    {
        $this->total_value = abs($this->quantity) * ($this->unit_cost ?? 0);
        return $this;
    }

    /**
     * Get move type label
     */
    public function getTypeLabelAttribute()
    {
        return match($this->move_type) {
            self::TYPE_INCOMING => 'Incoming/Receive',
            self::TYPE_OUTGOING => 'Outgoing/Dispatch',
            self::TYPE_TRANSFER => 'Transfer',
            self::TYPE_ADJUSTMENT => 'Adjustment',
            self::TYPE_RETURN => 'Return',
            default => $this->move_type,
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => $this->status,
        };
    }
}
