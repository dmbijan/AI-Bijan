<?php

namespace App\Modules\Marketing\Services;

use App\Modules\Marketing\Models\Deal;
use App\Modules\Marketing\Models\DealActivity;
use Illuminate\Support\Facades\DB;

class DealService
{
    /**
     * Get all deals for a tenant.
     */
    public function getDeals(int $tenantId, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Deal::where('tenant_id', $tenantId);

        if (isset($filters['stage'])) {
            $query->byStage($filters['stage']);
        }

        if (isset($filters['active_only'])) {
            $query->active();
        }

        if (isset($filters['contact_id'])) {
            $query->where('contact_id', $filters['contact_id']);
        }

        if (isset($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get a deal by ID.
     */
    public function getDealById(int $dealId, int $tenantId): ?Deal
    {
        return Deal::where('id', $dealId)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Create a new deal.
     */
    public function createDeal(array $data): Deal
    {
        return DB::transaction(function () use ($data) {
            return Deal::create($data);
        });
    }

    /**
     * Update a deal.
     */
    public function updateDeal(Deal $deal, array $data): Deal
    {
        return DB::transaction(function () use ($deal, $data) {
            $deal->update($data);
            return $deal->fresh();
        });
    }

    /**
     * Delete a deal.
     */
    public function deleteDeal(Deal $deal): bool
    {
        return $deal->delete();
    }

    /**
     * Move deal to next stage.
     */
    public function moveStage(Deal $deal, string $newStage): Deal
    {
        $stages = ['prospect', 'qualified', 'proposal', 'negotiation', 'closed_won', 'closed_lost'];
        $currentIndex = array_search($deal->stage, $stages);
        $newIndex = array_search($newStage, $stages);

        if ($newIndex <= $currentIndex) {
            throw new \InvalidArgumentException("Cannot move deal backwards in pipeline.");
        }

        // Set close date if closing
        if (in_array($newStage, ['closed_won', 'closed_lost'])) {
            $data = ['stage' => $newStage];
            if (!$deal->actual_close_date) {
                $data['actual_close_date'] = now();
            }
            return $this->updateDeal($deal, $data);
        }

        return $this->updateDeal($deal, ['stage' => $newStage]);
    }

    /**
     * Add activity to deal.
     */
    public function addActivity(Deal $deal, string $type, string $description, float $value = 0): DealActivity
    {
        return DealActivity::create([
            'tenant_id' => $deal->tenant_id,
            'deal_id' => $deal->id,
            'type' => $type,
            'description' => $description,
            'value' => $value,
            'currency' => $deal->currency ?? 'MYR',
        ]);
    }

    /**
     * Get deal statistics.
     */
    public function getDealStats(int $tenantId): array
    {
        $totalDeals = Deal::where('tenant_id', $tenantId)->count();
        $activeDeals = Deal::where('tenant_id', $tenantId)
            ->whereIn('stage', ['prospect', 'qualified', 'proposal', 'negotiation'])
            ->count();
        $wonDeals = Deal::where('tenant_id', $tenantId)
            ->where('stage', 'closed_won')
            ->count();
        $lostDeals = Deal::where('tenant_id', $tenantId)
            ->where('stage', 'closed_lost')
            ->count();

        $totalValue = Deal::where('tenant_id', $tenantId)->sum('value');
        $wonValue = Deal::where('tenant_id', $tenantId)
            ->where('stage', 'closed_won')
            ->sum('value');

        return [
            'total_deals' => $totalDeals,
            'active_deals' => $activeDeals,
            'won_deals' => $wonDeals,
            'lost_deals' => $lostDeals,
            'total_value' => $totalValue,
            'won_value' => $wonValue,
            'win_rate' => ($totalDeals > 0) ? ($wonDeals / $totalDeals) * 100 : 0,
        ];
    }

    /**
     * Forecast revenue.
     */
    public function forecastRevenue(int $tenantId): float
    {
        $deals = Deal::where('tenant_id', $tenantId)
            ->whereIn('stage', ['prospect', 'qualified', 'proposal', 'negotiation'])
            ->get();

        $forecast = 0;
        foreach ($deals as $deal) {
            $forecast += $deal->expected_revenue;
        }

        return round($forecast, 2);
    }

    /**
     * Get overdue deals.
     */
    public function getOverdueDeals(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Deal::where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->whereNull('expected_close_date')
                  ->orWhere('expected_close_date', '<', now());
            })
            ->whereIn('stage', ['prospect', 'qualified', 'proposal', 'negotiation'])
            ->orderBy('expected_close_date', 'asc')
            ->get();
    }
}
