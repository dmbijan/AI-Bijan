<?php

namespace App\Modules\Assets\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'assets';

    protected $fillable = [
        'tenant_id',
        'category_id',
        'asset_no',
        'name',
        'serial_number',
        'barcode',
        'description',
        'status',
        'purchase_price',
        'purchase_date',
        'vendor_id',
        'assigned_to',
        'location',
        'warranty_provider',
        'warranty_start',
        'warranty_end',
        'salvage_value',
        'useful_life_years',
        'depreciation_method',
        'current_book_value',
        'total_depreciation',
        'disposed_date',
        'disposal_value',
        'disposal_reason',
        'created_by',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'purchase_date' => 'date',
        'salvage_value' => 'decimal:2',
        'useful_life_years' => 'integer',
        'current_book_value' => 'decimal:2',
        'total_depreciation' => 'decimal:2',
        'disposed_date' => 'date',
        'disposal_value' => 'decimal:2',
        'warranty_start' => 'date',
        'warranty_end' => 'date',
    ];

    /**
     * Get the category that owns this asset.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    /**
     * Get the vendor/supplier of this asset.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Models\Client::class, 'vendor_id');
    }

    /**
     * Get the user this asset is assigned to.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    /**
     * Get the creator of this asset record.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get depreciation schedules for this asset.
     */
    public function depreciationSchedules(): HasMany
    {
        return $this->hasMany(DepreciationSchedule::class);
    }

    /**
     * Get maintenance requests for this asset.
     */
    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    /**
     * Get transfer history for this asset.
     */
    public function transfers(): HasMany
    {
        return $this->hasMany(AssetTransfer::class);
    }

    /**
     * Get documents for this asset.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(AssetDocument::class);
    }

    /**
     * Get disposals for this asset.
     */
    public function disposals(): HasMany
    {
        return $this->hasMany(AssetDisposal::class);
    }

    /**
     * Calculate current book value based on depreciation.
     */
    public function calculateBookValue(): float
    {
        $depreciableAmount = $this->purchase_price - $this->salvage_value;
        
        if ($this->depreciation_method === 'straight_line') {
            $annualDepreciation = $depreciableAmount / max($this->useful_life_years, 1);
            
            // Calculate years elapsed
            $purchaseDate = new \DateTime($this->purchase_date);
            $now = new \DateTime();
            $yearsElapsed = $purchaseDate->diff($now)->y;
            
            $totalDepreciation = min($annualDepreciation * $yearsElapsed, $depreciableAmount);
            
            $this->total_depreciation = $totalDepreciation;
            $this->current_book_value = $this->purchase_price - $totalDepreciation;
        }
        
        return (float) $this->current_book_value ?? $this->purchase_price;
    }

    /**
     * Check if asset is under warranty.
     */
    public function isUnderWarranty(): bool
    {
        if (!$this->warranty_end) {
            return false;
        }
        
        return now() <= $this->warranty_end;
    }

    /**
     * Check if asset is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'installed']);
    }

    /**
     * Check if asset needs maintenance soon.
     */
    public function needsMaintenanceSoon(int $daysAhead = 30): bool
    {
        $lastMaintenance = $this->maintenanceRequests()
            ->where('status', 'completed')
            ->orderBy('completed_date', 'desc')
            ->first();
        
        if (!$lastMaintenance || !$lastMaintenance->completed_date) {
            return true;
        }
        
        $lastDate = new \DateTime($lastMaintenance->completed_date);
        $nextDue = (clone $lastDate)->modify("+$daysAhead days");
        
        return now() >= $lastDate && now() <= $nextDue;
    }

    /**
     * Scope by tenant.
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope active assets only.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'installed']);
    }

    /**
     * Generate unique asset number.
     */
    public static function generateAssetNo(): string
    {
        $year = now()->format('Y');
        $lastNo = static::whereYear('created_at', $year)
            ->orderBy('asset_no', 'desc')
            ->value('asset_no');
        
        $number = $lastNo ? intval(substr($lastNo, -4)) + 1 : 1;
        
        return 'AST-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
