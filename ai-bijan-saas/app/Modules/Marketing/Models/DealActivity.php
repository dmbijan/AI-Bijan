<?php

namespace App\Modules\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealActivity extends Model
{
    protected $table = 'marketing_deal_activities';

    protected $fillable = [
        'tenant_id',
        'deal_id',
        'type',
        'description',
        'value',
        'currency',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the activity.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the deal this activity belongs to.
     */
    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class, 'deal_id');
    }

    /**
     * Scope by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Order by created date descending.
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
