<?php

namespace App\Modules\IndustryPacks\Services;

use Illuminate\Support\Facades\DB;

class DashboardLayoutService
{
    /**
     * Get dashboard layout for an industry
     */
    public function getDashboardLayout($industryKey)
    {
        $dashboards = config('industry_packs.dashboards', []);

        if (!isset($dashboards[$industryKey])) {
            throw new \InvalidArgumentException("Dashboard layout not found for industry: {$industryKey}");
        }

        return $dashboards[$industryKey];
    }

    /**
     * Get widget configuration for a specific widget type
     */
    public function getWidgetConfig($industryKey, $widgetType = null)
    {
        $layout = $this->getDashboardLayout($industryKey);

        if ($widgetType) {
            foreach ($layout['widgets'] as $widget) {
                if ($widget['type'] === $widgetType) {
                    return $widget;
                }
            }
            return null;
        }

        return $layout['widgets'];
    }

    /**
     * Generate dashboard HTML structure
     */
    public function generateDashboardHTML($industryKey, $tenantId = null)
    {
        $layout = $this->getDashboardLayout($industryKey);
        $widgets = $layout['widgets'];

        $html = '<div class="dashboard-container" data-industry="' . $industryKey . '">';

        foreach ($widgets as $index => $widget) {
            $html .= '<div class="widget widget-' . $widget['type'] . '" data-metric="' . $widget['metric'] . '">';
            $html .= '<h3>' . htmlspecialchars($widget['title']) . '</h3>';
            $html .= '<div class="widget-content" id="widget-' . $index . '"></div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Get recommended widgets based on role
     */
    public function getWidgetsForRole($industryKey, $role)
    {
        $layout = $this->getDashboardLayout($industryKey);
        $widgets = $layout['widgets'];

        // Admin sees all widgets
        if ($role === 'admin') {
            return $widgets;
        }

        // Filter widgets by role (simplified logic)
        $filtered = [];
        foreach ($widgets as $widget) {
            // KPI cards are visible to everyone
            if ($widget['type'] === 'kpi_card') {
                $filtered[] = $widget;
            }
            // Managers see charts too
            if (in_array($role, ['manager', 'admin']) && in_array($widget['type'], ['line_chart', 'bar_chart'])) {
                $filtered[] = $widget;
            }
        }

        return $filtered;
    }

    /**
     * Customize dashboard layout for tenant
     */
    public function customizeDashboard($tenantId, $industryKey, $customWidgets)
    {
        // Save custom layout to tenant settings
        // This would integrate with B-Virtual Office or tenant settings
        \Log::info("Custom dashboard for tenant {$tenantId}: " . json_encode($customWidgets));

        return [
            'success' => true,
            'message' => 'Dashboard layout customized successfully.',
        ];
    }

    /**
     * Reset dashboard to default industry layout
     */
    public function resetToDefault($tenantId, $industryKey)
    {
        $profile = IndustryProfile::where('tenant_id', $tenantId)->first();

        if (!$profile) {
            throw new \InvalidArgumentException("No industry profile found for tenant: {$tenantId}");
        }

        $layout = $this->getDashboardLayout($industryKey);

        return [
            'success' => true,
            'layout' => $layout,
            'message' => 'Dashboard reset to default layout.',
        ];
    }
}
