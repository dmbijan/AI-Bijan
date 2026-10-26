<?php

namespace App\Modules\SalesCopilot\Services;

use App\Modules\Blast\Models\BlastMessage;
use App\Modules\Prospecting\Models\Prospect;
use Illuminate\Support\Facades\Log;

/**
 * DealBriefService — ringkasan 1 halaman untuk eksekutif/jualan.
 *
 * Menyatukan: data prospect, sejarah blast, skor, dan isyarat untuk
 * satu pandangan yang boleh digunakan dalam mesy meeting.
 */
class DealBriefService
{
    /**
     * Jana brief untuk satu prospect.
     */
    public function brief(int $tenantId, int $prospectId): ?array
    {
        $prospect = Prospect::query()
            ->where('tenant_id', $tenantId)
            ->find($prospectId);

        if (! $prospect) {
            return null;
        }

        $messages = BlastMessage::query()
            ->where('tenant_id', $tenantId)
            ->where('prospect_id', $prospect->id)
            ->orderByDesc('created_at')
            ->limit((int) config('sales_copilot.brief.max_items', 15))
            ->get();

        $campaigns = $messages->pluck('campaign_id')->unique()->filter();

        $campaignNames = \App\Modules\Blast\Models\BlastCampaign::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('id', $campaigns)
            ->pluck('name', 'id');

        $timeline = $messages->map(function ($m) use ($campaignNames) {
            return [
                'tarikh' => optional($m->sent_at ?? $m->created_at)->toDateTimeString(),
                'kempen' => $campaignNames[$m->campaign_id] ?? ('#'.$m->campaign_id),
                'channel' => $m->channel,
                'status' => $m->status,
                'dibuka' => $m->opened_at !== null,
                'diklik' => $m->clicked_at !== null,
                'dibalas' => $m->replied_at !== null,
            ];
        })->toArray();

        return [
            'prospect' => [
                'id' => $prospect->id,
                'nama' => trim($prospect->first_name.' '.$prospect->last_name),
                'syarikat' => $prospect->company,
                'jawatapan' => $prospect->job_title,
                'email' => $prospect->email,
                'telefon' => $prospect->phone,
                'negara' => $prospect->country,
                'saiz_syarikat' => $prospect->company_size,
                'sumber' => $prospect->source_id,
                'status' => $prospect->status,
            ],
            'consent' => [
                'status' => $prospect->consent_status,
                'sumber' => $prospect->consent_source,
                'tarikh' => optional($prospect->consent_at)->toDateTimeString(),
                'boleh_email' => $prospect->bolehDihantar('email'),
                'boleh_whatsapp' => $prospect->bolehDihantar('whatsapp'),
            ],
            'skor' => [
                'nilai' => (int) $prospect->score,
                'label' => $this->labelSkor((int) $prospect->score),
                'perincian' => $prospect->score_breakdown,
            ],
            'isyarat' => $this->isyarat($prospect, $messages),
            'timeline' => $timeline,
            'ringkasan' => $this->ringkasan($prospect, $messages),
            'cadangan_berturut' => $this->cadangan($prospect, $messages),
            'dijana_pada' => now()->toIso8601String(),
        ];
    }

    /**
     * Senarai prospect yang perlu perhatian (panas & lama tidak contacted).
     */
    public function requiresAttention(int $tenantId, int $limit = 10): array
    {
        $idleDays = (int) config('sales_copilot.follow_up.idle_days', 5);
        $hotThreshold = (int) config('prospecting.scoring.hot_threshold', 70);

        return Prospect::query()
            ->where('tenant_id', $tenantId)
            ->where('score', '>=', $hotThreshold)
            ->whereIn('status', [Prospect::STATUS_APPROVED, Prospect::STATUS_CONTACTED, Prospect::STATUS_SCORED])
            ->where(function ($q) use ($idleDays) {
                $q->whereNull('last_touched_at')
                    ->orWhere('last_touched_at', '<', now()->subDays($idleDays));
            })
            ->orderByDesc('score')
            ->limit($limit)
            ->get(['id', 'first_name', 'last_name', 'company', 'score', 'status', 'last_touched_at'])
            ->toArray();
    }

    /**
     * Isyarat untuk brief.
     */
    private function isyarat(Prospect $prospect, $messages): array
    {
        $signals = [];

        if ($messages->whereNotNull('opened_at')->count() > 0) {
            $signals[] = ['jenis' => 'opened', 'label' => 'Telah membuka e-mel'];
        }

        if ($messages->whereNotNull('clicked_at')->count() > 0) {
            $signals[] = ['jenis' => 'clicked', 'label' => 'Telah klik pautan'];
        }

        if ($messages->whereNotNull('replied_at')->count() > 0) {
            $signals[] = ['jenis' => 'replied', 'label' => 'Sudah membalas'];
        }

        if ($messages->where('status', 'bounced')->count() > 0) {
            $signals[] = ['jenis' => 'bounce', 'label' => 'Ada e-mel bounce'];
        }

        if ($prospect->consent_status !== Prospect::CONSENT_GRANTED) {
            $signals[] = ['jenis' => 'no_consent', 'label' => 'Tiada consent — tak boleh dihubungi'];
        }

        $idleDays = (int) config('sales_copilot.follow_up.idle_days', 5);
        if ($prospect->last_touched_at && $prospect->last_touched_at->lt(now()->subDays($idleDays))) {
            $signals[] = ['jenis' => 'idle', 'label' => 'Tiada interaksi '.((int) $prospect->last_touched_at->diffInDays(now())).' hari'];
        }

        return $signals;
    }

    /**
     * Ringkasan automatik (templat, bukan AI — supaya pantas & boleh dipercayai).
     */
    private function ringkasan(Prospect $prospect, $messages): string
    {
        $parts = [];

        $nama = trim($prospect->first_name.' '.$prospect->last_name);
        $syarikat = $prospect->company ?: 'syarikat tidak dinyatakan';

        $parts[] = sprintf(
            '%s (%s) — skor %d (%s).',
            $nama ?: 'Prospect',
            $syarikat,
            (int) $prospect->score,
            $this->labelSkor((int) $prospect->score)
        );

        if ($messages->isNotEmpty()) {
            $total = $messages->count();
            $delivered = $messages->whereIn('status', ['sent', 'delivered'])->count();
            $opened = $messages->whereNotNull('opened_at')->count();
            $replied = $messages->whereNotNull('replied_at')->count();

            $parts[] = sprintf(
                'Blast: %d mesej (%d dihantar, %d dibuka, %d dibalas).',
                $total,
                $delivered,
                $opened,
                $replied
            );
        } else {
            $parts[] = 'Belum ada blast dihantar.';
        }

        if ($prospect->consent_status !== Prospect::CONSENT_GRANTED) {
            $parts[] = 'PENTING: tiada consent direkodkan — mesej luaran DILARANG.';
        }

        return implode(' ', $parts);
    }

    /**
     * Cadangan tindakan berikutnya.
     */
    private function cadangan(Prospect $prospect, $messages): array
    {
        $cadangan = [];

        if ($prospect->consent_status !== Prospect::CONSENT_GRANTED) {
            $cadangan[] = [
                'langkah' => 'Dapatkan consent sebelum menghubungi',
                'ketinggalan' => 'critical',
            ];

            return $cadangan;
        }

        if ($messages->whereNotNull('replied_at')->count() > 0) {
            $cadangan[] = ['langkah' => 'Follow-up balasan — jana draf respons', 'ketinggalan' => 'high'];
        } elseif ($messages->isNotEmpty() && $messages->whereNotNull('opened_at')->count() > 0) {
            $cadangan[] = ['langkah' => 'Tudia opened — hantar follow-up pendek', 'ketinggalan' => 'medium'];
        } elseif ($messages->isEmpty()) {
            $cadangan[] = ['langkah' => 'Hantar mesej pertama', 'ketinggalan' => 'high'];
        } else {
            $cadangan[] = ['langkah' => 'Pertimbangkan saluran lain (WhatsApp)', 'ketinggalan' => 'low'];
        }

        if ((int) $prospect->score >= (int) config('prospecting.scoring.hot_threshold', 70)) {
            $cadangan[] = ['langkah' => 'Prospek panas — hubungi hari ini', 'ketinggalan' => 'high'];
        }

        return $cadangan;
    }

    private function labelSkor(int $score): string
    {
        if ($score >= (int) config('prospecting.scoring.hot_threshold', 70)) {
            return 'panas';
        }

        if ($score >= (int) config('prospecting.scoring.warm_threshold', 45)) {
            return 'suam';
        }

        return 'sejuk';
    }
}
