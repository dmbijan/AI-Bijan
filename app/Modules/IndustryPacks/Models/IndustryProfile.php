<?php

namespace App\Modules\IndustryPacks\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class IndustryProfile extends Model
{
    use BelongsToTenant;

    protected $table = 'industry_profiles';

    protected $fillable = [
        'tenant_id',
        'industry_key',
        'name',
        'description',
        'enabled_modules',
        'dashboard_layout',
        'settings',
        'status',
    ];

    protected $casts = [
        'enabled_modules' => 'array',
        'dashboard_layout' => 'array',
        'settings' => 'array',
    ];

    /**
     * Get all available industries from config
     */
    public static function getAvailableIndustries()
    {
        return config('industry_packs.industries', []);
    }

    /**
     * Get industry by key
     */
    public static function getByKey($key)
    {
        $industries = self::getAvailableIndustries();
        return $industries[$key] ?? null;
    }

    /**
     * Get dashboard layout for this industry
     */
    public function getDashboardLayout()
    {
        $dashboards = config('industry_packs.dashboards', []);
        return $dashboards[$this->industry_key] ?? null;
    }

    /**
     * Get pre-configured metrics for this industry
     */
    public function getMetrics()
    {
        $metrics = config('industry_packs.metrics', []);
        return $metrics[$this->industry_key] ?? [];
    }

    /**
     * Get default cost components for this industry
     */
    public function getCostComponents()
    {
        $costs = config('industry_packs.cost_components', []);
        return $costs[$this->industry_key] ?? [];
    }

    /**
     * Get template documents for this industry
     */
    public function getTemplates()
    {
        $templates = config('industry_packs.templates', []);
        return $templates[$this->industry_key] ?? [];
    }

    /**
     * Get workflows for this industry
     */
    public function getWorkflows()
    {
        $workflows = config('industry_packs.workflows', []);
        return $workflows[$this->industry_key] ?? [];
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
