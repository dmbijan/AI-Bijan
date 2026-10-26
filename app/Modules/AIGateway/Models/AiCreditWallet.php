<?php

namespace App\Modules\AIGateway\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class AiCreditWallet extends Model
{
    use BelongsToTenant;

    protected $table = 'ai_credit_wallets';

    protected $fillable = [
        'tenant_id',
        'balance',
        'period_quota',
        'used_this_period',
        'period_start',
        'period_end',
        'auto_recharge_enabled',
        'auto_recharge_package',
        'currency',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'period_quota' => 'decimal:2',
        'used_this_period' => 'decimal:2',
        'auto_recharge_enabled' => 'boolean',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
    ];

    // ===== Helpers =====

    /**
     * Dapatkan atau cipta wallet untuk tenant
     */
    public static function forTenant(int $tenantId): self
    {
        return static::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'balance' => 0,
                'period_quota' => 0,
                'used_this_period' => 0,
                'period_start' => now()->startOfMonth(),
                'period_end' => now()->endOfMonth(),
                'currency' => 'MYR',
            ]
        );
    }

    /**
     * Baki efektif (topup + kuota belum habis)
     */
    public function effectiveBalance(): float
    {
        $quotaRemaining = max(0, (float) $this->period_quota - (float) $this->used_this_period);
        return (float) $this->balance + $quotaRemaining;
    }

    /**
     * Peratus penggunaan kuota
     */
    public function usagePercent(): float
    {
        $quota = (float) $this->period_quota;
        if ($quota <= 0) {
            return 0.0;
        }

        return round(((float) $this->used_this_period / $quota) * 100, 2);
    }

    /**
     * Boleh guna kredit sebanyak ini?
     */
    public function canAfford(float $amount): bool
    {
        return $this->effectiveBalance() >= $amount;
    }

    /**
     * Tolak kredit — guna kuota dahulu, kemudian topup balance
     */
    public function deduct(float $amount): void
    {
        if (!$this->canAfford($amount)) {
            throw new \RuntimeException('Baki kredit tidak mencukupi.');
        }

        $quotaRemaining = max(0, (float) $this->period_quota - (float) $this->used_this_period);
        $fromQuota = min($amount, $quotaRemaining);

        $this->used_this_period = (float) $this->used_this_period + $fromQuota;

        $remainder = $amount - $fromQuota;
        if ($remainder > 0) {
            $this->balance = (float) $this->balance - $remainder;
        }

        $this->save();
    }

    /**
     * Tambah kredit (top-up)
     */
    public function topUp(float $amount): void
    {
        $this->balance = (float) $this->balance + $amount;
        $this->save();
    }

    /**
     * Reset kuota bulanan
     */
    public function resetPeriod(float $newQuota): void
    {
        $this->period_quota = $newQuota;
        $this->used_this_period = 0;
        $this->period_start = now()->startOfMonth();
        $this->period_end = now()->endOfMonth();
        $this->save();
    }

    /**
     * Alert yang perlu dipaparkan
     */
    public function applicableAlerts(): array
    {
        $percent = $this->usagePercent();
        $thresholds = config('ai_gateway.wallet.low_balance_alerts', [80, 50, 20]);
        $alerts = [];

        foreach ($thresholds as $threshold) {
            // Alert apabila penggunaan melebihi (100 - threshold)%
            if ($percent >= (100 - $threshold)) {
                $alerts[] = [
                    'level' => $threshold,
                    'message' => "Penggunaan kredit AI telah mencapai {$percent}% daripada kuota.",
                ];
            }
        }

        return $alerts;
    }
}
