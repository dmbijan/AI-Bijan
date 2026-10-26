<?php

namespace App\Modules\BrainHub\Services;

use App\Modules\Analytics\Models\MetricValue;
use Illuminate\Support\Facades\DB;

class ForecastService
{
    /**
     * Generate forecast for a metric
     */
    public function generateForecast(int $metricId, int $horizonDays = 30, string $model = null): array
    {
        $metric = \App\Modules\Analytics\Models\Metric::find($metricId);
        if (!$metric) {
            return ['success' => false, 'message' => 'Metric not found'];
        }

        // Get historical data
        $history = MetricValue::where('metric_id', $metricId)
            ->orderBy('calculated_at')
            ->get();

        if ($history->count() < 14) {
            return [
                'success' => false,
                'message' => 'Insufficient data for forecasting (minimum 14 data points required)',
            ];
        }

        // Select model
        $model = $model ?? config('brainhub.forecasting.default_model', 'exponential_smoothing');

        // Generate forecast based on model
        switch ($model) {
            case 'linear_regression':
                $forecast = $this->linearRegressionForecast($history, $horizonDays);
                break;
            case 'moving_average':
                $forecast = $this->movingAverageForecast($history, $horizonDays);
                break;
            case 'exponential_smoothing':
                $forecast = $this->exponentialSmoothingForecast($history, $horizonDays);
                break;
            default:
                $forecast = $this->exponentialSmoothingForecast($history, $horizonDays);
        }

        // Save forecast to database
        $forecastRecord = $this->saveForecast($metric, $model, $horizonDays, $forecast);

        return array_merge([
            'success' => true,
            'forecast_id' => $forecastRecord->id,
            'model_used' => $model,
        ], $forecast);
    }

    /**
     * Linear Regression Forecast
     */
    protected function linearRegressionForecast($history, int $horizonDays): array
    {
        $values = $history->pluck('value')->toArray();
        $n = count($values);

        $sumX = array_sum(range(1, $n));
        $sumY = array_sum($values);
        $sumXY = 0;
        $sumXX = 0;

        foreach (range(1, $n) as $x) {
            $sumXY += $x * $values[$x - 1];
            $sumXX += $x * $x;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        // Calculate R-squared
        $meanY = $sumY / $n;
        $ssTot = 0;
        $ssRes = 0;

        foreach ($values as $i => $y) {
            $ssTot += pow($y - $meanY, 2);
            $predicted = $slope * ($i + 1) + $intercept;
            $ssRes += pow($y - $predicted, 2);
        }

        $rSquared = $ssTot > 0 ? 1 - ($ssRes / $ssTot) : 0;

        // Generate future values
        $lastDate = $history->last()->calculated_at;
        $forecasts = [];

        for ($i = 1; $i <= $horizonDays; $i++) {
            $x = $n + $i;
            $predicted = $slope * $x + $intercept;

            // Confidence decreases with distance
            $confidence = max(0.5, 1 - ($i / ($horizonDays * 2)));

            $forecasts[] = [
                'date' => $lastDate->addDays($i)->format('Y-m-d'),
                'predicted_value' => round($predicted, 2),
                'confidence_lower' => round($predicted - 1.96 * sqrt($ssRes / $n), 2),
                'confidence_upper' => round($predicted + 1.96 * sqrt($ssRes / $n), 2),
                'confidence_level' => round($confidence, 2),
            ];
        }

        return [
            'predictions' => $forecasts,
            'r_squared' => round($rSquared, 4),
            'slope' => round($slope, 4),
            'intercept' => round($intercept, 4),
            'last_actual_value' => end($values),
        ];
    }

    /**
     * Moving Average Forecast
     */
    protected function movingAverageForecast($history, int $horizonDays): array
    {
        $values = $history->pluck('value')->toArray();
        $windowSize = min(7, floor(count($values) / 3));

        if ($windowSize < 2) {
            $windowSize = 2;
        }

        // Calculate moving average
        $maValues = [];
        for ($i = 0; $i <= count($values) - $windowSize; $i++) {
            $maValues[array_sum(array_slice($values, $i, $windowSize)) / $windowSize] = true;
        }

        $lastMA = array_sum(array_slice($values, -$windowSize)) / $windowSize;
        $stdDev = sqrt(array_reduce(array_slice($values, -$windowSize), fn($carry, $v) => $carry + pow($v - $lastMA, 2), 0) / $windowSize);

        $lastDate = $history->last()->calculated_at;
        $forecasts = [];

        for ($i = 1; $i <= $horizonDays; $i++) {
            $forecasts[] = [
                'date' => $lastDate->addDays($i)->format('Y-m-d'),
                'predicted_value' => round($lastMA, 2),
                'confidence_lower' => round($lastMA - 1.96 * $stdDev, 2),
                'confidence_upper' => round($lastMA + 1.96 * $stdDev, 2),
                'confidence_level' => round(max(0.5, 1 - ($i / ($horizonDays * 2))), 2),
            ];
        }

        return [
            'predictions' => $forecasts,
            'moving_average' => round($lastMA, 2),
            'window_size' => $windowSize,
            'std_deviation' => round($stdDev, 2),
            'last_actual_value' => end($values),
        ];
    }

    /**
     * Exponential Smoothing Forecast (Holt's Double)
     */
    protected function exponentialSmoothingForecast($history, int $horizonDays): array
    {
        $values = $history->pluck('value')->toArray();
        $alpha = 0.3; // Level smoothing
        $beta = 0.1;  // Trend smoothing

        $level = $values[0];
        $trend = count($values) > 1 ? $values[1] - $values[0] : 0;

        // Fit the model
        for ($i = 1; $i < count($values) - 1; $i++) {
            $newLevel = $alpha * $values[$i] + (1 - $alpha) * ($level + $trend);
            $newTrend = $beta * ($newLevel - $level) + (1 - $beta) * $trend;
            $level = $newLevel;
            $trend = $newTrend;
        }

        $lastDate = $history->last()->calculated_at;
        $forecasts = [];

        for ($h = 1; $h <= $horizonDays; $h++) {
            $predicted = $level + $h * $trend;
            $forecasts[] = [
                'date' => $lastDate->addDays($h)->format('Y-m-d'),
                'predicted_value' => round($predicted, 2),
                'confidence_lower' => round($predicted - 2 * abs($trend * $h), 2),
                'confidence_upper' => round($predicted + 2 * abs($trend * $h), 2),
                'confidence_level' => round(max(0.5, 1 - ($h / ($horizonDays * 2))), 2),
            ];
        }

        return [
            'predictions' => $forecasts,
            'level' => round($level, 2),
            'trend' => round($trend, 2),
            'alpha' => $alpha,
            'beta' => $beta,
            'last_actual_value' => end($values),
        ];
    }

    /**
     * Save forecast to database
     */
    protected function saveForecast(\App\Modules\Analytics\Models\Metric $metric, string $model, int $horizonDays, array $forecast): \App\Modules\BrainHub\Models\Forecast
    {
        $latestPrediction = $forecast['predictions'][0] ?? null;

        return \App\Modules\BrainHub\Models\Forecast::create([
            'tenant_id' => $metric->tenant_id,
            'metric_id' => $metric->id,
            'source_module' => $metric->data_source ?? 'analytics',
            'title' => sprintf('Forecast: %s (%d days)', $metric->name, $horizonDays),
            'description' => sprintf(
                'Predicted value for %s over next %d days using %s model.',
                $metric->name,
                $horizonDays,
                $model
            ),
            'model_used' => $model,
            'forecast_horizon_days' => $horizonDays,
            'predicted_value' => $latestPrediction['predicted_value'] ?? null,
            'confidence_lower' => $latestPrediction['confidence_lower'] ?? null,
            'confidence_upper' => $latestPrediction['confidence_upper'] ?? null,
            'confidence_level' => $latestPrediction['confidence_level'] ?? null,
            'accuracy_score' => $forecast['r_squared'] ?? null,
            'metadata' => [
                'model_params' => array_filter($forecast, fn($k) => !in_array($k, ['predictions']), ARRAY_FILTER_USE_KEY),
                'predictions_count' => count($forecast['predictions'] ?? []),
            ],
        ]);
    }

    /**
     * Get all forecasts for a metric
     */
    public function getForecasts(int $metricId, int $limit = 10): array
    {
        return \App\Modules\BrainHub\Models\Forecast::where('metric_id', $metricId)
            ->latest()
            ->take($limit)
            ->get()
            ->toArray();
    }

    /**
     * Batch forecast multiple metrics
     */
    public function batchForecast(array $metricIds, int $horizonDays = 30): array
    {
        $results = [];

        foreach ($metricIds as $metricId) {
            try {
                $results[$metricId] = $this->generateForecast($metricId, $horizonDays);
            } catch (\Exception $e) {
                $results[$metricId] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Evaluate forecast accuracy
     */
    public function evaluateAccuracy(int $forecastId): array
    {
        $forecast = \App\Modules\BrainHub\Models\Forecast::find($forecastId);

        if (!$forecast || is_null($forecast->actual_value)) {
            return ['success' => false, 'message' => 'No actual value confirmed yet'];
        }

        $mape = abs($forecast->predicted_value - $forecast->actual_value) / abs($forecast->predicted_value) * 100;

        return [
            'success' => true,
            'forecast_id' => $forecastId,
            'predicted' => $forecast->predicted_value,
            'actual' => $forecast->actual_value,
            'mape' => round($mape, 2),
            'accuracy' => round(100 - $mape, 2),
            'within_confidence' => $forecast->actual_value >= $forecast->confidence_lower
                && $forecast->actual_value <= $forecast->confidence_upper,
        ];
    }
}
