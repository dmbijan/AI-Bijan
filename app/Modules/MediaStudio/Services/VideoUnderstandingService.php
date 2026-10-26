<?php

namespace App\Modules\MediaStudio\Services;

use App\Modules\AIGateway\Services\GatewayService;
use App\Modules\MediaStudio\Models\MediaAsset;
use App\Modules\MediaStudio\Models\MediaJob;
use App\Modules\MediaStudio\Models\MediaTranscript;
use App\Modules\MediaStudio\Models\MediaUnderstanding;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * VideoUnderstandingService — pipeline pemahaman video (BAB 82.3).
 *
 * Pipeline 10 langkah:
 * 1. Validasi & sanitasi
 * 2. Transcode (ffmpeg)
 * 3. Scene detection → keyframes
 * 4. Transkripsi (ASR via AI Gateway)
 * 5. Deskripsi visual (VLM per scene)
 * 6. Ekstraksi tindakan
 * 7. Entiti & kata kunci
 * 8. Ringkasan + QA pairs
 * 9. Embed & index
 * 10. Simpan
 *
 * ⚠️ Jika ffmpeg tidak tersedia, langkah 2-3 dilangkau — pipeline
 * terus ke transkripsi. Ini degradation yang selamat, bukan kegagalan.
 */
class VideoUnderstandingService
{
    public function __construct(
        private MediaModerationService $moderation,
        private ConsentGateService $consentGate
    ) {
    }

    /**
     * Jalankan pemahaman untuk satu aset video.
     */
    public function fahami(MediaAsset $asset, int $tenantId, ?int $userId = null): MediaJob
    {
        $job = MediaJob::create([
            'tenant_id' => $tenantId,
            'kind' => MediaJob::KIND_UNDERSTANDING,
            'status' => MediaJob::STATUS_RUNNING,
            'input_asset_id' => $asset->id,
            'started_at' => now(),
            'requested_by' => $userId,
            'autonomy_level' => 0,
            'approval_required' => false,
        ]);

        try {
            $this->proses($job, $asset, $tenantId, $userId);

            $job->update([
                'status' => MediaJob::STATUS_SUCCEEDED,
                'progress' => 100,
                'finished_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('MediaStudio: pemahaman video gagal', [
                'asset_id' => $asset->id,
                'error' => $e->getMessage(),
            ]);

            $job->update([
                'status' => MediaJob::STATUS_FAILED,
                'error' => $e->getMessage(),
                'finished_at' => now(),
            ]);
        }

        return $job->fresh();
    }

    /**
     * Pemprosesan dalaman.
     */
    private function proses(MediaJob $job, MediaAsset $asset, int $tenantId, ?int $userId): void
    {
        // Langkah 2-3: transcode + keyframe (kalau ffmpeg ada)
        $keyframes = [];
        $ffmpeg = $this->ffmpegAvailable();

        if ($ffmpeg && $asset->isVideo()) {
            $job->update(['progress' => 20]);
            $keyframes = $this->ekstrakKeyframes($asset, $tenantId);
        }

        // Langkah 4: transkripsi
        $job->update(['progress' => 40]);
        $transkripsi = $this->transkripsi($asset, $tenantId, $userId);

        // Moderasi transkripsi
        $moderation = $this->moderation->semakTeks($transkripsi['text']);
        if (! $moderation['passed']) {
            $job->update([
                'status' => MediaJob::STATUS_FAILED,
                'error' => 'Moderasi gagal: '.implode(', ', array_column($moderation['violations'], 'kategori')),
            ]);

            return;
        }

        if ($transkripsi['text'] !== '' || ! empty($transkripsi['segments'])) {
            MediaTranscript::updateOrCreate(
                ['tenant_id' => $tenantId, 'asset_id' => $asset->id],
                [
                    'language' => $transkripsi['language'],
                    'segments' => $transkripsi['segments'],
                    'text' => $transkripsi['text'],
                    'confidence' => $transkripsi['confidence'],
                    'keywords' => $transkripsi['keywords'],
                ]
            );
        }

        // Langkah 5-8: analisis AI
        $job->update(['progress' => 60]);
        $analisis = $this->analisisAI($asset, $tenantId, $userId, $transkripsi, $keyframes);

        if ($analisis === null) {
            // AI tidak tersedia — simpan apa yang ada
            MediaUnderstanding::updateOrCreate(
                ['tenant_id' => $tenantId, 'asset_id' => $asset->id],
                [
                    'summary' => $transkripsi['text'] !== '' ? mb_substr($transkripsi['text'], 0, 2000) : null,
                    'actions' => [],
                    'entities' => [],
                    'timeline' => [],
                    'qa_pairs' => [],
                    'model' => 'fallback-transcript-only',
                ]
            );

            return;
        }

        // Langkah 10: simpan
        MediaUnderstanding::updateOrCreate(
            ['tenant_id' => $tenantId, 'asset_id' => $asset->id],
            [
                'summary' => $analisis['summary'] ?? null,
                'actions' => $analisis['actions'] ?? [],
                'entities' => $analisis['entities'] ?? [],
                'timeline' => $analisis['timeline'] ?? [],
                'qa_pairs' => $analisis['qa_pairs'] ?? [],
                'model' => $analisis['model'] ?? null,
                'language' => $transkripsi['language'],
            ]
        );
    }

    /**
     * Transkripsi melalui AI Gateway (ASR).
     */
    private function transkripsi(MediaAsset $asset, int $tenantId, ?int $userId): array
    {
        $kosong = [
            'text' => '', 'segments' => [], 'language' => 'ms',
            'confidence' => null, 'keywords' => [],
        ];

        if (! config('ai_gateway.live_calls_enabled', false)) {
            return $kosong;
        }

        try {
            $path = Storage::disk($asset->storage_disk)->path($asset->storage_path);
        } catch (\Throwable) {
            return $kosong;
        }

        if (! is_file($path)) {
            return $kosong;
        }

        try {
            $hasil = app(GatewayService::class)->call(
                $tenantId,
                [[
                    'role' => 'user',
                    'content' => 'Transkripsikan fail audio ini. Kembalikan JSON dengan medan: text (string penuh), segments (array {start,end,text,speaker}), language (string), confidence (number 0-1), keywords (array string).',
                ]],
                'audio_transcription',
                [
                    'user_id' => $userId,
                    'attachments' => [[
                        'type' => 'audio',
                        'path' => $path,
                        'mime' => $asset->mime,
                    ]],
                ]
            );

            $kandungan = $hasil['kandungan'] ?? $hasil['content'] ?? '';

            if (is_string($kandungan) && $kandungan !== '') {
                $decoded = json_decode($kandungan, true);
                if (is_array($decoded)) {
                    return array_merge($kosong, $decoded);
                }

                return array_merge($kosong, ['text' => $kandungan]);
            }
        } catch (\Throwable $e) {
            Log::warning('MediaStudio: transkripsi gagal', [
                'asset_id' => $asset->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $kosong;
    }

    /**
     * Analisis AI — ringkasan, tindakan, entiti, QA pairs.
     */
    private function analisisAI(
        MediaAsset $asset,
        int $tenantId,
        ?int $userId,
        array $transkripsi,
        array $keyframes
    ): ?array {
        if (! config('ai_gateway.live_calls_enabled', false)) {
            return null;
        }

        $prompt = $this->binaPrompt($transkripsi, $keyframes);

        try {
            $hasil = app(GatewayService::class)->call(
                $tenantId,
                [['role' => 'user', 'content' => $prompt]],
                'video_understanding',
                ['user_id' => $userId]
            );

            $kandungan = $hasil['kandungan'] ?? $hasil['content'] ?? '';

            if (! is_string($kandungan) || $kandungan === '') {
                return null;
            }

            // Cuba parse JSON (AI mungkin bungkus dalam ```json ... ```)
            $bersih = trim(preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $kandungan));
            $decoded = json_decode($bersih, true);

            if (! is_array($decoded)) {
                return ['summary' => $kandungan, 'actions' => [], 'entities' => [], 'timeline' => [], 'qa_pairs' => []];
            }

            return $decoded;
        } catch (\Throwable $e) {
            Log::warning('MediaStudio: analisis AI gagal', [
                'asset_id' => $asset->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Bina prompt analisis.
     */
    private function binaPrompt(array $transkripsi, array $keyframes): string
    {
        $bahasa = $transkripsi['language'] ?: 'ms';

        $prompt = <<<PROMPT
Anda penganalisis video untuk platform perniagaan. Analisis rekod mesy meeting / SOP / video latihan berikut.

TRANSKRIPSI:
{$transkripsi['text']}

Kunci frame diekstrak: {$keyframes['count']}

Kembalikan JSON SAHAJA (tanpa teks lain) dengan struktur:
{
  "summary": "ringkasan 3-5 ayat dalam Bahasa Melayu",
  "actions": [{"actor": "siapa", "action": "apa yang dibuat", "object": "apa yangcalia", "timestamp": "mm:ss", "confidence": 0.0-1.0}],
  "entities": {"people": [], "places": [], "products": [], "numbers": [], "decisions": []},
  "timeline": [{"time": "mm:ss", "event": "apa yang berlaku"}],
  "qa_pairs": [{"question": "soalan lazim tentang video ini", "answer": "jawapan"}]
}

Gunakan Bahasa Melayu untuk ringkasan dan timeline. EJA yang tidak jelas, jangan mengarang.
PROMPT;

        return $prompt;
    }

    /**
     * Ekstrak keyframe menggunakan ffmpeg (kalau ada).
     */
    private function ekstrakKeyframes(MediaAsset $asset, int $tenantId): array
    {
        $max = (int) config('media_studio.limits.max_keyframes', 120);

        try {
            $disk = Storage::disk($asset->storage_disk);
            $path = $disk->path($asset->storage_path);

            if (! is_file($path)) {
                return ['count' => 0, 'paths' => []];
            }

            $outputDir = storage_path('app/media-keyframes/'.$tenantId.'/'.$asset->id);
            @mkdir($outputDir, 0775, true);

            $cmd = sprintf(
                'ffmpeg -i %s -vf "fps=1/10,scale=640:-1" -frames:v %d %s 2>&1',
                escapeshellarg($path),
                $max,
                escapeshellarg($outputDir.'/frame-%04d.jpg')
            );

            @exec($cmd, $output, $exitCode);

            $files = glob($outputDir.'/frame-*.jpg') ?: [];

            return ['count' => count($files), 'paths' => $files];
        } catch (\Throwable $e) {
            Log::info('MediaStudio: ekstraksi keyframe gagal, terus tanpa keyframe', [
                'error' => $e->getMessage(),
            ]);

            return ['count' => 0, 'paths' => []];
        }
    }

    /**
     * Adakah ffmpeg tersedia?
     */
    public function ffmpegAvailable(): bool
    {
        static $tersedia = null;

        if ($tersedia !== null) {
            return $tersedia;
        }

        @exec('ffmpeg -version 2>&1', $output, $exitCode);
        $tersedia = $exitCode === 0;

        return $tersedia;
    }
}
