<?php

namespace App\Modules\BrainHub\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BrainHub\Services\ForecastService;
use Illuminate\Http\Request;

class ForecastController extends Controller
{
    protected $forecastService;

    public function __construct(ForecastService $forecastService)
    {
        $this->forecastService = $forecastService;
    }

    /**
     * Generate forecast for metric
     */
    public function generate(Request $request, int $metricId)
    {
        $validated = $request->validate([
            'horizon_days' => 'integer|min:1|max:365',
            'model' => 'string|in:linear_regression,moving_average,exponential_smoothing,arima',
        ]);

        $result = $this->forecastService->generateForecast(
            $metricId,
            $validated['horizon_days'] ?? 30,
            $validated['model'] ?? null
        );

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }

    /**
     * Get forecasts for metric
     */
    public function show(Request $request, int $metricId)
    {
        $limit = $request->input('limit', 10);
        $forecasts = $this->forecastService->getForecasts($metricId, $limit);

        return response()->json($forecasts);
    }

    /**
     * Batch forecast multiple metrics
     */
    public function batchGenerate(Request $request)
    {
        $validated = $request->validate([
            'metric_ids' => 'required|array',
            'metric_ids.*' => 'integer',
            'horizon_days' => 'integer|min:1|max:365',
        ]);

        $result = $this->forecastService->batchForecast(
            $validated['metric_ids'],
            $validated['horizon_days'] ?? 30
        );

        return response()->json($result);
    }

    /**
     * Evaluate forecast accuracy
     */
    public function evaluate(Request $request, int $forecastId)
    {
        $result = $this->forecastService->evaluateAccuracy($forecastId);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result);
    }
}
