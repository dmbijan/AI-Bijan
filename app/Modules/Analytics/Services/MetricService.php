<?php

namespace App\Modules\Analytics\Services;

use App\Modules\Analytics\Models\Metric;
use App\Modules\Analytics\Models\MetricValue;
use Illuminate\Support\Facades\DB;

class MetricService
{
    /**
     * Get all metrics for tenant
     */
    public function getMetrics(int $tenantId, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Metric::where('tenant_id', $tenantId)->with(['values'])->latest();

        if (isset($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (isset($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (isset($filters['active'])) {
            $query->active();
        }

        return $query->paginate(20);
    }

    /**
     * Get single metric with history
     */
    public function getMetric(int $tenantId, int $metricId): ?Metric
    {
        return Metric::where('tenant_id', $tenantId)
            ->with(['values' => function ($q) {
                $q->orderBy('calculated_at', 'desc')->take(100);
            }])
            ->find($metricId);
    }

    /**
     * Calculate a single metric
     */
    public function calculateMetric(int $tenantId, int $metricId): array
    {
        $metric = $this->getMetric($tenantId, $metricId);

        if (!$metric) {
            return ['success' => false, 'message' => 'Metric not found'];
        }

        $value = $metric->calculate();

        return [
            'success' => true,
            'metric_id' => $metricId,
            'value' => $value,
            'trend' => $metric->trend_direction,
            'trend_percentage' => $metric->trend_percentage,
            'calculated_at' => $metric->last_calculated_at,
        ];
    }

    /**
     * Calculate all metrics for tenant
     */
    public function calculateAllMetrics(int $tenantId): array
    {
        $metrics = Metric::where('tenant_id', $tenantId)->active()->get();
        $results = [];

        DB::beginTransaction();
        try {
            foreach ($metrics as $metric) {
                $results[] = $this->calculateMetric($tenantId, $metric->id);
            }

            DB::commit();

            return [
                'success' => true,
                'total' => $metrics->count(),
                'results' => $results,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get metric trend data
     */
    public function getTrendData(int $tenantId, int $metricId, int $days = 30): array
    {
        return MetricValue::getTrendData($metricId, $days);
    }

    /**
     * Create or update metric
     */
    public function upsertMetric(int $tenantId, array $data): Metric
    {
        return Metric::updateOrCreate(
            ['tenant_id' => $tenantId, 'code' => $data['code']],
            array_merge($data, ['tenant_id' => $tenantId])
        );
    }

    /**
     * Delete metric
     */
    public function deleteMetric(int $tenantId, int $metricId): bool
    {
        return Metric::where('tenant_id', $tenantId)->destroy($metricId);
    }

    /**
     * Get metric values within date range
     */
    public function getValuesInRange(int $tenantId, int $metricId, string $from, string $to): array
    {
        return MetricValue::where('metric_id', $metricId)
            ->whereBetween('calculated_at', [$from, $to])
            ->orderBy('calculated_at')
            ->get()
            ->toArray();
    }

    /**
     * Trigger batch calculation
     */
    public function triggerBatchCalculation(array $metricIds): array
    {
        $results = [];

        foreach ($metricIds as $metricId) {
            $metric = Metric::find($metricId);
            if ($metric) {
                $results[] = $this->calculateMetric($metric->tenant_id, $metricId);
            }
        }

        return $results;
    }
}
