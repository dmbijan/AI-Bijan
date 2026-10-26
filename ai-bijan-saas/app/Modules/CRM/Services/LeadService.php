<?php

namespace App\Modules\CRM\Services;

use App\Modules\CRM\Models\Lead;
use App\Modules\CRM\Models\Opportunity;
use Illuminate\Support\Facades\DB;

class LeadService
{
    /**
     * Create a new lead.
     */
    public function createLead(array $data): Lead
    {
        return DB::transaction(function () use ($data) {
            $leadNumber = Lead::generateLeadNumber();
            
            $lead = Lead::create([
                'lead_number' => $leadNumber,
                'tenant_id' => $data['tenant_id'],
                'source' => $data['source'] ?? 'website',
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'company' => $data['company'] ?? null,
                'position' => $data['position'] ?? null,
                'requirements' => $data['requirements'] ?? null,
                'estimated_value' => $data['estimated_value'] ?? 0,
                'expected_close_date' => $data['expected_close_date'] ?? null,
                'status' => 'new',
                'priority' => $data['priority'] ?? 'medium',
                'assigned_to' => $data['assigned_to'] ?? null,
            ]);
            
            return $lead;
        });
    }

    /**
     * Update lead status.
     */
    public function updateLeadStatus(Lead $lead, string $status, array $extra = []): Lead
    {
        return DB::transaction(function () use ($lead, $status, $extra) {
            $lead->status = $status;
            
            if (isset($extra['loss_reason'])) {
                $lead->loss_reason = $extra['loss_reason'];
            }
            
            if (in_array($status, ['won', 'lost'])) {
                $lead->converted_at = now();
            }
            
            $lead->save();
            
            return $lead;
        });
    }

    /**
     * Convert lead to opportunity.
     */
    public function convertToOpportunity(Lead $lead, array $opportunityData = []): Opportunity
    {
        return DB::transaction(function () use ($lead, $opportunityData) {
            // Create opportunity
            $opportunity = Opportunity::create([
                'opportunity_number' => Opportunity::generateOpportunityNumber(),
                'tenant_id' => $lead->tenant_id,
                'title' => $opportunityData['title'] ?? $lead->name . ' - Opportunity',
                'description' => $opportunityData['description'] ?? $lead->requirements,
                'estimated_value' => $opportunityData['estimated_value'] ?? $lead->estimated_value,
                'probability' => $opportunityData['probability'] ?? 10,
                'stage' => 'qualification',
                'expected_close_date' => $opportunityData['expected_close_date'] ?? $lead->expected_close_date,
                'owner_id' => $opportunityData['owner_id'] ?? $lead->assigned_to,
            ]);
            
            // Update lead with opportunity reference
            $lead->update([
                'opportunity_id' => $opportunity->id,
                'status' => 'won',
                'converted_at' => now(),
            ]);
            
            return $opportunity;
        });
    }

    /**
     * Assign lead to salesperson.
     */
    public function assignLead(Lead $lead, int $userId): Lead
    {
        $lead->assigned_to = $userId;
        $lead->save();
        
        return $lead;
    }

    /**
     * Get lead statistics for dashboard.
     */
    public function getLeadStatistics(int $tenantId): array
    {
        $query = Lead::where('tenant_id', $tenantId);
        
        return [
            'total_leads' => (clone $query)->count(),
            'new_leads' => (clone $query)->byStatus('new')->count(),
            'contacted_leads' => (clone $query)->byStatus('contacted')->count(),
            'qualified_leads' => (clone $query)->byStatus('qualified')->count(),
            'proposal_leads' => (clone $query)->byStatus('proposal')->count(),
            'negotiation_leads' => (clone $query)->byStatus('negotiation')->count(),
            'won_leads' => (clone $query)->byStatus('won')->count(),
            'lost_leads' => (clone $query)->byStatus('lost')->count(),
            'conversion_rate' => function () use ($query) {
                $total = (clone $query)->count();
                $won = (clone $query)->byStatus('won')->count();
                
                return $total > 0 ? round(($won / $total) * 100, 2) : 0;
            },
            'total_pipeline_value' => (clone $query)->sum('estimated_value'),
        ];
    }
}
