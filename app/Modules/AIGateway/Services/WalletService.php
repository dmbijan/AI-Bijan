<?php

namespace App\Modules\AIGateway\Services;

use App\Modules\AIGateway\Models\AiCreditWallet;
use App\Modules\AIGateway\Models\AiUsageEvent;
use Illuminate\Support\Facades\DB;

/**
 * AI Credit Wallet Service (BAB 22)
 */
class WalletService
{
    /**
     * Dapatkan wallet tenant
     */
    public function get(int $tenantId): AiCreditWallet
    {
        return AiCreditWallet::forTenant($tenantId);
    }

    /**
     * Tambah kredit (top-up)
     */
    public function topUp(int $tenantId, float $amount, array $meta = []): AiCreditWallet
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Jumlah top-up mesti positif.');
        }

        return DB::transaction(function () use ($tenantId, $amount, $meta) {
            $wallet = AiCreditWallet::forTenant($tenantId);
            $wallet->topUp($amount);

            DB::table('ai_wallet_ledger')->insert([
                'tenant_id' => $tenantId,
                'type' => 'top_up',
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'reference' => $meta['reference'] ?? null,
                'description' => $meta['description'] ?? 'Top-up kredit AI',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $wallet->fresh();
        });
    }

    /**
     * Tolak kredit manual (contoh: pelarasan)
     */
    public function deduct(int $tenantId, float $amount, string $description): AiCreditWallet
    {
        return DB::transaction(function () use ($tenantId, $amount, $description) {
            $wallet = AiCreditWallet::forTenant($tenantId);
            $wallet->deduct($amount);

            DB::table('ai_wallet_ledger')->insert([
                'tenant_id' => $tenantId,
                'type' => 'deduction',
                'amount' => -$amount,
                'balance_after' => $wallet->balance,
                'reference' => null,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $wallet->fresh();
        });
    }

    /**
     * Reset kuota bulanan
     */
    public function resetMonthlyQuota(int $tenantId, float $quota): AiCreditWallet
    {
        $wallet = AiCreditWallet::forTenant($tenantId);
        $wallet->resetPeriod($quota);

        return $wallet->fresh();
    }

    /**
     * Sejarah ledger
     */
    public function ledger(int $tenantId, int $limit = 50): array
    {
        if (!DB::getSchemaBuilder()->hasTable('ai_wallet_ledger')) {
            return [];
        }

        return DB::table('ai_wallet_ledger')
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Ringkasan penggunaan
     */
    public function usageSummary(int $tenantId, $from = null, $to = null): array
    {
        $from = $from ?? now()->startOfMonth();
        $to = $to ?? now();

        $base = AiUsageEvent::forTenant($tenantId)->between($from, $to);

        $wallet = AiCreditWallet::forTenant($tenantId);

        return [
            'wallet' => [
                'balance' => (float) $wallet->balance,
                'period_quota' => (float) $wallet->period_quota,
                'used_this_period' => (float) $wallet->used_this_period,
                'effective_balance' => $wallet->effectiveBalance(),
                'usage_percent' => $wallet->usagePercent(),
                'period_start' => $wallet->period_start,
                'period_end' => $wallet->period_end,
            ],
            'usage' => [
                'total_requests' => (clone $base)->count(),
                'successful' => (clone $base)->successful()->count(),
                'failed' => (clone $base)->where('status', AiUsageEvent::STATUS_FAILED)->count(),
                'blocked' => (clone $base)->where('status', AiUsageEvent::STATUS_BLOCKED)->count(),
                'total_tokens' => (int) (clone $base)->sum('total_tokens'),
                'total_credits' => (int) (clone $base)->sum('credits_charged'),
                'avg_latency_ms' => (int) round((clone $base)->avg('latency_ms') ?? 0),
            ],
            'by_model' => (clone $base)
                ->select('model_display_name', DB::raw('count(*) as requests'), DB::raw('sum(credits_charged) as credits'))
                ->groupBy('model_display_name')
                ->get()
                ->toArray(),
            'by_agent' => (clone $base)
                ->whereNotNull('agent_key')
                ->select('agent_key', DB::raw('count(*) as requests'), DB::raw('sum(credits_charged) as credits'))
                ->groupBy('agent_key')
                ->get()
                ->toArray(),
            'period' => ['from' => $from, 'to' => $to],
        ];
    }

    /**
     * Alert baki rendah
     */
    public function alerts(int $tenantId): array
    {
        return $this->get($tenantId)->applicableAlerts();
    }
}
