<?php

namespace App\Modules\Marketing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deal extends Model
{
    use BelongsToTenant;

    protected $table = 'marketing_deals';

    protected $fillable = [
        'tenant_id',
        'contact_id',
        'campaign_id',
        'title',
        'description',
        'stage',
        'value',
        'currency',
        'probability',
        'expected_close_date',
        'actual_close_date',
        'lost_reason',
        'properties',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
        'properties' => 'array',
    ];

    /**
     * Get the tenant that owns the deal.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the contact associated with this deal.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    /**
     * Get the campaign associated with this deal.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    /**
     * Get all activities for this deal.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(DealActivity::class, 'deal_id');
    }

    /**
     * Scope by stage.
     */
    public function scopeByStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }

    /**
     * Scope active deals (not closed).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('stage', ['prospect', 'qualified', 'proposal', 'negotiation']);
    }

    /**
     * Scope won deals.
     */
    public function scopeWon($query)
    {
        return $query->where('stage', 'closed_won');
    }

    /**
     * Scope lost deals.
     */
    public function scopeLost($query)
    {
        return $query->where('stage', 'closed_lost');
    }

    /**
     * Calculate expected revenue.
     */
    public function getExpectedRevenueAttribute(): float
    {
        return ($this->value ?? 0) * ($this->probability ?? 0) / 100;
    }

    /**
     * Check if deal is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->expected_close_date && 
               $this->expected_close_date < now() && 
               !in_array($this->stage, ['closed_won', 'closed_lost']);
    }
}
