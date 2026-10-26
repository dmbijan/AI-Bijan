<?php

namespace App\Modules\SCM\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'suppliers';

    protected $fillable = [
        'tenant_id',
        'supplier_code',
        'company_name',
        'company_name_bm',
        'ssm_number',
        'gst_number',
        'sst_number',
        'tax_identification',
        'contact_person',
        'contact_email',
        'contact_phone',
        'address',
        'city',
        'state',
        'postcode',
        'category',
        'status',
        'payment_terms_days',
        'currency',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'withholding_tax_rate',
        'rating',
        'on_time_delivery_count',
        'late_delivery_count',
        'quality_issues_count',
        'is_approved',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'payment_terms_days' => 'integer',
        'withholding_tax_rate' => 'decimal:2',
        'rating' => 'decimal:1',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Contacts for this supplier
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(SupplierContact::class);
    }

    /**
     * Purchase orders from this supplier
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Approver user
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Scope: active suppliers only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Calculate on-time delivery percentage
     */
    public function getOnTimeDeliveryPercentageAttribute(): float
    {
        $total = $this->on_time_delivery_count + $this->late_delivery_count;
        if ($total === 0) {
            return 0;
        }
        return round(($this->on_time_delivery_count / $total) * 100, 2);
    }

    /**
     * Generate next supplier code
     */
    public static function generateSupplierCode(): string
    {
        $year = date('Y');
        $prefix = "SUP-{$year}-";
        
        $lastSupplier = self::where('supplier_code', 'like', "{$prefix}%")
            ->orderBy('supplier_code', 'desc')
            ->first();
        
        if ($lastSupplier) {
            $lastNumber = (int) substr($lastSupplier->supplier_code, -5);
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }
        
        return "{$prefix}{$newNumber}";
    }
}
