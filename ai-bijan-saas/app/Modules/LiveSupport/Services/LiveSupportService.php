<?php

namespace App\Modules\LiveSupport\Services;

use App\Modules\LiveSupport\Models\LiveSupportCall;
use App\Modules\LiveSupport\Models\LiveSupportChannel;
use App\Modules\LiveSupport\Models\LiveSupportTranscript;
use Illuminate\Support\Facades\Log;

/**
 * LiveSupportService — customer service live diuruskan AI, dengan eskalasi manusia.
 *
 * ✅ Idea #28 (BAB 86): "call service customer service live di jaga oleh ai,
 * connect ke e-sim".
 *
 * Aliran:
 * 1. Panggilan masuk → AI jawab (jika `ai_enabled`)
 * 2. AI transkripsi + analisis niat + sentiment
 * 3. Jika keyakinan < threshold ATAU sentiment negatif ATAU kata kunci kritikal
 *    → eskalasi ke manusia (`human_fallback`)
 * 4. Selepas tamat → ringkasan AI + tindakan yang diambil
 *
 * ⚠️ Telefoni sebenar (Twilio/operator eSIM) memerlukan kredensial — modul ini
 * mengurus orkestrasi & rekod; penghantaran audio dilakukan oleh adapter.
 */
class LiveSupportService
{
    /** Kata kunci yang sentiasa mencetuskan eskalasi manusia. */
    public const ESKALASI_KEYWORDS = [
        'refund', 'complaint', 'saman', 'lawyer', 'police', 'scam', 'tipu',
        'consumer tribunal', 'tribunal', 'batal order', 'marah', 'teruk',
    ];

    /**
     * Terima panggilan masuk.
     */
    public function terimaPanggilan(int $tenantId, int $channelId, array $data): LiveSupportCall
    {
        $channel = LiveSupportChannel::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($channelId);

        if (! $channel->aiBolehUrus()) {
            throw new \RuntimeException('Saluran ini tidak aktif untuk AI');
        }

        // Semak had serentak
        $aktif = LiveSupportCall::query()
            ->where('tenant_id', $tenantId)
            ->where('live_support_channel_id', $channelId)
            ->whereIn('status', [
                LiveSupportCall::STATUS_RINGING,
                LiveSupportCall::STATUS_ANSWERED,
                LiveSupportCall::STATUS_AI_HANDLING,
                LiveSupportCall::STATUS_HUMAN_HANDLING,
            ])
            ->count();

        if ($aktif >= $channel->max_concurrent) {
            throw new \RuntimeException('Had panggilan serentak tercapai');
        }

        return LiveSupportCall::create([
            'tenant_id' => $tenantId,
            'live_support_channel_id' => $channelId,
            'external_call_id' => $data['external_call_id'] ?? ('sim_' . uniqid()),
            'direction' => $data['direction'] ?? 'inbound',
            'caller_number' => $data['caller_number'] ?? null,
            'caller_name' => $data['caller_name'] ?? null,
            'status' => LiveSupportCall::STATUS_ANSWERED,
            'handler_type' => LiveSupportCall::HANDLER_AI,
            'started_at' => now(),
            'answered_at' => now(),
            'meta' => $data['meta'] ?? null,
        ]);
    }

    /**
     * Tambah transkripsi + analisis.
     */
    public function tambahTranskrip(int $tenantId, int $callId, array $data): LiveSupportTranscript
    {
        $call = LiveSupportCall::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($callId);

        $transcript = LiveSupportTranscript::create([
            'tenant_id' => $tenantId,
            'live_support_call_id' => $call->id,
            'speaker' => $data['speaker'] ?? 'caller',
            'text' => $data['text'] ?? '',
            'confidence' => $data['confidence'] ?? null,
            'offset_ms' => $data['offset_ms'] ?? 0,
        ]);

        $call->update([
            'status' => LiveSupportCall::STATUS_AI_HANDLING,
            'sentiment' => $this->deteksiSentiment($data['text'] ?? ''),
        ]);

        // Semak eskalasi
        $this->semakEskalasi($tenantId, $call->fresh(), $data['text'] ?? '');

        return $transcript;
    }

    /**
     * Semak sama ada perlu eskalasi ke manusia.
     */
    public function semakEskalasi(int $tenantId, LiveSupportCall $call, string $teks): bool
    {
        $channel = $call->channel;
        $perlu = false;
        $sebab = null;

        // 1. Kata kunci kritikal
        foreach (self::ESKALASI_KEYWORDS as $kata) {
            if (stripos($teks, $kata) !== false) {
                $perlu = true;
                $sebab = "Kata kunci kritikal: {$kata}";
                break;
            }
        }

        // 2. Sentiment negatif
        if (! $perlu && $call->sentiment === 'negative') {
            $perlu = true;
            $sebab = 'Sentiment negatif dikesan';
        }

        // 3. Keyakinan rendah
        if (! $perlu && $call->confidence_avg !== null && $channel
            && (float) $call->confidence_avg < (float) $channel->ai_confidence_threshold) {
            $perlu = true;
            $sebab = 'Keyakinan AI di bawah ambang';
        }

        if ($perlu && $channel?->human_fallback) {
            $call->update([
                'handler_type' => LiveSupportCall::HANDLER_HUMAN,
                'status' => LiveSupportCall::STATUS_HUMAN_HANDLING,
                'escalation_reason' => $sebab,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Eskalasi manual ke manusia.
     */
    public function eskalasi(int $tenantId, int $callId, ?string $sebab = null, ?int $userId = null): LiveSupportCall
    {
        $call = LiveSupportCall::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($callId);

        $call->update([
            'handler_type' => LiveSupportCall::HANDLER_HUMAN,
            'status' => LiveSupportCall::STATUS_HUMAN_HANDLING,
            'handler_user_id' => $userId ?? auth()->id(),
            'escalation_reason' => $sebab ?? 'Eskalasi manual',
        ]);

        return $call->fresh();
    }

    /**
     * Tamatkan panggilan + jana ringkasan AI.
     */
    public function tamatPanggilan(int $tenantId, int $callId): LiveSupportCall
    {
        $call = LiveSupportCall::query()
            ->with('transcripts')
            ->where('tenant_id', $tenantId)
            ->findOrFail($callId);

        $durasi = $call->answered_at
            ? (int) $call->answered_at->diffInSeconds(now())
            : 0;

        $ringkasan = $this->janaRingkasan($tenantId, $call);

        $call->update([
            'status' => LiveSupportCall::STATUS_COMPLETED,
            'duration_seconds' => $durasi,
            'ended_at' => now(),
            'ai_summary' => $ringkasan['summary'],
            'ai_actions' => $ringkasan['actions'],
        ]);

        return $call->fresh();
    }

    /**
     * Jana ringkasan perbualan.
     */
    private function janaRingkasan(int $tenantId, LiveSupportCall $call): array
    {
        $teks = $call->transcripts->map(fn ($t) => "{$t->speaker}: {$t->text}")->implode("\n");

        if (trim($teks) === '') {
            return ['summary' => 'Tiada transkripsi direkod.', 'actions' => []];
        }

        try {
            $gateway = app(\App\Modules\AIGateway\Services\GatewayService::class);

            $hasil = $gateway->call(
                $tenantId,
                [
                    ['role' => 'system', 'content' => 'Ringkaskan panggilan perkhidmatan pelanggan dalam 2 ayat.'],
                    ['role' => 'user', 'content' => $teks],
                ],
                'live_support_summary',
                ['max_output_tokens' => 250, 'source_module' => 'live_support']
            );

            $kandungan = (string) ($hasil['content'] ?? '');

            if (trim($kandungan) === '' || str_starts_with($kandungan, '[SIMULASI]')) {
                throw new \RuntimeException('Ringkasan AI tidak tersedia');
            }

            return ['summary' => $kandungan, 'actions' => []];
        } catch (\Throwable $e) {
            // Fallback heuristik
            $bilangan = $call->transcripts->count();
            $sentiment = $call->sentiment ?? 'neutral';

            return [
                'summary' => "Panggilan {$bilangan} giliran, sentiment {$sentiment}."
                    . ($call->escalation_reason ? " Dieskalasi: {$call->escalation_reason}." : ''),
                'actions' => [],
            ];
        }
    }

    /**
     * Deteksi sentiment ringkas.
     */
    private function deteksiSentiment(string $teks): string
    {
        $teks = mb_strtolower($teks);

        $negatif = ['marah', 'teruk', 'kecewa', 'tipu', 'lambat', 'rosak', 'bodoh', 'refund', 'complaint'];
        $positif = ['terima kasih', 'bagus', 'hebat', 'puas hati', 'suka', 'baik'];

        foreach ($negatif as $k) {
            if (str_contains($teks, $k)) {
                return 'negative';
            }
        }

        foreach ($positif as $k) {
            if (str_contains($teks, $k)) {
                return 'positive';
            }
        }

        return 'neutral';
    }

    /**
     * Ringkasan operasi live support.
     */
    public function ringkasan(int $tenantId, int $hari = 30): array
    {
        $calls = LiveSupportCall::query()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays($hari))
            ->get();

        $jumlah = $calls->count();
        $eskalasi = $calls->where('handler_type', LiveSupportCall::HANDLER_HUMAN)->count();
        $selesai = $calls->where('status', LiveSupportCall::STATUS_COMPLETED);

        return [
            'jumlah_panggilan' => $jumlah,
            'dijawab_ai' => $calls->where('handler_type', LiveSupportCall::HANDLER_AI)->count(),
            'dieskalasi' => $eskalasi,
            'kadar_eskalasi' => $jumlah > 0 ? round($eskalasi / $jumlah * 100, 2) : 0.0,
            'selesai' => $selesai->count(),
            'terlepas' => $calls->where('status', LiveSupportCall::STATUS_MISSED)->count(),
            'purata_tempoh_saat' => $selesai->count() > 0
                ? (int) round($selesai->avg('duration_seconds'))
                : 0,
            'ikut_sentiment' => $calls->groupBy('sentiment')->map(fn ($g) => $g->count())->toArray(),
            'saluran_aktif' => LiveSupportChannel::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->count(),
        ];
    }
}
