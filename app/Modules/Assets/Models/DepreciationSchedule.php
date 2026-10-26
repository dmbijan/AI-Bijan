<?php

namespace App\Modules\Assets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepreciationSchedule extends Model
{
    use HasFactory;

    protected $table = 'depreciation_schedules';

    protected $fillable = [
        'asset_id',
        'tenant_id',
        'year',
        'period',
        'opening_value',
        'depreciation_amount',
        'closing_value',
        'calculated_at',
        'is_posted',
    ];

    protected $casts = [
        'opening_value' => 'decimal:2',
        'depreciation_amount' => 'decimal:2',
        'closing_value' => 'decimal:2',
        'calculated_at' => 'datetime',
        'is_posted' => 'boolean',
    ];

    /**
     * Get the asset this schedule belongs to.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Scope by tenant.
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope by year.
     */
    public function scopeByYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope posted schedules only.
     */
    public function scopePosted($query)
    {
        return $query->where('is_posted', true);
    }
}
