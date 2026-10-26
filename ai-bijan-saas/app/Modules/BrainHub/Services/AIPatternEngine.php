<?php

namespace App\Modules\BrainHub\Services;

use App\Modules\Analytics\Models\MetricValue;
use Illuminate\Support\Facades\DB;

class AIPatternEngine
{
    /**
     * Detect all patterns in tenant data
     */
    public function detectPatterns(int $tenantId, array $filters = []): array
    {
        $metrics = \App\Modules\Analytics\Models\Metric::where('tenant_id', $tenantId)
            ->active()
            ->get();

        $patterns = [
            'seasonal' => [],
            'trend' => [],
            'cyclical' => [],
            'anomaly' => [],
            'correlation' => [],
        ];

        foreach ($metrics as $metric) {
            // Skip if filtered
            if (!empty($filters['categories']) && !in_array($metric->category, $filters['categories'])) {
                continue;
            }

            // Detect seasonal patterns
            $seasonal = $this->detectSeasonality($metric);
            if ($seasonal) {
                $patterns['seasonal'][] = $seasonal;
            }

            // Detect trends
            $trend = $this->detectTrend($metric);
            if ($trend) {
                $patterns['trend'][] = $trend;
            }

            // Detect anomalies
            $anomalies = $this->detectAnomalies($metric);
            if ($anomalies) {
                $patterns['anomaly'] = array_merge($patterns['anomaly'], $anomalies);
            }
        }

        // Detect correlations between metrics
        $patterns['correlation'] = $this->detectCorrelations($metrics);

        return $patterns;
    }

    /**
     * Detect seasonality using FFT-like approach
     */
    protected function detectSeasonality(\App\Modules\Analytics\Models\Metric $metric): ?array
    {
        $values = MetricValue::where('metric_id', $metric->id)
            ->orderBy('calculated_at')
            ->take(100)
            ->get();

        if ($values->count() < 28) {
            return null;
        }

        $data = $values->pluck('value')->toArray();
        $n = count($data);

        // Simple autocorrelation to find periodicity
        $mean = array_sum($data) / $n;
        $variance = array_reduce($data, fn($c, $v) => $c + pow($v - $mean, 2), 0) / $n;

        if ($variance == 0) {
            return null;
        }

        $bestLag = 0;
        $bestCorrelation = 0;

        for ($lag = 7; $lag < intdiv($n, 2); $lag++) {
            $correlation = 0;
            $count = 0;

            for ($i = 0; $i < $n - $lag; $i++) {
                $correlation += ($data[$i] - $mean) * ($data[$i + $lag] - $mean);
                $count++;
            }

            $correlation /= ($count * $variance);

            if ($correlation > $bestCorrelation && $correlation > 0.3) {
                $bestCorrelation = $correlation;
                $bestLag = $lag;
            }
        }

        if ($bestLag > 0 && $bestCorrelation >= config('brainhub.patterns.min_pattern_strength', 0.7)) {
            return [
                'metric_id' => $metric->id,
                'metric_name' => $metric->name,
                'periodicity_days' => $bestLag,
                'strength' => round($bestCorrelation, 4),
                'description' => sprintf(
                    '%s shows a seasonal pattern with approximately %d-day cycle (confidence: %.0f%%)',
                    $metric->name,
                    $bestLag,
                    $bestCorrelation * 100
                ),
            ];
        }

        return null;
    }

    /**
     * Detect trend using linear regression
     */
    protected function detectTrend(\App\Modules\Analytics\Models\Metric $metric): ?array
    {
        $values = MetricValue::where('metric_id', $metric->id)
            ->orderBy('calculated_at')
            ->take(90)
            ->get();

        if ($values->count() < 14) {
            return null;
        }

        $data = $values->pluck('value')->toArray();
        $n = count($data);

        $sumX = array_sum(range(1, $n));
        $sumY = array_sum($data);
        $sumXY = 0;
        $sumXX = 0;

        foreach (range(1, $n) as $x) {
            $sumXY += $x * $data[$x - 1];
            $sumXX += $x * $x;
        }

        $denominator = $n * $sumXX - $sumX * $sumX;
        if ($denominator == 0) {
            return null;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;
        $intercept = ($sumY - $slope * $sumX) / $n;

        // Calculate R-squared
        $meanY = $sumY / $n;
        $ssTot = 0;
        $ssRes = 0;

        foreach ($data as $i => $y) {
            $ssTot += pow($y - $meanY, 2);
            $predicted = $slope * ($i + 1) + $intercept;
            $ssRes += pow($y - $predicted, 2);
        }

        $rSquared = $ssTot > 0 ? 1 - ($ssRes / $ssTot) : 0;

        if (abs($rSquared) >= config('brainhub.patterns.min_pattern_strength', 0.7)) {
            $direction = $slope > 0 ? 'increasing' : 'decreasing';

            return [
                'metric_id' => $metric->id,
                'metric_name' => $metric->name,
                'direction' => $direction,
                'strength' => round(abs($rSquared), 4),
                'slope' => round($slope, 4),
                'description' => sprintf(
                    '%s shows a %s trend over the past period (R²: %.0f%%)',
                    $metric->name,
                    $direction,
                    abs($rSquared) * 100
                ),
            ];
        }

        return null;
    }

    /**
     * Detect anomalies using statistical methods
     */
    protected function detectAnomalies(\App\Modules\Analytics\Models\Metric $metric): array
    {
        $values = MetricValue::where('metric_id', $metric->id)
            ->orderBy('calculated_at')
            ->take(60)
            ->get();

        if ($values->count() < 10) {
            return [];
        }

        $data = $values->pluck('value')->toArray();
        $n = count($data);
        $mean = array_sum($data) / $n;
        $variance = array_reduce($data, fn($c, $v) => $c + pow($v - $mean, 2), 0) / $n;
        $stdDev = sqrt($variance);

        if ($stdDev == 0) {
            return [];
        }

        $anomalies = [];
        $threshold = 2.5; // Standard deviations

        foreach ($values as $index => $point) {
            $zScore = abs($point->value - $mean) / $stdDev;

            if ($zScore > $threshold) {
                $anomalies[] = [
                    'metric_id' => $metric->id,
                    'metric_name' => $metric->name,
                    'date' => $point->calculated_at->format('Y-m-d'),
                    'value' => $point->value,
                    'z_score' => round($zScore, 2),
                    'deviation' => round($point->value - $mean, 2),
                    'expected_range' => [
                        'min' => round($mean - ($threshold * $stdDev), 2),
                        'max' => round($mean + ($threshold * $stdDev), 2),
                    ],
                ];
            }
        }

        return $anomalies;
    }

    /**
     * Detect correlations between metrics
     */
    protected function detectCorrelations($metrics): array
    {
        $correlations = [];
        $metricArray = $metrics->toArray();
        $limit = min(count($metricArray), 20); // Limit to avoid O(n²) explosion

        for ($i = 0; $i < $limit; $i++) {
            for ($j = $i + 1; $j < $limit; $j++) {
                $corr = $this->calculatePearsonCorrelation(
                    $metricArray[$i]['id'],
                    $metricArray[$j]['id']
                );

                if ($corr !== null && abs($corr) >= 0.7) {
                    $correlations[] = [
                        'metric_1_id' => $metricArray[$i]['id'],
                        'metric_1_name' => $metricArray[$i]['name'],
                        'metric_2_id' => $metricArray[$j]['id'],
                        'metric_2_name' => $metricArray[$j]['name'],
                        'correlation' => round($corr, 4),
                        'strength' => abs($corr) >= 0.9 ? 'very_strong' : (abs($corr) >= 0.7 ? 'strong' : 'moderate'),
                        'direction' => $corr > 0 ? 'positive' : 'negative',
                    ];
                }
            }
        }

        // Sort by strength
        usort($correlations, fn($a, $b) => abs($b['correlation']) <=> abs($a['correlation']));

        return array_slice($correlations, 0, 20); // Top 20 correlations
    }

    /**
     * Calculate Pearson correlation between two metrics
     */
    protected function calculatePearsonCorrelation(int $metricId1, int $metricId2): ?float
    {
        $values1 = MetricValue::where('metric_id', $metricId1)
            ->orderBy('calculated_at')
            ->take(60)
            ->pluck('value')
            ->toArray();

        $values2 = MetricValue::where('metric_id', $metricId2)
            ->orderBy('calculated_at')
            ->take(60)
            ->pluck('value')
            ->toArray();

        // Ensure same length
        $minLength = min(count($values1), count($values2));
        if ($minLength < 10) {
            return null;
        }

        $values1 = array_slice($values1, -$minLength);
        $values2 = array_slice($values2, -$minLength);

        return $this->pearsonCorrelation($values1, $values2);
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

    /**
     * Get pattern summary for dashboard
     */
    public function getPatternSummary(int $tenantId): array
    {
        $patterns = $this->detectPatterns($tenantId);

        return [
            'total_patterns' => array_sum(array_map('count', $patterns)),
            'by_type' => array_count_values(array_merge(...array_map('array_column', $patterns, ['type']))),
            'top_seasonal' => array_slice($patterns['seasonal'], 0, 5),
            'top_trends' => array_slice($patterns['trend'], 0, 5),
            'top_correlations' => array_slice($patterns['correlation'], 0, 5),
            'anomaly_count' => count($patterns['anomaly']),
        ];
    }
}
