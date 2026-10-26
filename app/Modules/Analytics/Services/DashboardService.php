<?php

namespace App\Modules\Analytics\Services;

use App\Modules\Analytics\Models\Dashboard;
use App\Modules\Analytics\Models\DashboardWidget;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get all dashboards for tenant
     */
    public function getDashboards(int $tenantId, int $userId = null): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Dashboard::where('tenant_id', $tenantId)
            ->with(['widgets', 'createdBy'])
            ->latest();

        // Include shared dashboards if user is logged in
        if ($userId) {
            $query->orWhereJsonContains('shared_with', $userId);
        }

        return $query->paginate(20);
    }

    /**
     * Get single dashboard with widgets
     */
    public function getDashboard(int $tenantId, int $dashboardId): ?array
    {
        $dashboard = Dashboard::where('tenant_id', $tenantId)
            ->with(['widgets', 'createdBy'])
            ->firstWhere('id', $dashboardId);

        if (!$dashboard) {
            return null;
        }

        return [
            'dashboard' => $dashboard->toArray(),
            'widgets' => $dashboard->widgets->map(function ($widget) {
                return $this->getWidgetData($widget);
            })->toArray(),
        ];
    }

    /**
     * Get default dashboard
     */
    public function getDefaultDashboard(int $tenantId): ?array
    {
        $dashboard = Dashboard::where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->with(['widgets'])
            ->first();

        if (!$dashboard) {
            return null;
        }

        return [
            'dashboard' => $dashboard->toArray(),
            'widgets' => $dashboard->widgets->map(function ($widget) {
                return $this->getWidgetData($widget);
            })->toArray(),
        ];
    }

    /**
     * Create dashboard
     */
    public function createDashboard(int $tenantId, array $data, int $userId): Dashboard
    {
        return Dashboard::create(array_merge($data, [
            'tenant_id' => $tenantId,
            'created_by' => $userId,
            'is_default' => false,
        ]));
    }

    /**
     * Update dashboard
     */
    public function updateDashboard(int $tenantId, int $dashboardId, array $data): bool
    {
        return Dashboard::where('tenant_id', $tenantId)
            ->where('id', $dashboardId)
            ->update($data);
    }

    /**
     * Delete dashboard
     */
    public function deleteDashboard(int $tenantId, int $dashboardId): bool
    {
        return Dashboard::where('tenant_id', $tenantId)
            ->where('id', $dashboardId)
            ->delete();
    }

    /**
     * Add widget to dashboard
     */
    public function addWidget(int $tenantId, int $dashboardId, array $widgetData): DashboardWidget
    {
        return DashboardWidget::create(array_merge($widgetData, [
            'tenant_id' => $tenantId,
            'dashboard_id' => $dashboardId,
        ]));
    }

    /**
     * Update widget
     */
    public function updateWidget(int $tenantId, int $widgetId, array $data): bool
    {
        return DashboardWidget::where('tenant_id', $tenantId)
            ->where('id', $widgetId)
            ->update($data);
    }

    /**
     * Delete widget
     */
    public function deleteWidget(int $tenantId, int $widgetId): bool
    {
        return DashboardWidget::where('tenant_id', $tenantId)
            ->where('id', $widgetId)
            ->delete();
    }

    /**
     * Reorder widgets
     */
    public function reorderWidgets(int $tenantId, int $dashboardId, array $widgetOrder): bool
    {
        DB::beginTransaction();
        try {
            foreach ($widgetOrder as $index => $widgetId) {
                DashboardWidget::where('tenant_id', $tenantId)
                    ->where('id', $widgetId)
                    ->update([
                        'position_x' => $index,
                    ]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Share dashboard with users
     */
    public function shareDashboard(int $tenantId, int $dashboardId, array $userIds): bool
    {
        $dashboard = Dashboard::where('tenant_id', $tenantId)
            ->where('id', $dashboardId)
            ->first();

        if (!$dashboard) {
            return false;
        }

        $existingShares = $dashboard->shared_with ?? [];
        $mergedShares = array_unique(array_merge($existingShares, $userIds));

        return $dashboard->update(['shared_with' => $mergedShares]);
    }

    /**
     * Get widget data with current values
     */
    protected function getWidgetData(DashboardWidget $widget): array
    {
        $data = $widget->toArray();

        if ($widget->metric_id) {
            $metricService = app(MetricService::class);
            $trendData = $metricService->getTrendData($widget->tenant_id, $widget->metric_id, 30);
            $data['current_value'] = $trendData ? end($trendData)['value'] : null;
            $data['trend_data'] = $trendData;
        }

        return $data;
    }

    /**
     * Refresh all widget data for dashboard
     */
    public function refreshDashboard(int $tenantId, int $dashboardId): array
    {
        $dashboard = $this->getDashboard($tenantId, $dashboardId);

        if (!$dashboard) {
            return ['success' => false, 'message' => 'Dashboard not found'];
        }

        $refreshedWidgets = [];

        foreach ($dashboard['widgets'] as $widget) {
            if (isset($widget['metric_id'])) {
                $metricService = app(MetricService::class);
                $trendData = $metricService->getTrendData($tenantId, $widget['metric_id'], 30);
                $refreshedWidgets[] = [
                    'widget_id' => $widget['id'],
                    'current_value' => $trendData ? end($trendData)['value'] : null,
                    'trend_data' => $trendData,
                ];
            }
        }

        return [
            'success' => true,
            'widgets' => $refreshedWidgets,
        ];
    }
}
