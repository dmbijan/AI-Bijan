<?php

namespace App\Modules\Prospecting\Services;

use App\Modules\Prospecting\Models\Prospect;
use Illuminate\Support\Facades\Log;

/**
 * ConsentService — PENGETAS KONSEN (Fasa 1 / BAB 82).
 *
 * Ini adalah pintu paling kritikal dalam enjin jualan. Tanpa service ini,
 * blast engine boleh menghantar mesej kepada orang yang tidak pernah
 * memberi kebenaran —AFCti violation PDPA 2010.
 *
 * Prinsip:
 * 1. Tiada mesej keluar tanpa `consent_status = 'granted'`.
 * 2. Consent mestilah ada BUKTI (`consent_evidence` tidak kosong).
 * 3. Consent ada tarikh luput — selepas itu perlu diperbaharui.
 * 4. Penarikan consent mesti berlaku serta-merta dan kekal (suppression list).
 */
class ConsentService
{
    /**-channel yang memerlukan consent eksplisit (semuanya, sebenar). */
    public const CHANNELS_REQUIRING_CONSENT = ['email', 'sms', 'whatsapp', 'video'];

    /** Consent considered stale after this many days. */
    public const CONSENT_MAX_AGE_DAYS = 365;

    /**
     * Bolehkah prospect ini dihubungi melalui channel tertentu?
     */
    public function bolehDihantar(?Prospect $prospect, string $channel): bool
    {
        if (! $prospect) {
            return false;
        }

        if (! in_array($channel, self::CHANNELS_REQUIRING_CONSENT, true)) {
            return false;
        }

        if ($prospect->consent_status !== Prospect::CONSENT_GRANTED) {
            return false;
        }

        if (empty($prospect->consent_evidence)) {
            return false;
        }

        // Consent tamat tempoh
        if ($prospect->consent_expires_at && $prospect->consent_expires_at->isPast()) {
            return false;
        }

        // Consent terlalu lama (lebih 1 tahun) — perlu disegarkan
        if ($prospect->consent_at && $prospect->consent_at->lt(now()->subDays(self::CONSENT_MAX_AGE_DAYS))) {
            return false;
        }

        return $prospect->bolehDihantar($channel);
    }

    /**
     * Tapis senarai prospect kepada yang layak dihantar.
     *
     * @return array{allowed: \Illuminate\Support\Collection, rejected: \Illuminate\Support\Collection}
     */
    public function tapis(?array $prospects, string $channel): array
    {
        $allowed = collect();
        $rejected = collect();

        foreach ($prospects as $p) {
            if ($this->bolehDihantar($p, $channel)) {
                $allowed->push($p);
            } else {
                $rejected->push($p);
            }
        }

        return ['allowed' => $allowed, 'rejected' => $rejected];
    }

    /**
     * Rekod bukti consent.
     */
    public function rekodConsent(
        Prospect $prospect,
        string $source,
        string $evidence,
        ?int $validDays = null
    ): Prospect {
        $prospect->consent_status = Prospect::CONSENT_GRANTED;
        $prospect->consent_source = $source;
        $prospect->consent_evidence = $evidence;
        $prospect->consent_at = now();
        $prospect->consent_expires_at = $validDays
            ? now()->addDays($validDays)
            : now()->addDays(self::CONSENT_MAX_AGE_DAYS);
        $prospect->save();

        Log::info('Prospect: consent direkod', [
            'tenant_id' => $prospect->tenant_id,
            'prospect_id' => $prospect->id,
            'source' => $source,
        ]);

        return $prospect;
    }

    /**
     * Tarik balik consent. Prospect ditolak serta-merta.
     */
    public function tarikConsent(Prospect $prospect, string $reason = 'withdrawn'): Prospect
    {
        $prospect->consent_status = Prospect::CONSENT_DENIED;
        $prospect->consent_at = null;
        $prospect->consent_evidence = null;
        $prospect->consent_expires_at = null;
        $prospect->save();

        Log::warning('Prospect: consent ditarik balik', [
            'tenant_id' => $prospect->tenant_id,
            'prospect_id' => $prospect->id,
            'reason' => $reason,
        ]);

        return $prospect;
    }

    /**
     * Audit report consent untuk pematuhan PDPA.
     */
    public function laporanConsent(int $tenantId): array
    {
        $base = Prospect::query()->where('tenant_id', $tenantId);

        return [
            'jumlah' => (clone $base)->count(),
            'granted' => (clone $base)->where('consent_status', Prospect::CONSENT_GRANTED)->count(),
            'denied' => (clone $base)->where('consent_status', Prospect::CONSENT_DENIED)->count(),
            'unknown' => (clone $base)->where('consent_status', Prospect::CONSENT_UNKNOWN)->count(),
            'tiada_bukti' => (clone $base)
                ->where('consent_status', Prospect::CONSENT_GRANTED)
                ->where(function ($q) {
                    $q->whereNull('consent_evidence')->orWhere('consent_evidence', '');
                })
                ->count(),
            'tamat_tempoh' => (clone $base)
                ->where('consent_status', Prospect::CONSENT_GRANTED)
                ->whereNotNull('consent_expires_at')
                ->where('consent_expires_at', '<', now())
                ->count(),
            'generated_at' => now()->toIso8601String(),
        ];
    }
}
