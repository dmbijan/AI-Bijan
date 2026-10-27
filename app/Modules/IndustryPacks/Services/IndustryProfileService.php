<?php

namespace App\Modules\IndustryPacks\Services;

use App\Modules\IndustryPacks\Models\IndustryProfile;
use Illuminate\Support\Facades\DB;

class IndustryProfileService
{
    /**
     * Get all available industries
     */
    public function getAvailableIndustries()
    {
        return IndustryProfile::getAvailableIndustries();
    }

    /**
     * Apply industry pack to tenant
     */
    public function applyIndustryPack($tenantId, $industryKey)
    {
        $industries = IndustryProfile::getAvailableIndustries();

        if (!isset($industries[$industryKey])) {
            throw new \InvalidArgumentException("Industry '{$industryKey}' is not valid.");
        }

        $industry = $industries[$industryKey];

        DB::beginTransaction();
        try {
            // Create or update industry profile
            $profile = IndustryProfile::updateOrCreate(
                ['tenant_id' => $tenantId],
                [
                    'industry_key' => $industryKey,
                    'name' => $industry['name'],
                    'description' => $industry['description'],
                    'enabled_modules' => $industry['enabled_modules'],
                    'dashboard_layout' => $industry['default_dashboard'],
                    'status' => 'active',
                ]
            );

            // Apply metrics to B-Analytics
            $this->applyMetrics($tenantId, $industryKey);

            // Apply cost components to B-COGS
            $this->applyCostComponents($tenantId, $industryKey);

            // Apply document templates to B-Sign & Docs
            $this->applyTemplates($tenantId, $industryKey);

            // Enable modules for tenant
            $this->enableModules($tenantId, $industry['enabled_modules']);

            DB::commit();

            return [
                'success' => true,
                'profile' => $profile,
                'message' => "Industry pack '{$industry['name']}' applied successfully.",
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Apply pre-configured metrics to Analytics module
     */
    protected function applyMetrics($tenantId, $industryKey)
    {
        $metrics = config('industry_packs.metrics', [])[$industryKey] ?? [];

        foreach ($metrics as $metricConfig) {
            // This would integrate with B-Analytics Metric model
            // For now, just log what would be created
            \Log::info("Analytics metric would be created: {$metricConfig['name']}");
        }
    }

    /**
     * Apply default cost components to COGS module
     */
    protected function applyCostComponents($tenantId, $industryKey)
    {
        $components = config('industry_packs.cost_components', [])[$industryKey] ?? [];

        foreach ($components as $componentName) {
            // This would integrate with B-COGS CostComponent model
            \Log::info("COGS cost component would be created: {$componentName}");
        }
    }

    /**
     * Apply document templates to Documents module
     */
    protected function applyTemplates($tenantId, $industryKey)
    {
        $templates = config('industry_packs.templates', [])[$industryKey] ?? [];

        foreach ($templates as $templateName) {
            // This would integrate with B-Sign & Docs template system
            \Log::info("Document template would be enabled: {$templateName}");
        }
    }

    /**
     * Enable modules for tenant based on industry
     */
    protected function enableModules($tenantId, $modules)
    {
        foreach ($modules as $module) {
            // This would update tenant's enabled modules list
            \Log::info("Module would be enabled for tenant: {$module}");
        }
    }

    /**
     * Get tenant's current industry profile
     */
    public function getTenantProfile($tenantId)
    {
        return IndustryProfile::where('tenant_id', $tenantId)->first();
    }

    /**
     * Change industry pack for tenant
     */
    public function changeIndustryPack($tenantId, $newIndustryKey)
    {
        // First deactivate current
        $current = $this->getTenantProfile($tenantId);
        if ($current) {
            $current->update(['status' => 'archived']);
        }

        // Apply new
        return $this->applyIndustryPack($tenantId, $newIndustryKey);
    }

    /**
     * Get industry recommendations based on business type
     */
    public function recommendIndustry($businessKeywords)
    {
        $industries = $this->getAvailableIndustries();
        $scores = [];

        foreach ($industries as $key => $industry) {
            $score = 0;
            $keywords = $industry['description'];

            // Simple keyword matching (could be enhanced with NLP)
            if (stripos($keywords, 'retail') !== false && stripos($businessKeywords, 'shop') !== false) {
                $score += 3;
            }
            if (stripos($keywords, 'manufacturing') !== false && stripos($businessKeywords, 'production') !== false) {
                $score += 3;
            }
            if (stripos($keywords, 'services') !== false && stripos($businessKeywords, 'consulting') !== false) {
                $score += 3;
            }
            if (stripos($keywords, 'healthcare') !== false && stripos($businessKeywords, 'medical') !== false) {
                $score += 3;
            }
            if (stripos($keywords, 'food') !== false && stripos($businessKeywords, 'restaurant') !== false) {
                $score += 3;
            }
            if (stripos($keywords, 'construction') !== false && stripos($businessKeywords, 'building') !== false) {
                $score += 3;
            }
            if (stripos($keywords, 'logistics') !== false && stripos($businessKeywords, 'shipping') !== false) {
                $score += 3;
            }
            if (stripos($keywords, 'education') !== false && stripos($businessKeywords, 'school') !== false) {
                $score += 3;
            }
            if (stripos($keywords, 'real estate') !== false && stripos($businessKeywords, 'property') !== false) {
                $score += 3;
            }
            if (stripos($keywords, 'agriculture') !== false && stripos($businessKeywords, 'farm') !== false) {
                $score += 3;
            }

            if ($score > 0) {
                $scores[$key] = $score;
            }
        }

        rsort($scores);

        return [
            'recommendations' => array_keys($scores),
            'top_recommendation' => !empty($scores) ? array_keys($scores)[0] : null,
        ];
    }
}
