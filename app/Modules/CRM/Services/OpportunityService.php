<?php

namespace App\Modules\CRM\Services;

use App\Modules\CRM\Models\Opportunity;
use Illuminate\Support\Facades\DB;

class OpportunityService
{
    /**
     * Create a new opportunity.
     */
    public function createOpportunity(array $data): Opportunity
    {
        return DB::transaction(function () use ($data) {
            $opportunityNumber = Opportunity::generateOpportunityNumber();
            
            $opportunity = Opportunity::create([
                'opportunity_number' => $opportunityNumber,
                'tenant_id' => $data['tenant_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'estimated_value' => $data['estimated_value'] ?? 0,
                'probability' => $data['probability'] ?? 10,
                'stage' => 'prospecting',
                'expected_close_date' => $data['expected_close_date'] ?? null,
                'forecast_category' => $data['forecast_category'] ?? 'pipeline',
                'competitors' => $data['competitors'] ?? null,
                'win_strategy' => $data['win_strategy'] ?? null,
                'next_action' => $data['next_action'] ?? null,
                'next_action_date' => $data['next_action_date'] ?? null,
                'owner_id' => $data['owner_id'] ?? null,
            ]);
            
            return $opportunity;
        });
    }

    /**
     * Update opportunity stage.
     */
    public function updateStage(Opportunity $opportunity, string $stage, array $extra = []): Opportunity
    {
        return DB::transaction(function () use ($opportunity, $stage, $extra) {
            $opportunity->stage = $stage;
            
            // Set probability based on stage
            $probabilityMap = [
                'prospecting' => 10,
                'qualification' => 25,
                'needs_analysis' => 40,
                'proposal' => 60,
                'negotiation' => 80,
                'closed_won' => 100,
                'closed_lost' => 0,
            ];
            
            if (isset($probabilityMap[$stage])) {
                $opportunity->probability = $probabilityMap[$stage];
            }
            
            // Handle closing dates
            if (in_array($stage, ['closed_won', 'closed_lost'])) {
                $opportunity->actual_close_date = now()->format('Y-m-d');
                
                if ($stage === 'closed_lost' && isset($extra['loss_reason'])) {
                    $opportunity->loss_reason = $extra['loss_reason'];
                }
            }
            
            // Update next action
            if (isset($extra['next_action'])) {
                $opportunity->next_action = $extra['next_action'];
            }
            if (isset($extra['next_action_date'])) {
                $opportunity->next_action_date = $extra['next_action_date'];
            }
            
            $opportunity->save();
            
            return $opportunity;
        });
    }

    /**
     * Update opportunity value and details.
     */
    public function updateDetails(Opportunity $opportunity, array $data): Opportunity
    {
        return DB::transaction(function () use ($opportunity, $data) {
            $fillable = [
                'estimated_value',
                'probability',
                'expected_close_date',
                'competitors',
                'win_strategy',
                'next_action',
                'next_action_date',
            ];
            
            foreach ($fillable as $field) {
                if (isset($data[$field])) {
                    $opportunity->$field = $data[$field];
                }
            }
            
            $opportunity->save();
            
            return $opportunity;
        });
    }

    /**
     * Assign opportunity to salesperson.
     */
    public function assignOpportunity(Opportunity $opportunity, int $userId): Opportunity
    {
        $opportunity->owner_id = $userId;
        $opportunity->save();
        
        return $opportunity;
    }

    /**
     * Get pipeline summary for dashboard.
     */
    public function getPipelineSummary(int $tenantId): array
    {
        $query = Opportunity::where('tenant_id', $tenantId);
        
        $stages = ['prospecting', 'qualification', 'needs_analysis', 'proposal', 'negotiation'];
        $pipelineByStage = [];
        
        foreach ($stages as $stage) {
            $stageOpportunities = (clone $query)->byStage($stage)->get();
            $pipelineByStage[$stage] = [
                'count' => $stageOpportunities->count(),
                'total_value' => $stageOpportunities->sum('estimated_value'),
                'expected_revenue' => $stageOpportunities->sum(fn($opp) => $opp->expectedRevenue()),
            ];
        }
        
        return [
            'total_opportunities' => (clone $query)->active()->count(),
            'total_pipeline_value' => (clone $query)->active()->sum('estimated_value'),
            'total_expected_revenue' => (clone $query)->active()->sum(fn($opp) => $opp->expectedRevenue()),
            'by_stage' => $pipelineByStage,
            'won_this_month' => (clone $query)->where('stage', 'closed_won')
                ->whereMonth('actual_close_date', now()->month)
                ->count(),
            'lost_this_month' => (clone $query)->where('stage', 'closed_lost')
                ->whereMonth('actual_close_date', now()->month)
                ->count(),
        ];
    }

    /**
     * Get upcoming follow-ups.
     */
    public function getUpcomingFollowUps(int $tenantId, int $daysAhead = 7): array
    {
        return Opportunity::where('tenant_id', $tenantId)
            ->active()
            ->whereNotNull('next_action_date')
            ->where('next_action_date', '>=', now())
            ->where('next_action_date', '<=', now()->addDays($daysAhead))
            ->orderBy('next_action_date')
            ->with(['customer', 'owner'])
            ->get();
    }
}
