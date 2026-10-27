<?php

namespace App\Modules\Assets\Models;


use App\Models\Concerns\BelongsToTenant;use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceRequest extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'maintenance_requests';

    protected $fillable = [
        'tenant_id',
        'asset_id',
        'requested_by',
        'assigned_to',
        'type',
        'priority',
        'status',
        'title',
        'description',
        'resolution',
        'scheduled_date',
        'completed_date',
        'estimated_cost',
        'actual_cost',
        'downtime_hours',
        'vendor_id',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'downtime_hours' => 'decimal:2',
    ];

    /**
     * Get the asset this maintenance request is for.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user who requested this maintenance.
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'requested_by');
    }

    /**
     * Get the user assigned to this maintenance.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    /**
     * Get the vendor responsible for this maintenance.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Models\Client::class, 'vendor_id');
    }

    /**
     * Get parts/materials used in this maintenance.
     */
    public function parts(): HasMany
    {
        return $this->hasMany(MaintenancePart::class);
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
     * Scope by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if maintenance is overdue.
     */
    public function isOverdue(): bool
    {
        if (in_array($this->status, ['completed', 'cancelled'])) {
            return false;
        }

        if ($this->scheduled_date && now()->gt($this->scheduled_date)) {
            return true;
        }

        return false;
    }

    /**
     * Get total cost including parts.
     */
    public function getTotalCostAttribute(): float
    {
        $partsTotal = $this->parts()->sum('total_cost');
        return (float) ($this->actual_cost + $partsTotal);
    }
}
