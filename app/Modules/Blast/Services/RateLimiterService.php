<?php

namespace App\Modules\Blast\Services;

use App\Modules\Blast\Models\BlastDeliverability;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * RateLimiterService — had kadar blast.
 *
 * 3 lapisan had:
 * 1. Harian per tenant (config blast.daily_quota)
 * 2. Harian per domain (deliverability table)
 * 3. Sekejap per channel (email 20/s, SMS 10/s, WhatsApp 80/s)
 */
class RateLimiterService
{
    /**
     * Bolehkah hantar satu mesej lagi hari ini?
     */
    public function bolehHantar(int $tenantId, string $channel, ?string $domain = null): bool
    {
        // 1. Harian per tenant
        $tenantQuota = (int) config('blast.quotas.daily_per_tenant', 2000);
        $sentToday = $this->countHariIni($tenantId, $channel);

        if ($sentToday >= $tenantQuota) {
            Log::info('Blast: had harian tenant tercapai', [
                'tenant_id' => $tenantId,
                'channel' => $channel,
                'sent' => $sentToday,
                'quota' => $tenantQuota,
            ]);

            return false;
        }

        // 2. Harian per domain
        if ($domain) {
            $deliverability = $this->deliverability($tenantId, $domain);

            if ($deliverability->is_paused) {
                Log::warning('Blast: domain dijeda', [
                    'tenant_id' => $tenantId,
                    'domain' => $domain,
                    'reason' => $deliverability->pause_reason,
                ]);

                return false;
            }

            if ($deliverability->sent >= $deliverability->daily_quota) {
                return false;
            }
        }

        // 3. Sekejap per channel (token bucket ringkas)
        $perSecond = (int) config("blast.quotas.per_second.{$channel}", 10);

        if ($perSecond <= 0) {
            return true;
        }

        $key = "blast.rate.{$tenantId}.{$channel}";
        $used = (int) Cache::get($key, 0);

        if ($used >= $perSecond) {
            return false;
        }

        Cache::increment($key);
        Cache::put($key, Cache::get($key, 0), now()->addSeconds(2));

        return true;
    }

    /**
     * Bilangan mesej dihantar hari ini (tenant + channel).
     */
    public function countHariIni(int $tenantId, ?string $channel = null): int
    {
        $query = \App\Modules\Blast\Models\BlastMessage::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['sent', 'delivered'])
            ->whereDate('created_at', today());

        if ($channel) {
            $query->where('channel', $channel);
        }

        return $query->count();
    }

    /**
     * Ambil atau cipta rekod deliverability.
     */
    public function deliverability(int $tenantId, string $domain): BlastDeliverability
    {
        $record = BlastDeliverability::firstOrCreate(
            ['tenant_id' => $tenantId, 'domain' => $domain],
            [
                'daily_quota' => (int) config('blast.quotas.daily_per_domain', 500),
                'last_reset_at' => now(),
            ]
        );

        // Reset harian
        if (! $record->last_reset_at || $record->last_reset_at->lt(now()->startOfDay())) {
            $record->update([
                'sent' => 0,
                'delivered' => 0,
                'bounced' => 0,
                'complaints' => 0,
                'opened' => 0,
                'clicked' => 0,
                'last_reset_at' => now(),
            ]);
        }

        return $record;
    }
}
