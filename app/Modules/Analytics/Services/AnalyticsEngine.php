<?php

namespace App\Modules\Analytics\Services;

use App\Modules\Analytics\Models\Metric;
use Illuminate\Support\Facades\DB;

class AnalyticsEngine
{
    /**
     * Aggregate data from multiple modules
     */
    public function aggregateFromModules(array $modules, array $filters = []): array
    {
        $results = [];

        foreach ($modules as $module) {
            try {
                $results[$module] = $this->fetchModuleData($module, $filters);
            } catch (\Exception $e) {
                $results[$module] = [
                    'error' => $e->getMessage(),
                    'status' => 'failed',
                ];
            }
        }

        return $results;
    }

    /**
     * Fetch data from a specific module
     */
    protected function fetchModuleData(string $module, array $filters): array
    {
        // This would contain logic to query different module tables
        // For now, returning placeholder structure
        return [
            'module' => $module,
            'data' => [],
            'count' => 0,
            'status' => 'success',
        ];
    }

    /**
     * Analyze trends across metrics
     */
    public function analyzeTrends(int $tenantId, array $metricIds, int $days = 30): array
    {
        $trends = [];

        foreach ($metricIds as $metricId) {
            $metric = Metric::find($metricId);
            if (!$metric || $metric->tenant_id !== $tenantId) {
                continue;
            }

            $trendData = MetricService::getTrendData($metricId, $days);
            
            if (count($trendData) < 2) {
                continue;
            }

            $trend = $this->calculateTrend($trendData);

            $trends[] = [
                'metric_id' => $metricId,
                'metric_name' => $metric->name,
                'direction' => $trend['direction'],
                'strength' => $trend['strength'],
                'percentage_change' => $trend['percentage_change'],
            ];
        }

        return $trends;
    }

    /**
     * Calculate trend from data points
     */
    protected function calculateTrend(array $dataPoints): array
    {
        if (count($dataPoints) < 2) {
            return ['direction' => 'stable', 'strength' => 0, 'percentage_change' => 0];
        }

        $firstValue = $dataPoints[0]['value'];
        $lastValue = end($dataPoints)['value'];

        if ($firstValue == 0) {
            return ['direction' => 'stable', 'strength' => 0, 'percentage_change' => 0];
        }

        $percentageChange = (($lastValue - $firstValue) / abs($firstValue)) * 100;

        // Calculate strength (0-1) based on consistency
        $strength = $this->calculateConsistency($dataPoints);

        return [
            'direction' => $percentageChange > 0 ? 'up' : ($percentageChange < 0 ? 'down' : 'stable'),
            'strength' => $strength,
            'percentage_change' => round($percentageChange, 2),
        ];
    }

    /**
     * Calculate trend consistency
     */
    protected function calculateConsistency(array $dataPoints): float
    {
        if (count($dataPoints) < 3) {
            return 0.5;
        }

        $directions = [];
        for ($i = 1; $i < count($dataPoints); $i++) {
            $prev = $dataPoints[$i - 1]['value'];
            $curr = $dataPoints[$i]['value'];
            $directions[] = $curr > $prev ? 1 : ($curr < $prev ? -1 : 0);
        }

        if (empty($directions)) {
            return 0.5;
        }

        $positive = count(array_filter($directions, fn($d) => $d === 1));
        $negative = count(array_filter($directions, fn($d) => $d === -1));

        return max($positive, $negative) / count($directions);
    }

    /**
     * Detect anomalies in metric data
     */
    public function detectAnomalies(int $metricId, int $windowSize = 30): array
    {
        $metric = Metric::find($metricId);
        if (!$metric) {
            return [];
        }

        $values = MetricService::getTrendData($metricId, $windowSize);
        $anomalies = [];

        if (count($values) < 3) {
            return $anomalies;
        }

        $mean = array_sum(array_column($values, 'value')) / count($values);
        $variance = array_reduce($values, fn($carry, $item) => $carry + pow($item['value'] - $mean, 2), 0) / count($values);
        $stdDev = sqrt($variance);

        foreach ($values as $point) {
            if (abs($point['value'] - $mean) > 2 * $stdDev) {
                $anomalies[] = [
                    'date' => $point['date'],
                    'value' => $point['value'],
                    'deviation' => abs($point['value'] - $mean) / $stdDev,
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Forecast future values using simple linear regression
     */
    public function forecast(int $metricId, int $periods = 7): array
    {
        $metric = Metric::find($metricId);
        if (!$metric) {
            return [];
        }

        $values = MetricService::getTrendData($metricId, 90);
        
        if (count($values) < 7) {
            return [];
        }

        $forecasted = $this->linearRegressionForecast($values, $periods);

        return $forecasted;
    }

    /**
     * Simple linear regression forecast
     */
    protected function linearRegressionForecast(array $data, int $periods): array
    {
        $n = count($data);
        $xValues = range(1, $n);
        $yValues = array_column($data, 'value');

        $sumX = array_sum($xValues);
        $sumY = array_sum($yValues);
        $sumXY = array_sum(array_map(fn($x, $y) => $x * $y, $xValues, $yValues));
        $sumXX = array_sum(array_map(fn($x) => $x * $x, $xValues));

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumXX - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        $forecast = [];
        for ($i = 1; $i <= $periods; $i++) {
            $forecast[] = [
                'period' => $n + $i,
                'predicted_value' => round($intercept + $slope * ($n + $i), 2),
            ];
        }

        return $forecast;
    }

    /**
     * Generate insights from metrics
     */
    public function generateInsights(int $tenantId, array $metricIds): array
    {
        $insights = [];

        foreach ($metricIds as $metricId) {
            $metric = Metric::where('id', $metricId)
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$metric || !$metric->last_value) {
                continue;
            }

            // Check thresholds
            if ($metric->threshold_critical && $metric->last_value <= $metric->threshold_critical) {
                $insights[] = [
                    'type' => 'critical',
                    'metric' => $metric->name,
                    'message' => sprintf(
                        '%s has reached critical level: %.2f (threshold: %.2f)',
                        $metric->name,
                        $metric->last_value,
                        $metric->threshold_critical
                    ),
                    'severity' => 'high',
                ];
            } elseif ($metric->threshold_warning && $metric->last_value <= $metric->threshold_warning) {
                $insights[] = [
                    'type' => 'warning',
                    'metric' => $metric->name,
                    'message' => sprintf(
                        '%s is below warning level: %.2f (threshold: %.2f)',
                        $metric->name,
                        $metric->last_value,
                        $metric->threshold_warning
                    ),
                    'severity' => 'medium',
                ];
            }

            // Check significant changes
            if ($metric->previous_value && abs($metric->trend_percentage) > 20) {
                $insights[] = [
                    'type' => 'significant_change',
                    'metric' => $metric->name,
                    'message' => sprintf(
                        '%s changed by %.2f%% compared to previous period',
                        $metric->name,
                        $metric->trend_percentage
                    ),
                    'severity' => 'low',
                ];
            }
        }

        return $insights;
    }

    /**
     * Cross-module correlation analysis
     */
    public function analyzeCorrelation(int $tenantId, array $metricIds): array
    {
        if (count($metricIds) < 2) {
            return [];
        }

        $correlations = [];

        for ($i = 0; $i < count($metricIds); $i++) {
            for ($j = $i + 1; $j < count($metricIds); $j++) {
                $corr = $this->calculateCorrelation($metricIds[$i], $metricIds[$j]);
                if ($corr !== null) {
                    $correlations[] = [
                        'metric_1_id' => $metricIds[$i],
                        'metric_2_id' => $metricIds[$j],
                        'correlation' => round($corr, 4),
                    ];
                }
            }
        }

        return $correlations;
    }

    /**
     * Calculate Pearson correlation between two metrics
     */
    protected function calculateCorrelation(int $metricId1, int $metricId2): ?float
    {
        $values1 = MetricService::getTrendData($metricId1, 30);
        $values2 = MetricService::getTrendData($metricId2, 30);

        if (count($values1) !== count($values2) || count($values1) < 3) {
            return null;
        }

        $y1 = array_column($values1, 'value');
        $y2 = array_column($values2, 'value');

        return $this->pearsonCorrelation($y1, $y2);
    }

    /**
     * Pearson correlation coefficient
     */
    protected function pearsonCorrelation(array $x, array $y): float
    {
        $n = count($x);
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = array_sum(array_map(fn($a, $b) => $a * $b, $x, $y));
        $sumXX = array_sum(array_map(fn($v) => $v * $v, $x));
        $sumYY = array_sum(array_map(fn($v) => $v * $v, $y));

        $denominator = sqrt(($n * $sumXX - $sumX * $sumX) * ($n * $sumYY - $sumY * $sumY));

        if ($denominator == 0) {
            return 0;
        }

        return ($n * $sumXY - $sumX * $sumY) / $denominator;
    }
}
