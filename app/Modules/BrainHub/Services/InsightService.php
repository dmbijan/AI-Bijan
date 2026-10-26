<?php

namespace App\Modules\BrainHub\Services;

use App\Modules\BrainHub\Models\Insight;
use App\Modules\Analytics\Services\MetricService;
use App\Modules\Analytics\Services\AnalyticsEngine;
use Illuminate\Support\Facades\DB;

class InsightService
{
    /**
     * Generate insights from metrics
     */
    public function generateInsights(int $tenantId, array $filters = []): array
    {
        $metrics = \App\Modules\Analytics\Models\Metric::where('tenant_id', $tenantId)
            ->active()
            ->whereNotNull('last_value')
            ->get();

        $insights = [];

        foreach ($metrics as $metric) {
            // Skip if filtered
            if (!empty($filters['categories']) && !in_array($metric->category, $filters['categories'])) {
                continue;
            }

            // Check threshold violations
            $thresholdInsights = $this->checkThresholds($metric);
            $insights = array_merge($insights, $thresholdInsights);

            // Check trends
            $trendInsights = $this->checkTrends($metric);
            $insights = array_merge($insights, $trendInsights);

            // Check anomalies
            $anomalyInsights = $this->checkAnomalies($metric);
            $insights = array_merge($insights, $anomalyInsights);
        }

        // Sort by severity
        usort($insights, fn($a, $b) => match([$b['severity'], $a['severity']]) {
            ['critical', 'high'] => 1,
            ['critical', 'medium'] => 1,
            ['high', 'low'] => 1,
            default => 0,
        });

        // Save to database
        foreach ($insights as $insightData) {
            $this->saveInsight($tenantId, $insightData);
        }

        return $insights;
    }

    /**
     * Check threshold violations
     */
    protected function checkThresholds(\App\Modules\Analytics\Models\Metric $metric): array
    {
        $insights = [];

        if ($metric->threshold_critical && $metric->last_value <= $metric->threshold_critical) {
            $insights[] = [
                'title' => sprintf('Critical: %s Below Threshold', $metric->name),
                'description' => sprintf(
                    '%s has dropped to %.2f, which is below the critical threshold of %.2f.',
                    $metric->name,
                    $metric->last_value,
                    $metric->threshold_critical
                ),
                'category' => $metric->category,
                'type' => Insight::TYPE_RISK,
                'severity' => Insight::SEVERITY_CRITICAL,
                'source_module' => $metric->data_source ?? 'unknown',
                'source_metric' => $metric->id,
                'confidence_score' => 0.95,
                'data_points' => [
                    'current_value' => $metric->last_value,
                    'threshold' => $metric->threshold_critical,
                ],
            ];
        } elseif ($metric->threshold_warning && $metric->last_value <= $metric->threshold_warning) {
            $insights[] = [
                'title' => sprintf('Warning: %s Approaching Threshold', $metric->name),
                'description' => sprintf(
                    '%s is at %.2f, approaching the warning threshold of %.2f.',
                    $metric->name,
                    $metric->last_value,
                    $metric->threshold_warning
                ),
                'category' => $metric->category,
                'type' => Insight::TYPE_RISK,
                'severity' => Insight::SEVERITY_HIGH,
                'source_module' => $metric->data_source ?? 'unknown',
                'source_metric' => $metric->id,
                'confidence_score' => 0.85,
                'data_points' => [
                    'current_value' => $metric->last_value,
                    'threshold' => $metric->threshold_warning,
                ],
            ];
        }

        return $insights;
    }

    /**
     * Check trend patterns
     */
    protected function checkTrends(\App\Modules\Analytics\Models\Metric $metric): array
    {
        $insights = [];

        if (abs($metric->trend_percentage ?? 0) > 20) {
            $direction = $metric->trend_direction === 'up' ? 'increased' : 'decreased';
            $severity = abs($metric->trend_percentage) > 50 ? Insight::SEVERITY_HIGH : Insight::SEVERITY_MEDIUM;

            $insights[] = [
                'title' => sprintf('Significant Trend: %s %s by %s%%', $metric->name, $direction, abs($metric->trend_percentage)),
                'description' => sprintf(
                    '%s has %s by %.1f%% compared to the previous period.',
                    $metric->name,
                    $direction,
                    abs($metric->trend_percentage)
                ),
                'category' => $metric->category,
                'type' => Insight::TYPE_TREND,
                'severity' => $severity,
                'source_module' => $metric->data_source ?? 'unknown',
                'source_metric' => $metric->id,
                'confidence_score' => 0.80,
                'data_points' => [
                    'current_value' => $metric->last_value,
                    'previous_value' => $metric->previous_value,
                    'change_percentage' => $metric->trend_percentage,
                ],
            ];
        }

        return $insights;
    }

    /**
     * Check for anomalies
     */
    protected function checkAnomalies(\App\Modules\Analytics\Models\Metric $metric): array
    {
        $insights = [];

        try {
            $engine = app(AnalyticsEngine::class);
            $anomalies = $engine->detectAnomalies($metric->id, 30);

            if (!empty($anomalies['anomalies'])) {
                $latestAnomaly = end($anomalies['anomalies']);

                $insights[] = [
                    'title' => sprintf('Anomaly Detected in %s', $metric->name),
                    'description' => sprintf(
                        'Unusual pattern detected in %s. Value %.2f deviates %.2f standard deviations from mean.',
                        $metric->name,
                        $latestAnomaly['value'],
                        $latestAnomaly['deviation']
                    ),
                    'category' => $metric->category,
                    'type' => Insight::TYPE_ANOMALY,
                    'severity' => Insight::SEVERITY_MEDIUM,
                    'source_module' => $metric->data_source ?? 'unknown',
                    'source_metric' => $metric->id,
                    'confidence_score' => min(0.95, 0.5 + ($latestAnomaly['deviation'] / 10)),
                    'data_points' => $anomalies,
                ];
            }
        } catch (\Exception $e) {
            // Log error but don't fail
            logger()->error('BrainHub: Anomaly detection failed', [
                'metric_id' => $metric->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $insights;
    }

    /**
     * Save insight to database
     */
    protected function saveInsight(int $tenantId, array $data): ?Insight
    {
        // Check for duplicate recent insights
        $exists = Insight::where('tenant_id', $tenantId)
            ->where('title', $data['title'])
            ->where('created_at', '>=', now()->subHours(6))
            ->exists();

        if ($exists) {
            return null;
        }

        return Insight::create(array_merge($data, [
            'tenant_id' => $tenantId,
            'is_read' => false,
        ]));
    }

    /**
     * Get insights for user
     */
    public function getInsights(int $tenantId, int $userId = null, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Insight::where('tenant_id', $tenantId)->latest();

        if (isset($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (isset($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (isset($filters['severity'])) {
            $query->bySeverity($filters['severity']);
        }

        if (isset($filters['unread']) && $filters['unread']) {
            $query->unread();
        }

        if (isset($filters['undismissed']) && $filters['undismissed']) {
            $query->undismissed();
        }

        return $query->paginate(20);
    }

    /**
     * Mark insight as read
     */
    public function markAsRead(int $tenantId, int $insightId): bool
    {
        $insight = Insight::where('tenant_id', $tenantId)->find($insightId);
        return $insight ? $insight->markAsRead() : false;
    }

    /**
     * Dismiss insight
     */
    public function dismissInsight(int $tenantId, int $insightId, int $userId): bool
    {
        $insight = Insight::where('tenant_id', $tenantId)->find($insightId);
        return $insight ? $insight->dismiss($userId) : false;
    }

    /**
     * Record action taken on insight
     */
    public function recordAction(int $tenantId, int $insightId, string $notes = ''): bool
    {
        $insight = Insight::where('tenant_id', $tenantId)->find($insightId);
        return $insight ? $insight->recordAction($notes) : false;
    }

    /**
     * Get insight statistics
     */
    public function getStatistics(int $tenantId): array
    {
        return [
            'total' => Insight::where('tenant_id', $tenantId)->count(),
            'unread' => Insight::where('tenant_id', $tenantId)->unread()->count(),
            'by_severity' => Insight::where('tenant_id', $tenantId)
                ->selectRaw('severity, COUNT(*) as count')
                ->groupBy('severity')
                ->pluck('count', 'severity')
                ->toArray(),
            'by_category' => Insight::where('tenant_id', $tenantId)
                ->selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'by_type' => Insight::where('tenant_id', $tenantId)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
        ];
    }

    /**
     * Bulk generate insights (scheduled job)
     */
    public function bulkGenerate(int $tenantId): array
    {
        DB::beginTransaction();
        try {
            $insights = $this->generateInsights($tenantId);
            DB::commit();

            return [
                'success' => true,
                'insights_generated' => count($insights),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
