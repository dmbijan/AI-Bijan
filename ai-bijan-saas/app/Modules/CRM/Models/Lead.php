<?php

namespace App\Modules\CRM\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use BelongsToTenant;

    protected $table = 'leads';
    
    protected $fillable = [
        'tenant_id',
        'lead_number',
        'source',
        'name',
        'email',
        'phone',
        'company',
        'position',
        'requirements',
        'estimated_value',
        'expected_close_date',
        'status',
        'priority',
        'assigned_to',
        'opportunity_id',
        'converted_at',
        'loss_reason',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'expected_close_date' => 'date',
        'converted_at' => 'datetime',
    ];

    /**
     * Get tenant that owns the lead.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get assigned salesperson.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    /**
     * Get converted opportunity.
     */
    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class, 'opportunity_id');
    }

    /**
     * Generate lead number automatically.
     */
    public static function generateLeadNumber(): string
    {
        $year = date('Y');
        $lastNumber = static::whereYear('created_at', $year)
            ->max('id');
        $number = ($lastNumber ?? 0) + 1;
        
        return sprintf('LEAD-%s-%04d', $year, $number);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope unconverted leads.
     */
    public function scopeUnconverted($query)
    {
        return $query->whereNull('converted_at');
    }

    /**
     * Check if lead is converted.
     */
    public function isConverted(): bool
    {
        return !is_null($this->converted_at);
    }

    /**
     * Check if lead is won.
     */
    public function isWon(): bool
    {
        return $this->status === 'won';
    }

    /**
     * Check if lead is lost.
     */
    public function isLost(): bool
    {
        return $this->status === 'lost';
    }
}
