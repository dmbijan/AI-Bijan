<?php

namespace App\Modules\AIGateway\Services;

use App\Modules\AIGateway\Models\AiModel;
use App\Modules\AIGateway\Models\AiUsageEvent;
use Illuminate\Support\Facades\DB;

/**
 * Model Catalog Service (BAB 21.2)
 *
 * Admin mengurus katalog model. Pelanggan hanya nampak display name.
 */
class ModelCatalogService
{
    /**
     * Senarai model (admin view — dengan ID dalaman)
     */
    public function listForAdmin(array $filters = []): array
    {
        $query = AiModel::query();

        if (!empty($filters['tier'])) {
            $query->byTier($filters['tier']);
        }
        if (!empty($filters['provider'])) {
            $query->byProvider($filters['provider']);
        }
        if (isset($filters['active'])) {
            $query->where('is_active', (bool) $filters['active']);
        }

        return $query->orderBy('provider_slug')->orderBy('tier')->get()->toArray();
    }

    /**
     * Senarai model (customer view — tanpa ID dalaman)
     */
    public function listForCustomer(?string $tier = null): array
    {
        $query = AiModel::active()->orderBy('tier');

        if ($tier) {
            $query->byTier($tier);
        }

        return $query->get()->map(fn ($m) => $m->toCustomerArray())->toArray();
    }

    /**
     * Cipta/kemas kini model
     */
    public function upsert(array $data): AiModel
    {
        $required = ['provider_slug', 'provider_model_id', 'display_name', 'tier'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \InvalidArgumentException("Medan '{$field}' diperlukan.");
            }
        }

        $tier = $data['tier'];
        if (!in_array($tier, array_keys(config('ai_gateway.tiers', [])), true)) {
            throw new \InvalidArgumentException("Tier tidak sah: {$tier}");
        }

        return AiModel::updateOrCreate(
            [
                'provider_slug' => $data['provider_slug'],
                'provider_model_id' => $data['provider_model_id'],
            ],
            [
                'display_name' => $data['display_name'],
                'tier' => $tier,
                'capabilities' => $data['capabilities'] ?? ['text'],
                'max_context' => $data['max_context'] ?? null,
                'input_rate' => $data['input_rate'] ?? null,
                'output_rate' => $data['output_rate'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'version_snapshot' => $data['version_snapshot'] ?? null,
                'availability_status' => $data['availability_status'] ?? AiModel::STATUS_AVAILABLE,
                'last_verified' => $data['last_verified'] ?? now(),
                'metadata' => $data['metadata'] ?? null,
            ]
        );
    }

    /**
     * Toggle aktif/tidak aktif
     */
    public function toggle(int $id): AiModel
    {
        $model = AiModel::findOrFail($id);
        $model->is_active = !$model->is_active;
        $model->save();

        return $model;
    }

    /**
     * Statistik penggunaan per model
     */
    public function usageByModel(int $tenantId, $from = null, $to = null): array
    {
        $from = $from ?? now()->subDays(30);
        $to = $to ?? now();

        return AiUsageEvent::forTenant($tenantId)
            ->between($from, $to)
            ->select(
                'model_display_name',
                'tier',
                DB::raw('count(*) as requests'),
                DB::raw('sum(total_tokens) as tokens'),
                DB::raw('sum(credits_charged) as credits'),
                DB::raw('avg(latency_ms) as avg_latency')
            )
            ->groupBy('model_display_name', 'tier')
            ->orderByDesc('requests')
            ->get()
            ->toArray();
    }

    /**
     * Semak kesihatan katalog
     */
    public function healthCheck(): array
    {
        $models = AiModel::all();
        $active = $models->where('is_active', true)->count();
        $stale = $models->filter(function ($m) {
            return $m->last_verified && $m->last_verified->lt(now()->subDays(30));
        })->count();

        return [
            'total_models' => $models->count(),
            'active_models' => $active,
            'inactive_models' => $models->count() - $active,
            'stale_verification' => $stale,
            'by_tier' => $models->groupBy('tier')->map->count()->toArray(),
            'by_provider' => $models->groupBy('provider_slug')->map->count()->toArray(),
            'warning' => $stale > 0
                ? "{$stale} model tidak diverifikasi lebih 30 hari. Sila semak dokumentasi provider."
                : null,
        ];
    }
}
