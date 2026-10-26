<?php

namespace App\Modules\Prospecting\Services;

use App\Modules\Prospecting\Models\Prospect;

/**
 * ProspectScoringService — skor prospek 0-100.
 *
 * Skor kekal: berbanding LeadScoreService (Marketing) yang fokus interaksi,
 * ini fokus KESELURUHAN (kelengkapan + consent + pengesahan).
 */
class ProspectScoringService
{
    /**
     * Kirakan skor prospect dan simpan.
     */
    public function skor(Prospect $prospect): Prospect
    {
        $breakdown = [];

        $weights = config('prospecting.scoring.weights');

        // Kelengkapan data
        $breakdown['has_email'] = ! empty($prospect->email) ? $weights['has_email'] : 0;
        $breakdown['has_phone'] = ! empty($prospect->phone) ? $weights['has_phone'] : 0;
        $breakdown['has_company'] = ! empty($prospect->company) ? $weights['has_company'] : 0;
        $breakdown['has_job_title'] = ! empty($prospect->job_title) ? $weights['has_job_title'] : 0;
        $breakdown['website_present'] = ! empty($prospect->website) ? $weights['website_present'] : 0;
        $breakdown['linkedin_present'] = ! empty($prospect->linkedin) ? $weights['linkedin_present'] : 0;

        // Consent — tanpa ini, prospect tidak boleh dihubungi langsung
        $breakdown['consent_granted'] = $prospect->consent_status === Prospect::CONSENT_GRANTED
            ? $weights['consent_granted']
            : 0;

        // Pengesahan
        $breakdown['verified'] = $prospect->verification_status === 'verified'
            ? $weights['verified']
            : 0;

        $score = array_sum($breakdown);

        $prospect->score = $score;
        $prospect->score_breakdown = $breakdown;
        $prospect->status = $prospect->status === Prospect::STATUS_NEW
            ? Prospect::STATUS_SCORED
            : $prospect->status;
        $prospect->save();

        return $prospect;
    }

    /**
     * Skor semula semua prospect tenant (untuk scheduler).
     */
    public function skorSemula(int $tenantId): int
    {
        $count = 0;

        Prospect::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [Prospect::STATUS_NEW, Prospect::STATUS_ENRICHED, Prospect::STATUS_SCORED])
            ->chunkById(100, function ($chunk) use (&$count) {
                foreach ($chunk as $prospect) {
                    $this->skor($prospect);
                    $count++;
                }
            });

        return $count;
    }

    /**
     * Ambang label — panas / suam / sejuk.
     */
    public function label(int $score): string
    {
        if ($score >= config('prospecting.scoring.hot_threshold')) {
            return 'panas';
        }

        if ($score >= config('prospecting.scoring.warm_threshold')) {
            return 'suam';
        }

        return 'sejuk';
    }
}
