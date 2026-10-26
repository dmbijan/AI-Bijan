<?php

namespace App\Modules\SCM\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'tenant_id',
        'po_number',
        'supplier_id',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'status',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'currency',
        'payment_terms_days',
        'payment_due_date',
        'shipping_method',
        'shipping_address',
        'tracking_number',
        'internal_notes',
        'terms_conditions',
        'requested_by',
        'approved_by',
        'approved_at',
        'journal_entry_id',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'payment_due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Line items for this PO
     */
    public function lines(): HasMany
    {
        return $this->hasMany(PurchaseOrderLine::class);
    }

    /**
     * Approval requests for this PO
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(PurchaseOrderApproval::class);
    }

    /**
     * Goods received notes for this PO
     */
    public function grns(): HasMany
    {
        return $this->hasMany(GoodsReceivedNote::class);
    }

    /**
     * Supplier who provides these goods
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Requester user
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    /**
     * Approver user
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Scope: approved POs only
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope: pending approval
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    /**
     * Generate next PO number
     */
    public static function generatePONumber(): string
    {
        $year = date('Y');
        $prefix = "PO-{$year}-";
        
        $lastPO = self::where('po_number', 'like', "{$prefix}%")
            ->orderBy('po_number', 'desc')
            ->first();
        
        if ($lastPO) {
            $lastNumber = (int) substr($lastPO->po_number, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }
        
        return "{$prefix}{$newNumber}";
    }

    /**
     * Calculate total quantity ordered
     */
    public function getTotalQuantityOrderedAttribute(): int
    {
        return $this->lines()->sum('quantity_ordered');
    }

    /**
     * Check if PO is fully received
     */
    public function isFullyReceived(): bool
    {
        foreach ($this->lines as $line) {
            if ($line->quantity_received < $line->quantity_ordered) {
                return false;
            }
        }
        return true;
    }
}
