<?php

namespace App\Modules\Marketing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use BelongsToTenant;

    protected $table = 'marketing_campaigns';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'type',
        'status',
        'goal',
        'budget',
        'currency',
        'start_date',
        'end_date',
        'segments',
        'templates',
        'channels',
        'settings',
        'performance_data',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'segments' => 'array',
        'templates' => 'array',
        'channels' => 'array',
        'settings' => 'array',
        'performance_data' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'budget' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the campaign.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the creator of this campaign.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get all sequences for this campaign.
     */
    public function sequences(): HasMany
    {
        return $this->hasMany(Sequence::class, 'campaign_id');
    }

    /**
     * Get all vouchers for this campaign.
     */
    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class, 'campaign_id');
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
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
     * Scope active campaigns.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['running', 'scheduled']);
    }

    /**
     * Check if campaign is currently running.
     */
    public function isRunning(): bool
    {
        return in_array($this->status, ['running', 'scheduled'])
            && ($this->start_date <= now() || !$this->start_date)
            && ($this->end_date >= now() || !$this->end_date);
    }

    /**
     * Get performance summary.
     */
    public function getPerformanceSummary(): array
    {
        return $this->performance_data ?? [];
    }
}
