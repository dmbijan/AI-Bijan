<?php

namespace App\Modules\BrainHub\Services;

use App\Modules\BrainHub\Models\Recommendation;
use App\Modules\BrainHub\Models\Insight;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    /**
     * Generate recommendations from insights
     */
    public function generateRecommendations(int $tenantId, array $filters = []): array
    {
        $insights = Insight::where('tenant_id', $tenantId)
            ->undismissed()
            ->whereNull('action_taken')
            ->latest()
            ->take(100)
            ->get();

        $recommendations = [];

        foreach ($insights as $insight) {
            $rec = $this->createRecommendationFromInsight($insight);
            if ($rec) {
                $recommendations[] = $rec;
            }
        }

        // Save recommendations
        foreach ($recommendations as $data) {
            $this->saveRecommendation($tenantId, $data);
        }

        return $recommendations;
    }

    /**
     * Create recommendation from insight
     */
    protected function createRecommendationFromInsight(Insight $insight): ?array
    {
        switch ($insight->type) {
            case Insight::TYPE_RISK:
                return $this->createRiskMitigationRec($insight);
            case Insight::TYPE_OPPORTUNITY:
                return $this->createOpportunityRec($insight);
            case Insight::TYPE_TREND:
                return $this->createTrendRec($insight);
            case Insight::TYPE_ANOMALY:
                return $this->createAnomalyRec($insight);
            default:
                return null;
        }
    }

    /**
     * Risk mitigation recommendation
     */
    protected function createRiskMitigationRec(Insight $insight): array
    {
        return [
            'title' => sprintf('Address %s Issue', $insight->title),
            'description' => $insight->description,
            'type' => Recommendation::TYPE_RISK_MITIGATION,
            'priority' => match($insight->severity) {
                Insight::SEVERITY_CRITICAL => Recommendation::PRIORITY_URGENT,
                Insight::SEVERITY_HIGH => Recommendation::PRIORITY_HIGH,
                default => Recommendation::PRIORITY_MEDIUM,
            },
            'category' => $insight->category,
            'related_module' => $insight->source_module,
            'estimated_impact' => $insight->confidence_score * 100,
            'action_steps' => $this->generateActionSteps($insight->category, 'risk'),
        ];
    }

    /**
     * Opportunity recommendation
     */
    protected function createOpportunityRec(Insight $insight): array
    {
        return [
            'title' => sprintf('Leverage %s Opportunity', $insight->title),
            'description' => $insight->description,
            'type' => Recommendation::TYPE_OPPORTUNITY,
            'priority' => Recommendation::PRIORITY_MEDIUM,
            'category' => $insight->category,
            'related_module' => $insight->source_module,
            'estimated_savings' => $insight->confidence_score * 50000,
            'action_steps' => $this->generateActionSteps($insight->category, 'opportunity'),
        ];
    }

    /**
     * Trend recommendation
     */
    protected function createTrendRec(Insight $insight): array
    {
        return [
            'title' => sprintf('Respond to %s Trend', $insight->title),
            'description' => $insight->description,
            'type' => Recommendation::TYPE_ACTIONABLE,
            'priority' => abs($insight->data_points['change_percentage'] ?? 0) > 30 
                ? Recommendation::PRIORITY_HIGH 
                : Recommendation::PRIORITY_MEDIUM,
            'category' => $insight->category,
            'related_module' => $insight->source_module,
            'action_steps' => $this->generateActionSteps($insight->category, 'trend'),
        ];
    }

    /**
     * Anomaly recommendation
     */
    protected function createAnomalyRec(Insight $insight): array
    {
        return [
            'title' => sprintf('Investigate %s Anomaly', $insight->title),
            'description' => $insight->description,
            'type' => Recommendation::TYPE_ACTIONABLE,
            'priority' => Recommendation::PRIORITY_HIGH,
            'category' => $insight->category,
            'related_module' => $insight->source_module,
            'action_steps' => [
                'Review recent transactions or events affecting this metric',
                'Check for data collection or reporting errors',
                'Verify external factors (market changes, holidays, etc.)',
                'Document findings and update monitoring thresholds if needed',
            ],
        ];
    }

    /**
     * Generate action steps based on category
     */
    protected function generateActionSteps(string $category, string $context): array
    {
        $steps = match($category) {
            'financial' => [
                'Review financial statements and cash flow',
                'Identify cost reduction opportunities',
                'Assess revenue generation strategies',
                'Consult with finance team for detailed analysis',
            ],
            'sales' => [
                'Analyze sales pipeline and conversion rates',
                'Review sales team performance',
                'Identify top-performing products/services',
                'Develop targeted sales strategies',
            ],
            'operations' => [
                'Audit operational processes',
                'Identify bottlenecks and inefficiencies',
                'Consider automation opportunities',
                'Review resource allocation',
            ],
            'hr' => [
                'Review employee satisfaction surveys',
                'Analyze turnover rates and patterns',
                'Assess training and development needs',
                'Consider retention strategies',
            ],
            'marketing' => [
                'Review campaign performance metrics',
                'Analyze customer acquisition costs',
                'Assess channel effectiveness',
                'Optimize marketing budget allocation',
            ],
            'customer' => [
                'Review customer feedback and complaints',
                'Analyze satisfaction scores',
                'Identify improvement areas',
                'Develop customer retention plans',
            ],
            'inventory' => [
                'Review stock levels and turnover rates',
                'Identify slow-moving items',
                'Optimize reorder points',
                'Consider demand forecasting improvements',
            ],
            default => [
                'Investigate root cause',
                'Develop action plan',
                'Implement changes',
                'Monitor results',
            ],
        };

        return $steps;
    }

    /**
     * Save recommendation to database
     */
    protected function saveRecommendation(int $tenantId, array $data): ?Recommendation
    {
        // Check for duplicate
        $exists = Recommendation::where('tenant_id', $tenantId)
            ->where('title', $data['title'])
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($exists) {
            return null;
        }

        return Recommendation::create(array_merge($data, [
            'tenant_id' => $tenantId,
            'is_accepted' => false,
            'is_completed' => false,
        ]));
    }

    /**
     * Get recommendations for user
     */
    public function getRecommendations(int $tenantId, int $userId = null, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Recommendation::where('tenant_id', $tenantId)->latest();

        if ($userId) {
            // Include personalized and general recommendations
            $query->where(function ($q) use ($userId) {
                $q->whereNull('user_id')->orWhere('user_id', $userId);
            });
        }

        if (isset($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (isset($filters['priority'])) {
            $query->byPriority($filters['priority']);
        }

        if (isset($filters['pending']) && $filters['pending']) {
            $query->pending();
        }

        return $query->paginate(20);
    }

    /**
     * Accept recommendation
     */
    public function acceptRecommendation(int $tenantId, int $recId, int $userId): bool
    {
        $rec = Recommendation::where('tenant_id', $tenantId)
            ->where('id', $recId)
            ->where(function ($q) use ($userId) {
                $q->whereNull('user_id')->orWhere('user_id', $userId);
            })
            ->first();

        return $rec ? $rec->accept() : false;
    }

    /**
     * Complete recommendation
     */
    public function completeRecommendation(int $tenantId, int $recId, string $notes = ''): bool
    {
        $rec = Recommendation::where('tenant_id', $tenantId)
            ->find($recId);

        return $rec ? $rec->complete($notes) : false;
    }

    /**
     * Provide feedback on recommendation
     */
    public function provideFeedback(int $tenantId, int $recId, int $score, string $notes = ''): bool
    {
        $rec = Recommendation::where('tenant_id', $tenantId)->find($recId);
        return $rec ? true : false; // Feedback implementation depends on model
    }

    /**
     * Get recommendation statistics
     */
    public function getStatistics(int $tenantId): array
    {
        return [
            'total' => Recommendation::where('tenant_id', $tenantId)->count(),
            'pending' => Recommendation::where('tenant_id', $tenantId)->pending()->count(),
            'accepted' => Recommendation::where('tenant_id', $tenantId)
                ->where('is_accepted', true)
                ->count(),
            'completed' => Recommendation::where('tenant_id', $tenantId)
                ->where('is_completed', true)
                ->count(),
            'by_type' => Recommendation::where('tenant_id', $tenantId)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_priority' => Recommendation::where('tenant_id', $tenantId)
                ->selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority')
                ->toArray(),
        ];
    }

    /**
     * Bulk generate recommendations
     */
    public function bulkGenerate(int $tenantId): array
    {
        DB::beginTransaction();
        try {
            $recommendations = $this->generateRecommendations($tenantId);
            DB::commit();

            return [
                'success' => true,
                'recommendations_generated' => count($recommendations),
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
