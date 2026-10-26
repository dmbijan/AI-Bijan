<?php

namespace App\Modules\Marketing\Services;

use App\Modules\Marketing\Models\Campaign;
use Illuminate\Support\Facades\DB;

class CampaignService
{
    /**
     * Get all campaigns for a tenant.
     */
    public function getCampaigns(int $tenantId, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Campaign::where('tenant_id', $tenantId);

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (isset($filters['active_only'])) {
            $query->active();
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get a campaign by ID.
     */
    public function getCampaignById(int $campaignId, int $tenantId): ?Campaign
    {
        return Campaign::where('id', $campaignId)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Get a campaign by slug.
     */
    public function getCampaignBySlug(string $slug, int $tenantId): ?Campaign
    {
        return Campaign::where('slug', $slug)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Create a new campaign.
     */
    public function createCampaign(array $data): Campaign
    {
        if (empty($data['slug'])) {
            $data['slug'] = Campaign::generateSlug($data['name']);
        }

        return DB::transaction(function () use ($data) {
            return Campaign::create($data);
        });
    }

    /**
     * Update a campaign.
     */
    public function updateCampaign(Campaign $campaign, array $data): Campaign
    {
        return DB::transaction(function () use ($campaign, $data) {
            $campaign->update($data);
            return $campaign->fresh();
        });
    }

    /**
     * Delete a campaign.
     */
    public function deleteCampaign(Campaign $campaign): bool
    {
        return $campaign->delete();
    }

    /**
     * Start a campaign.
     */
    public function startCampaign(Campaign $campaign): Campaign
    {
        return $campaign->update(['status' => 'running']) ? $campaign->fresh() : null;
    }

    /**
     * Pause a campaign.
     */
    public function pauseCampaign(Campaign $campaign): Campaign
    {
        return $campaign->update(['status' => 'paused']) ? $campaign->fresh() : null;
    }

    /**
     * Complete a campaign.
     */
    public function completeCampaign(Campaign $campaign): Campaign
    {
        return $campaign->update(['status' => 'completed']) ? $campaign->fresh() : null;
    }

    /**
     * Cancel a campaign.
     */
    public function cancelCampaign(Campaign $campaign): Campaign
    {
        return $campaign->update(['status' => 'cancelled']) ? $campaign->fresh() : null;
    }

    /**
     * Schedule a campaign.
     */
    public function scheduleCampaign(Campaign $campaign, string $startDate, string $endDate): Campaign
    {
        return $campaign->update([
            'status' => 'scheduled',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]) ? $campaign->fresh() : null;
    }

    /**
     * Update campaign performance data.
     */
    public function updatePerformance(Campaign $campaign, array $performanceData): Campaign
    {
        $currentPerformance = $campaign->performance_data ?? [];
        $updatedPerformance = array_merge($currentPerformance, $performanceData);

        return $campaign->update(['performance_data' => $updatedPerformance]) ? $campaign->fresh() : null;
    }

    /**
     * Get campaign statistics.
     */
    public function getCampaignStats(int $tenantId): array
    {
        $totalCampaigns = Campaign::where('tenant_id', $tenantId)->count();
        $activeCampaigns = Campaign::where('tenant_id', $tenantId)
            ->whereIn('status', ['running', 'scheduled'])
            ->count();
        $completedCampaigns = Campaign::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->count();

        return [
            'total' => $totalCampaigns,
            'active' => $activeCampaigns,
            'completed' => $completedCampaigns,
        ];
    }

    /**
     * Get unique slug.
     */
    public function getUniqueSlug(string $name, int $tenantId): string
    {
        $baseSlug = Campaign::generateSlug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Campaign::where('slug', $slug)->where('tenant_id', $tenantId)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
