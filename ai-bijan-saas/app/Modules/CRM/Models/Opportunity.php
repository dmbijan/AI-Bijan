<?php

namespace App\Modules\CRM\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    use BelongsToTenant;

    protected $table = 'opportunities';
    
    protected $fillable = [
        'tenant_id',
        'opportunity_number',
        'customer_id',
        'title',
        'description',
        'estimated_value',
        'probability',
        'stage',
        'expected_close_date',
        'actual_close_date',
        'forecast_category',
        'competitors',
        'win_strategy',
        'loss_reason',
        'next_action',
        'next_action_date',
        'owner_id',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'probability' => 'decimal:2',
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
        'next_action_date' => 'date',
    ];

    /**
     * Get tenant that owns the opportunity.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get associated customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get opportunity owner.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_id');
    }

    /**
     * Get quotations for this opportunity.
     */
    public function quotations()
    {
        return $this->hasMany(\App\Modules\CRM\Models\Quotation::class);
    }

    /**
     * Generate opportunity number automatically.
     */
    public static function generateOpportunityNumber(): string
    {
        $year = date('Y');
        $lastNumber = static::whereYear('created_at', $year)
            ->max('id');
        $number = ($lastNumber ?? 0) + 1;
        
        return sprintf('OPP-%s-%04d', $year, $number);
    }

    /**
     * Scope by stage.
     */
    public function scopeByStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }

    /**
     * Scope active opportunities (not closed).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('stage', ['prospecting', 'qualification', 'needs_analysis', 'proposal', 'negotiation']);
    }

    /**
     * Check if opportunity is won.
     */
    public function isWon(): bool
    {
        return $this->stage === 'closed_won';
    }

    /**
     * Check if opportunity is lost.
     */
    public function isLost(): bool
    {
        return $this->stage === 'closed_lost';
    }

    /**
     * Check if opportunity is closed.
     */
    public function isClosed(): bool
    {
        return in_array($this->stage, ['closed_won', 'closed_lost']);
    }

    /**
     * Calculate expected revenue.
     */
    public function expectedRevenue(): float
    {
        return $this->estimated_value * ($this->probability / 100);
    }
}
