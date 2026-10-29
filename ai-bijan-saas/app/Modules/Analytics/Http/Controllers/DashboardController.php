<?php

namespace App\Modules\Analytics\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use App\Http\Controllers\Controller;
use App\Modules\Analytics\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * List all dashboards
     */
    public function index(Request $request)
    {
        return response()->json($this->dashboardService->getDashboards(
            app(TenantContext::class)->id(),
            $request->user()->id
        ));
    }

    /**
     * Get default dashboard
     */
    public function getDefault(Request $request)
    {
        $dashboard = $this->dashboardService->getDefaultDashboard(app(TenantContext::class)->id());

        if (!$dashboard) {
            return response()->json(['message' => 'Default dashboard not found'], 404);
        }

        return response()->json($dashboard);
    }

    /**
     * Show single dashboard
     */
    public function show(Request $request, int $dashboardId)
    {
        $dashboard = $this->dashboardService->getDashboard(
            app(TenantContext::class)->id(),
            $dashboardId
        );

        if (!$dashboard) {
            return response()->json(['message' => 'Dashboard not found'], 404);
        }

        return response()->json($dashboard);
    }

    /**
     * Create dashboard
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:analytics_dashboards,code',
            'description' => 'nullable|string',
            'type' => 'string|in:main,executive,operational,custom',
            'layout' => 'string|in:grid,list,flex',
            'widgets' => 'nullable|array',
        ]);

        $dashboard = $this->dashboardService->createDashboard(
            app(TenantContext::class)->id(),
            $validated,
            $request->user()->id
        );

        return response()->json($dashboard, 201);
    }

    /**
     * Update dashboard
     */
    public function update(Request $request, int $dashboardId)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $this->dashboardService->updateDashboard(
            app(TenantContext::class)->id(),
            $dashboardId,
            $validated
        );

        return response()->json(['message' => 'Dashboard updated successfully']);
    }

    /**
     * Delete dashboard
     */
    public function destroy(Request $request, int $dashboardId)
    {
        $this->dashboardService->deleteDashboard(
            app(TenantContext::class)->id(),
            $dashboardId
        );

        return response()->json(['message' => 'Dashboard deleted successfully']);
    }

    /**
     * Add widget to dashboard
     */
    public function addWidget(Request $request, int $dashboardId)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:kpi_card,line_chart,bar_chart,pie_chart,table,gauge,trend,metric',
            'metric_id' => 'nullable|integer|exists:analytics_metrics,id',
            'chart_config' => 'nullable|array',
            'filters' => 'nullable|array',
            'position_x' => 'integer',
            'position_y' => 'integer',
            'width' => 'integer|min:1|max:12',
            'height' => 'integer|min:1',
        ]);

        $widget = $this->dashboardService->addWidget(
            app(TenantContext::class)->id(),
            $dashboardId,
            $validated
        );

        return response()->json($widget, 201);
    }

    /**
     * Update widget
     */
    public function updateWidget(Request $request, int $widgetId)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:kpi_card,line_chart,bar_chart,pie_chart,table,gauge,trend,metric',
            'metric_id' => 'nullable|integer',
            'chart_config' => 'nullable|array',
            'filters' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $this->dashboardService->updateWidget(
            app(TenantContext::class)->id(),
            $widgetId,
            $validated
        );

        return response()->json(['message' => 'Widget updated successfully']);
    }

    /**
     * Delete widget
     */
    public function deleteWidget(Request $request, int $widgetId)
    {
        $this->dashboardService->deleteWidget(
            app(TenantContext::class)->id(),
            $widgetId
        );

        return response()->json(['message' => 'Widget deleted successfully']);
    }

    /**
     * Share dashboard
     */
    public function share(Request $request, int $dashboardId)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'integer',
        ]);

        $this->dashboardService->shareDashboard(
            app(TenantContext::class)->id(),
            $dashboardId,
            $validated['user_ids']
        );

        return response()->json(['message' => 'Dashboard shared successfully']);
    }

    /**
     * Refresh dashboard data
     */
    public function refresh(Request $request, int $dashboardId)
    {
        $result = $this->dashboardService->refreshDashboard(
            app(TenantContext::class)->id(),
            $dashboardId
        );

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }
}
