<?php

namespace App\Modules\SalesCopilot\Services;

use App\Modules\Prospecting\Models\Prospect;
use Illuminate\Support\Facades\Log;

/**
 * FollowUpNudgerService — cadang follow-up untuk prospect yang "diam".
 *
 * ⚠️ Pagar: hanya prospect dengan consent granted dicadangkan.
 * Nudger tidak menghantar — ia hanya mencadangkan (kepada eksekutif).
 */
class FollowUpNudgerService
{
    /**
     * Senarai prospect yang patut di-follow-up.
     */
    public function cadangFollowUp(int $tenantId, int $limit = 20): array
    {
        $idleDays = (int) config('sales_copilot.follow_up.idle_days', 5);
        $maxPerDay = (int) config('sales_copilot.follow_up.max_per_day', 20);

        $prospects = Prospect::query()
            ->where('tenant_id', $tenantId)
            // PAGAR: consent wajib
            ->where('consent_status', Prospect::CONSENT_GRANTED)
            ->whereNotNull('consent_evidence')
            ->whereIn('status', [
                Prospect::STATUS_APPROVED,
                Prospect::STATUS_CONTACTED,
                Prospect::STATUS_SCORED,
                Prospect::STATUS_REPLIED,
            ])
            ->where(function ($q) use ($idleDays) {
                $q->whereNull('last_touched_at')
                    ->orWhere('last_touched_at', '<', now()->subDays($idleDays));
            })
            ->orderByDesc('score')
            ->limit(min($limit, $maxPerDay))
            ->get();

        $suggestions = [];

        foreach ($prospects as $prospect) {
            $idleFor = $prospect->last_touched_at
                ? (int) $prospect->last_touched_at->diffInDays(now())
                : 0;

            $suggestions[] = [
                'prospect_id' => $prospect->id,
                'nama' => trim($prospect->first_name.' '.$prospect->last_name),
                'syarikat' => $prospect->company,
                'skor' => (int) $prospect->score,
                'status' => $prospect->status,
                'diam_sejak_hari' => $idleFor,
                'cadangan_saluran' => $this->cadanganSaluran($prospect),
                'urgensi' => $idleFor >= 14 ? 'tinggi' : ($idleFor >= 7 ? 'sederhana' : 'rendah'),
                'tidor' => $idleFor,
            ];
        }

        Log::info('SalesCopilot: follow-up dicadangkan', [
            'tenant_id' => $tenantId,
            'count' => count($suggestions),
        ]);

        return $suggestions;
    }

    /**
     * Cadangan saluran komunikasi berdasarkan data & consent.
     */
    private function cadanganSaluran(Prospect $prospect): string
    {
        if ($prospect->bolehDihantar('whatsapp')) {
            return 'whatsapp';
        }

        if ($prospect->bolehDihantar('email')) {
            return 'email';
        }

        return 'tiada';
    }
}
