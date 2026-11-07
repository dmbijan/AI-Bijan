<?php

namespace App\Modules\VoiceStudio\Services;

use App\Modules\VoiceStudio\Models\VoiceProfile;
use App\Modules\VoiceStudio\Models\VoiceTrack;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * VoiceEngineService — sintesis suara (TTS) pelbagai enjin.
 *
 * inspired oleh https://github.com/debpalash/VoiceStudio
 *
 * Enjin disokong:
 *   - elevenlabs  (API awam;Tenant boleh ubah ke self-hosted endpoint)
 *   - openai      (TTS gRPC/HTTP)
 *   - piper       (tempatan, HTTP)
 *   - coqui       (tempatan, HTTP)
 *   - elevenlabs_local (self-hosted)
 *
 * Semua enjin dikongsi kontrak `synthesize()` yang memulirkan:
 *   ['success' => bool, 'audio' => ?binary, 'format' => string,
 *    'duration' => float, 'error' => ?string]
 */
class VoiceEngineService
{
    public function __construct(private TenantContext $ctx) {}

    /**
     * Sintesis suara daripada teks.
     *
     * @param  array<string, mixed>  $option
     * @return array{success: bool, track: VoiceTrack|null, audio: ?string, url: ?string, error: ?string}
     */
    public function synthesize(string $text, VoiceProfile $profile, array $option = []): array
    {
        $tenantId = (int) $this->ctx->id();

        if ($tenantId <= 0) {
            return $this->gagal('Tiada tenant aktif untuk sintesis suara.');
        }

        $text = trim($text);

        if ($text === '') {
            return $this->gagal('Teks untuk sintesis tidak boleh kosong.');
        }

        $had = (int) config('voice_studio.limits.max_characters', 5000);

        if (mb_strlen($text) > $had) {
            return $this->gagal('Teks melebihi had '.$had.' aksara.');
        }

        $track = VoiceTrack::create([
            'uuid' => (string) Str::uuid(),
            'voice_profile_id' => $profile->id,
            'engine' => $option['engine'] ?? $profile->engine ?? config('voice_studio.default_engine', 'elevenlabs'),
            'text' => $text,
            'text_length' => mb_strlen($text),
            'status' => VoiceTrack::STATUS_PROCESSING,
            'format' => $option['format'] ?? config('voice_studio.default_format', 'mp3'),
            'created_by' => $option['user_id'] ?? auth()->id(),
        ]);

        try {
            $hasil = $this->hantarEnjin($track, $profile, $option);
        } catch (Throwable $e) {
            Log::warning('VoiceStudio: sintesis gagal', ['error' => $e->getMessage()]);
            $track->update([
                'status' => VoiceTrack::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            return $this->gagal($e->getMessage(), $track);
        }

        if (! $hasil['success']) {
            $track->update([
                'status' => VoiceTrack::STATUS_FAILED,
                'error_message' => $hasil['error'],
                'completed_at' => now(),
            ]);

            return $this->gagal($hasil['error'], $track);
        }

        $audio = $hasil['audio'];
        $path = $this->simpanAudio($tenantId, $track->uuid, $audio, $track->format);

        $track->update([
            'status' => VoiceTrack::STATUS_COMPLETED,
            'audio_path' => $path,
            'audio_url' => $path ? Storage::disk('public')->url($path) : null,
            'duration_seconds' => $hasil['duration'],
            'file_size_bytes' => $audio !== null ? strlen($audio) : 0,
            'cost_rm' => $this->EstimateCost(mb_strlen($text)),
            'completed_at' => now(),
        ]);

        $profile->increment('usage_count');
        $profile->increment('total_characters', mb_strlen($text));

        return [
            'success' => true,
            'track' => $track->fresh(),
            'audio' => $audio,
            'url' => $track->fresh()->audio_url,
            'error' => null,
        ];
    }

    /**
     * Hantar kepada enjin mengikut jenis.
     *
     * @param  array<string, mixed>  $option
     * @return array{success: bool, audio: ?string, duration: float, error: ?string}
     */
    protected function hantarEnjin(VoiceTrack $track, VoiceProfile $profile, array $option): array
    {
        $engine = $track->engine;

        return match ($engine) {
            'openai' => $this->openai($track, $profile),
            'piper' => $this->piper($track, $profile),
            'coqui' => $this->coqui($track, $profile),
            'elevenlabs_local' => $this->elevenlabs($track, $profile, $option),
            'elevenlabs' => $this->elevenlabs($track, $profile, $option),
            default => $this->elevenlabs($track, $profile, $option),
        };
    }

    /**
     * ElevenLabs — `POST /v1/text-to-speech/{voice_id}`.
     *
     * @param  array<string, mixed>  $option
     * @return array{success: bool, audio: ?string, duration: float, error: ?string}
     */
    protected function elevenlabs(VoiceTrack $track, VoiceProfile $profile, array $option): array
    {
        $base = rtrim((string) config('voice_studio.engines.elevenlabs.base_url'), '/');
        $kunci = $this->kunciApi('elevenlabs');
        $voiceId = $profile->engine_voice_id ?: config('voice_studio.engines.elevenlabs.default_voice_id');

        if ($kunci === '') {
            return ['success' => false, 'audio' => null, 'duration' => 0.0, 'error' => 'Kunci API ElevenLabs tidak dikonfigurasi.'];
        }

        $body = [
            'text' => $track->text,
            'model_id' => $option['model_id'] ?? config('voice_studio.engines.elevenlabs.model_id', 'eleven_multilingual_v2'),
            'voice_settings' => [
                'stability' => (float) ($option['stability'] ?? 0.5),
                'similarity_boost' => (float) ($option['similarity_boost'] ?? 0.75),
                'speed' => (float) ($profile->speed ?? 1.0),
            ],
        ];

        $response = $this->hantar($base.'/v1/text-to-speech/'.$voiceId, $body, [
            'xi-api-key' => $kunci,
            'Accept' => 'audio/mpeg',
        ]);

        if (! $response->successful()) {
            return ['success' => false, 'audio' => null, 'duration' => 0.0, 'error' => $this->ralat($response)];
        }

        return [
            'success' => true,
            'audio' => $response->body(),
            'duration' => $this->tekaTempo($track->text),
        ];
    }

    /**
     * OpenAI TTS — `POST /v1/audio/speech`.
     *
     * @return array{success: bool, audio: ?string, duration: float, error: ?string}
     */
    protected function openai(VoiceTrack $track, VoiceProfile $profile): array
    {
        $base = rtrim((string) config('voice_studio.engines.openai.base_url'), '/');
        $kunci = $this->kunciApi('openai');

        if ($kunci === '') {
            return ['success' => false, 'audio' => null, 'duration' => 0.0, 'error' => 'Kunci API OpenAI tidak dikonfigurasi.'];
        }

        $body = [
            'model' => config('voice_studio.engines.openai.model', 'gpt-4o-mini-tts'),
            'voice' => $profile->engine_voice_id ?: config('voice_studio.engines.openai.default_voice', 'alloy'),
            'input' => $track->text,
            'response_format' => $track->format,
            'speed' => (float) ($profile->speed ?? 1.0),
        ];

        $response = $this->hantar($base.'/v1/audio/speech', $body, [
            'Authorization' => 'Bearer '.$kunci,
        ]);

        if (! $response->successful()) {
            return ['success' => false, 'audio' => null, 'duration' => 0.0, 'error' => $this->ralat($response)];
        }

        return [
            'success' => true,
            'audio' => $response->body(),
            'duration' => $this->tekaTempo($track->text),
        ];
    }

    /**
     * Piper — pelayan TTS tempatan.
     *
     * @return array{success: bool, audio: ?string, duration: float, error: ?string}
     */
    protected function piper(VoiceTrack $track, VoiceProfile $profile): array
    {
        $base = rtrim((string) config('voice_studio.engines.piper.base_url'), '/');

        $response = $this->hantar($base.'/api/tts', [
            'text' => $track->text,
            'voice' => $profile->engine_voice_id ?: config('voice_studio.engines.piper.default_voice', 'en_US-lessac-medium'),
            'output_format' => $track->format === 'wav' ? 'wav' : 'mp3',
        ], []);

        if (! $response->successful()) {
            return ['success' => false, 'audio' => null, 'duration' => 0.0, 'error' => $this->ralat($response)];
        }

        return [
            'success' => true,
            'audio' => $response->body(),
            'duration' => $this->tekaTempo($track->text),
        ];
    }

    /**
     * Coqui TTS tempatan.
     *
     * @return array{success: bool, audio: ?string, duration: float, error: ?string}
     */
    protected function coqui(VoiceTrack $track, VoiceProfile $profile): array
    {
        $base = rtrim((string) config('voice_studio.engines.coqui.base_url'), '/');

        $response = $this->hantar($base.'/api/tts', [
            'text' => $track->text,
            'speaker_wav' => $profile->sample_path,
            'language' => $profile->language,
            'speed' => (float) ($profile->speed ?? 1.0),
        ], []);

        if (! $response->successful()) {
            return ['success' => false, 'audio' => null, 'duration' => 0.0, 'error' => $this->ralat($response)];
        }

        return [
            'success' => true,
            'audio' => $response->body(),
            'duration' => $this->tekaTempo($track->text),
        ];
    }

    /**
     * Hantar permintaan HTTP ke enjin.
     *
     * @param  array<string, mixed>  $body
     * @param  array<string, string>  $header
     */
    protected function hantar(string $url, array $body, array $header): Response
    {
        return Http::withHeaders(array_merge(['Content-Type' => 'application/json'], $header))
            ->timeout((int) config('voice_studio.timeout_seconds', 120))
            ->post($url, $body);
    }

    /**
     * Kunci API enjin daripada konfigurasi (Disulitkan pada `.env`).
     */
    protected function kunciApi(string $engine): string
    {
        $kunci = config("voice_studio.engines.{$engine}.api_key");

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
     * Simpan audio ke storan awam.
     */
    protected function simpanAudio(int $tenantId, string $uuid, ?string $audio, string $format): ?string
    {
        if ($audio === null || $audio === '') {
            return null;
        }

        $path = sprintf('voices/%d/%s.%s', $tenantId, $uuid, $format === 'wav' ? 'wav' : 'mp3');

        Storage::disk('public')->put($path, $audio);

        return $path;
    }

    /**
     * Tempo anggaran — ~150 kata/minit bacaan.
     */
    protected function tekaTempo(string $text): float
    {
        $perkataan = max(1, str_word_count(strip_tags($text)));

        return round($perkataan / 150 * 60, 2);
    }

    /**
     * Anggaran kos (RM per aksara).
     */
    public function EstimateCost(int $characters): float
    {
        return round($characters * (float) config('voice_studio.cost_rm_per_character', 0.00008), 6);
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array{success: bool, audio: ?string, duration: float, error: ?string}
     */
    protected function ralat(Response $response): array
    {
        $badan = $response->json();
        $mesej = $badan['detail']['message'] ?? $badan['error']['message'] ?? $badan['message'] ?? ('HTTP_'.$response->status());

        return ['success' => false, 'audio' => null, 'duration' => 0.0, 'error' => (string) $mesej];
    }

    /**
     * @return array{success: bool, track: VoiceTrack|null, audio: ?string, url: ?string, error: ?string}
     */
    protected function gagal(string $mesej, ?VoiceTrack $track = null): array
    {
        return [
            'success' => false,
            'track' => $track,
            'audio' => null,
            'url' => null,
            'error' => $mesej,
        ];
    }
}
