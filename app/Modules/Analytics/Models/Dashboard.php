<?php

namespace App\Modules\Analytics\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dashboard extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'analytics_dashboards';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'type', // main, executive, operational, custom
        'layout', // grid, list, flex
        'widgets',
        'is_active',
        'is_default',
        'shared_with',
        'created_by',
        'metadata',
    ];

    protected $casts = [
        'layout' => 'array',
        'widgets' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'shared_with' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Dashboard types
     */
    const TYPE_MAIN = 'main';
    const TYPE_EXECUTIVE = 'executive';
    const TYPE_OPERATIONAL = 'operational';
    const TYPE_CUSTOM = 'custom';

    /**
     * Layouts
     */
    const LAYOUT_GRID = 'grid';
    const LAYOUT_LIST = 'list';
    const LAYOUT_FLEX = 'flex';

    /**
     * Relationship: Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Relationship: Created by user
     */
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Relationship: Widgets
     */
    public function widgets()
    {
        return $this->hasMany(DashboardWidget::class);
    }

    /**
     * Scope: Active dashboards
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Default dashboard
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope: Shared with user
     */
    public function scopeSharedWith($query, int $userId)
    {
        return $query->whereJsonContains('shared_with', $userId);
    }

    /**
     * Check if dashboard is shared with user
     */
    public function isSharedWith(int $userId): bool
    {
        return in_array($userId, $this->shared_with ?? []);
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_MAIN => 'Main Dashboard',
            self::TYPE_EXECUTIVE => 'Executive Dashboard',
            self::TYPE_OPERATIONAL => 'Operational Dashboard',
            self::TYPE_CUSTOM => 'Custom Dashboard',
            default => $this->type,
        };
    }
}
