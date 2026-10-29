<?php

namespace App\Modules\Analytics\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use App\Http\Controllers\Controller;
use App\Modules\Analytics\Services\MetricService;
use App\Modules\Analytics\Models\Metric;
use Illuminate\Http\Request;

class MetricController extends Controller
{
    protected $metricService;

    public function __construct(MetricService $metricService)
    {
        $this->metricService = $metricService;
    }

    /**
     * List all metrics
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'type', 'active']);
        
        return response()->json($this->metricService->getMetrics(
            app(TenantContext::class)->id(),
            $filters
        ));
    }

    /**
     * Show single metric
     */
    public function show(Request $request, int $metricId)
    {
        $metric = $this->metricService->getMetric(app(TenantContext::class)->id(), $metricId);

        if (!$metric) {
            return response()->json(['message' => 'Metric not found'], 404);
        }

        return response()->json($metric);
    }

    /**
     * Calculate metric
     */
    public function calculate(Request $request, int $metricId)
    {
        $result = $this->metricService->calculateMetric(
            app(TenantContext::class)->id(),
            $metricId
        );

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Get historical data
     */
    public function history(Request $request, int $metricId)
    {
        $days = $request->input('days', 30);
        $history = $this->metricService->getTrendData(
            app(TenantContext::class)->id(),
            $metricId,
            $days
        );

        return response()->json($history);
    }

    /**
     * Create or update metric
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:analytics_metrics,code',
            'category' => 'required|string|in:finance,sales,operations,hr,marketing,customer,inventory,custom',
            'type' => 'required|string|in:kpi,metric,ratio,rate,score',
            'description' => 'nullable|string',
            'formula' => 'nullable|array',
            'data_source' => 'nullable|string',
            'aggregation_type' => 'string|in:sum,avg,count,min,max,ratio,cumulative',
            'unit' => 'nullable|string',
            'decimal_places' => 'integer|min:0|max:10',
            'target_value' => 'nullable|numeric',
            'threshold_warning' => 'nullable|numeric',
            'threshold_critical' => 'nullable|numeric',
            'refresh_interval_minutes' => 'integer|min:1',
        ]);

        $metric = $this->metricService->upsertMetric(
            app(TenantContext::class)->id(),
            array_merge($validated, ['code' => strtoupper($validated['code'])])
        );

        return response()->json($metric, 201);
    }

    /**
     * Update metric
     */
    public function update(Request $request, int $metricId)
    {
        $metric = Metric::where('tenant_id', app(TenantContext::class)->id())
            ->find($metricId);

        if (!$metric) {
            return response()->json(['message' => 'Metric not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|in:finance,sales,operations,hr,marketing,customer,inventory,custom',
            'description' => 'nullable|string',
            'target_value' => 'nullable|numeric',
            'threshold_warning' => 'nullable|numeric',
            'threshold_critical' => 'nullable|numeric',
            'is_active' => 'boolean',
            'refresh_interval_minutes' => 'integer|min:1',
        ]);

        $metric->update($validated);

        return response()->json($metric);
    }

    /**
     * Delete metric
     */
    public function destroy(Request $request, int $metricId)
    {
        $this->metricService->deleteMetric(app(TenantContext::class)->id(), $metricId);

        return response()->json(['message' => 'Metric deleted successfully']);
    }

    /**
     * Batch calculate metrics
     */
    public function batchCalculate(Request $request)
    {
        $validated = $request->validate([
            'metric_ids' => 'required|array',
            'metric_ids.*' => 'integer',
        ]);

        $result = $this->metricService->triggerBatchCalculation($validated['metric_ids']);

        return response()->json($result);
    }
}
