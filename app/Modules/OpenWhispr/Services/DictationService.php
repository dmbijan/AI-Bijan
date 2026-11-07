<?php

namespace App\Modules\OpenWhispr\Services;

use App\Modules\OpenWhispr\Models\DictationSession;
use App\Modules\OpenWhispr\Models\DictationVocabulary;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * DictationService — pengecamalan suara ke teks (STT).
 *
 * inspired oleh https://github.com/OpenWhispr/openwhispr
 *
 * Enjin disokong:
 *   - whisper_local  (faster-whisper / whisper.cpp dihoskan sendiri)
 *   - openai         (whisper-1 melalui API awam)
 *   - whisper        (alias kepada whisper_local)
 *
 * Selepas transkripsi, teks diproses: normalisasi, пункasi, dan
 * menggantikan perkataan dengan kamus tersuai tenant.
 */
class DictationService
{
    public function __construct(private TenantContext $ctx) {}

    /**
     * ID tenant semasa.
     */
    public function tenantId(): int
    {
        return (int) $this->ctx->id();
    }

    /**
     * Transkripsi fail audio.
     *
     * @return array{success: bool, session: DictationSession|null, text: ?string, error: ?string}
     */
    public function transcribe(string $audioPath, array $option = []): array
    {
        $tenantId = (int) $this->ctx->id();

        if ($tenantId <= 0) {
            return $this->gagal('Tiada tenant aktif untuk diktasi.');
        }

        $absolut = storage_path('app/public/'.ltrim($audioPath, '/'));

        if (! is_file($absolut)) {
            return $this->gagal('Fail audio tidak dijumpai: '.$audioPath);
        }

        $session = DictationSession::create([
            'uuid' => (string) Str::uuid(),
            'title' => $option['title'] ?? 'Diktee '.Carbon::now()->format('d/m/Y H:i'),
            'language' => $option['language'] ?? config('open_whispr.default_language', 'ms'),
            'engine' => $option['engine'] ?? config('open_whispr.default_engine', 'whisper_local'),
            'model' => $option['model'] ?? config('open_whispr.default_model', 'base'),
            'status' => DictationSession::STATUS_PROCESSING,
            'audio_path' => $audioPath,
            'audio_size_bytes' => filesize($absolut),
            'target_type' => $option['target_type'] ?? null,
            'target_id' => $option['target_id'] ?? null,
            'created_by' => $option['user_id'] ?? auth()->id(),
        ]);

        try {
            $hasil = $this->hantarEnjin($session, $absolut, $option);
        } catch (Throwable $e) {
            Log::warning('OpenWhispr: transkripsi gagal', ['error' => $e->getMessage()]);
            $session->update([
                'status' => DictationSession::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return $this->gagal($e->getMessage(), $session);
        }

        if (! $hasil['success']) {
            $session->update([
                'status' => DictationSession::STATUS_FAILED,
                'error_message' => $hasil['error'],
                'completed_at' => now(),
            ]);

            return $this->gagal($hasil['error'], $session);
        }

        $raw = $hasil['text'] ?? '';
        $diproses = $this->prosesTeks($raw);

        $session->update([
            'status' => DictationSession::STATUS_COMPLETED,
            'raw_text' => $raw,
            'processed_text' => $diproses,
            'segments' => $hasil['segments'] ?? [],
            'confidence' => (float) ($hasil['confidence'] ?? 0),
            'duration_seconds' => (float) ($hasil['duration'] ?? 0),
            'word_count' => str_word_count($diproses),
            'processing_ms' => (int) ($hasil['processing_ms'] ?? 0),
            'completed_at' => now(),
        ]);

        return [
            'success' => true,
            'session' => $session->fresh(),
            'text' => $diproses,
            'error' => null,
        ];
    }

    /**
     * Hantar audio kepada enjin STT.
     *
     * @param  array<string, mixed>  $option
     * @return array{success: bool, text: ?string, segments: array, confidence: float, duration: float, processing_ms: int, error: ?string}
     */
    protected function hantarEnjin(DictationSession $session, string $absolut, array $option): array
    {
        $mula = microtime(true);

        $hasil = match ($session->engine) {
            'openai' => $this->openai($session, $absolut),
            default => $this->whisperLocal($session, $absolut, $option),
        };

        $hasil['processing_ms'] = (int) ((microtime(true) - $mula) * 1000);

        return $hasil;
    }

    /**
     * Whisper tempatan — `POST /transcribe` (faster-whisper server).
     *
     * @param  array<string, mixed>  $option
     * @return array{success: bool, text: ?string, segments: array, confidence: float, duration: float, processing_ms: int, error: ?string}
     */
    protected function whisperLocal(DictationSession $session, string $absolut, array $option): array
    {
        $base = rtrim((string) config('open_whispr.engines.whisper_local.base_url'), '/');

        $response = Http::asMultipart()
            ->timeout((int) config('open_whispr.timeout_seconds', 300))
            ->attach('audio', file_get_contents($absolut), basename($absolut))
            ->post($base.'/transcribe', [
                'model' => $session->model,
                'language' => $session->language,
                'task' => 'transcribe',
                'response_format' => 'verbose_json',
                'temperature' => 0.0,
                'initial_prompt' => $option['initial_prompt'] ?? $this->promptKamus(),
            ]);

        if (! $response->successful()) {
            return $this->ralat($response);
        }

        $data = $response->json();

        $segments = collect($data['segments'] ?? [])->map(fn ($s) => [
            'start' => $s['start'] ?? null,
            'end' => $s['end'] ?? null,
            'text' => $s['text'] ?? '',
        ])->all();

        $confidence = 0.0;
        if (isset($data['segments']) && is_array($data['segments']) && $data['segments'] !== []) {
            $avg = collect($data['segments'])->avg(fn ($s) => $s['avg_logprob'] ?? null);
            $confidence = is_numeric($avg) ? round(min(1.0, max(0.0, exp((float) $avg))), 4) : 0.0;
        }

        return [
            'success' => true,
            'text' => $data['text'] ?? '',
            'segments' => $segments,
            'confidence' => $confidence,
            'duration' => (float) ($data['duration'] ?? 0),
            'processing_ms' => 0,
            'error' => null,
        ];
    }

    /**
     * OpenAI Whisper API.
     *
     * @return array{success: bool, text: ?string, segments: array, confidence: float, duration: float, processing_ms: int, error: ?string}
     */
    protected function openai(DictationSession $session, string $absolut): array
    {
        $base = rtrim((string) config('open_whispr.engines.openai.base_url'), '/');
        $kunci = $this->kunciApi();

        if ($kunci === '') {
            return [
                'success' => false, 'text' => null, 'segments' => [], 'confidence' => 0.0,
                'duration' => 0.0, 'processing_ms' => 0, 'error' => 'Kunci API OpenAI tidak dikonfigurasi.',
            ];
        }

        $response = Http::asMultipart()
            ->withToken($kunci)
            ->timeout((int) config('open_whispr.timeout_seconds', 300))
            ->attach('file', file_get_contents($absolut), basename($absolut))
            ->post($base.'/audio/transcriptions', [
                'model' => $session->model ?: 'whisper-1',
                'language' => $session->language,
                'response_format' => 'verbose_json',
                'prompt' => $this->promptKamus(),
            ]);

        if (! $response->successful()) {
            return $this->ralat($response);
        }

        $data = $response->json();

        return [
            'success' => true,
            'text' => $data['text'] ?? '',
            'segments' => collect($data['segments'] ?? [])->map(fn ($s) => [
                'start' => $s['start'] ?? null,
                'end' => $s['end'] ?? null,
                'text' => $s['text'] ?? '',
            ])->all(),
            'confidence' => 0.0,
            'duration' => (float) ($data['duration'] ?? 0),
            'processing_ms' => 0,
            'error' => null,
        ];
    }

    /**
     * Proses teks selepas transkripsi.
     *
     * - normalisasi ruang putih berulang
     * - menggantikan ungkapan biasa (filler words)
     * - pakai kamus tersuai tenant
     */
    public function prosesTeks(string $teks): string
    {
        $teks = trim($teks);

        if ($teks === '') {
            return '';
        }

        if (config('open_whispr.processing.strip_html', true)) {
            $teks = strip_tags($teks);
        }

        // Buang filler words (Bahasa Melayu + English) bila diaktifkan.
        if (config('open_whispr.processing.remove_fillers', true)) {
            $filler = config('open_whispr.processing.filler_words', []);
            if ($filler !== []) {
                $corak = '/\b('.implode('|', array_map(fn ($f) => preg_quote($f, '/'), $filler)).')\b/iu';
                $teks = preg_replace($corak, '', $teks) ?? $teks;
            }
        }

        // Normalisasi ruang putih.
        $teks = preg_replace('/\s+/u', ' ', $teks) ?? $teks;

        // Kembalikan tanda baca awal selepas teks kosong.
        $teks = ucfirst($teks);

        return trim($teks);
    }

    /**
     * Ganti perkataan dengan kamus tersuai tenant.
     */
    public function pakaiKamus(string $teks): string
    {
        $kamus = DictationVocabulary::query()
            ->where('is_active', true)
            ->orderByDesc('weight')
            ->get();

        foreach ($kamus as $item) {
            $sasaran = array_filter(array_merge([$item->word], $item->aliases ?? []));

            foreach ($sasaran as $s) {
                $teks = preg_replace(
                    '/\b'.preg_quote((string) $s, '/').'\b/iu',
                    (string) $item->word,
                    $teks
                ) ?? $teks;
            }
        }

        return $teks;
    }

    /**
     * Prompt awal daripada kamus tersuai — membantu Whisper mengenali istilah.
     */
    public function promptKamus(): string
    {
        $perkataan = DictationVocabulary::query()
            ->where('is_active', true)
            ->orderByDesc('weight')
            ->limit(50)
            ->pluck('word')
            ->all();

        return implode(', ', $perkataan);
    }

    /**
     * Kunci API daripada konfigurasi.
     */
    protected function kunciApi(): string
    {
        $kunci = config('open_whispr.engines.openai.api_key');

        if (! $kunci) {
            return '';
        }

        try {
            return Crypt::decryptString($kunci);
        } catch (Throwable) {
            return (string) $kunci;
        }
    }

    /**
     * @return array{success: bool, text: ?string, segments: array, confidence: float, duration: float, processing_ms: int, error: ?string}
     */
    protected function ralat(Response $response): array
    {
        $badan = $response->json();
        $mesej = $badan['error']['message'] ?? $badan['message'] ?? ('HTTP_'.$response->status());

        Log::warning('OpenWhispr: enjin STT gagal', ['status' => $response->status(), 'error' => $mesej]);

        return [
            'success' => false, 'text' => null, 'segments' => [], 'confidence' => 0.0,
            'duration' => 0.0, 'processing_ms' => 0, 'error' => (string) $mesej,
        ];
    }

    /**
     * @return array{success: bool, session: DictationSession|null, text: ?string, error: ?string}
     */
    protected function gagal(string $mesej, ?DictationSession $session = null): array
    {
        return [
            'success' => false,
            'session' => $session,
            'text' => null,
            'error' => $mesej,
        ];
    }
}
