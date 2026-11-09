<?php

namespace App\Modules\BLive\Services;

use App\Modules\BLive\Models\Avatar;
use App\Modules\BLive\Models\RenderJob;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * AvatarEngineService - abstraksi enjin render avatar.
 *
 * B-Live tidak membina enjin avatar sendiri. Ia bercakap dengan enjin
 * luaran melalui kontrak HTTP yang stabil:
 *
 *   POST {base_url}/avatars        -> { "job_id": "...", "status": "queued" }
 *   GET  {base_url}/avatars/{job}  -> { "status": "...", "output": {...} }
 *
 * Enjin `simulated` menghasilkan manifest deterministik (tanpa GPU)
 * supaya keseluruhan aliran boleh diuji dan demo tanpa pensijilan
 * MetaHuman / lesen GPU.
 */
class AvatarEngineService
{
    /**
     * Jana avatar untuk enjin yang dikonfigurasi.
     *
     * @param  array<string, mixed>  $specification
     * @return array{success: bool, job: ?RenderJob, error: ?string}
     */
    public function jana(Avatar $avatar, array $specification = []): array
    {
        $engine = $avatar->engine ?: (string) config('blive.avatar_engine', 'simulated');
        $cfg = config('blive.engines.'.$engine);

        if (! is_array($cfg)) {
            return [
                'success' => false,
                'job' => null,
                'error' => 'Enjin avatar tidak dikonfigurasi: '.$engine,
            ];
        }

        $job = RenderJob::create([
            'uuid' => (string) Str::uuid(),
            'avatar_id' => $avatar->id,
            'job_type' => RenderJob::TYPE_AVATAR_GENERATE,
            'engine' => $engine,
            'status' => RenderJob::STATUS_QUEUED,
            'input_payload' => [
                'name' => $avatar->name,
                'avatar_type' => $avatar->avatar_type,
                'appearance' => $avatar->appearance,
                'body_traits' => $avatar->body_traits,
                'render_quality' => $avatar->render_quality,
            ] + $specification,
        ]);

        $avatar->update([
            'status' => Avatar::STATUS_GENERATING,
            'engine' => $engine,
        ]);

        try {
            $hasil = ($cfg['driver'] ?? 'simulated') === 'simulated'
                ? $this->simulated($avatar, $job)
                : $this->http($avatar, $job, $cfg);
        } catch (Throwable $e) {
            Log::error('BLive: janaan avatar gagal', [
                'avatar_id' => $avatar->id,
                'engine' => $engine,
                'error' => $e->getMessage(),
            ]);

            $job->update([
                'status' => RenderJob::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            $avatar->update(['status' => Avatar::STATUS_FAILED]);

            return ['success' => false, 'job' => $job, 'error' => $e->getMessage()];
        }

        if (! $hasil['success']) {
            $job->update([
                'status' => RenderJob::STATUS_FAILED,
                'error_message' => $hasil['error'],
                'completed_at' => now(),
            ]);

            $avatar->update(['status' => Avatar::STATUS_FAILED]);

            return ['success' => false, 'job' => $job, 'error' => $hasil['error']];
        }

        $job->update([
            'status' => RenderJob::STATUS_COMPLETED,
            'output_payload' => $hasil['output'],
            'progress_percent' => 100,
            'started_at' => $job->started_at ?: now(),
            'completed_at' => now(),
        ]);

        $avatar->update([
            'status' => Avatar::STATUS_READY,
            'model_reference' => $hasil['output']['model_reference'] ?? $avatar->model_reference,
            'asset_storage_path' => $hasil['output']['asset_storage_path'] ?? $avatar->asset_storage_path,
            'generated_at' => now(),
        ]);

        return ['success' => true, 'job' => $job, 'error' => null];
    }

    /**
     * Enjin simulasi - manifest deterministik, tiada GPU diperlukan.
     *
     * @return array{success: bool, output: array<string, mixed>, error: ?string}
     */
    protected function simulated(Avatar $avatar, RenderJob $job): array
    {
        $benih = crc32($avatar->uuid.':'.$avatar->name.':'.$avatar->asset_version);

        $output = [
            'model_reference' => 'sim://avatar/'.$avatar->uuid.'/v'.$avatar->asset_version,
            'asset_storage_path' => 'simulated/avatars/'.$avatar->uuid.'/v'.$avatar->asset_version,
            'facial_profile' => 'sim_face_'.substr(hash('sha256', (string) $benih), 0, 12),
            'animation_profile' => 'sim_anim_'.substr(hash('sha256', (string) ($benih + 1)), 0, 12),
            'render_quality' => $avatar->render_quality,
            'checksum' => hash('sha256', $avatar->uuid.'|'.$avatar->asset_version.'|'.$benih),
            'generator' => 'simulated',
        ];

        return ['success' => true, 'output' => $output, 'error' => null];
    }

    /**
     * Enjin HTTP luaran (MetaHuman atau renderer tersuai).
     *
     * @param  array<string, mixed>  $cfg
     * @return array{success: bool, output: array<string, mixed>, error: ?string}
     */
    protected function http(Avatar $avatar, RenderJob $job, array $cfg): array
    {
        $base = rtrim((string) ($cfg['base_url'] ?? ''), '/');

        if ($base === '') {
            return ['success' => false, 'output' => [], 'error' => 'base_url enjin avatar tidak dikonfigurasi.'];
        }

        $job->update(['status' => RenderJob::STATUS_RUNNING, 'started_at' => now()]);

        $response = Http::timeout((int) ($cfg['timeout_seconds'] ?? 120))
            ->withHeaders(array_filter([
                'Authorization' => isset($cfg['api_key']) ? 'Bearer '.$cfg['api_key'] : null,
                'X-Idempotency-Key' => $job->uuid,
            ]))
            ->post($base.'/avatars', [
                'name' => $avatar->name,
                'avatar_type' => $avatar->avatar_type,
                'appearance' => $avatar->appearance,
                'body_traits' => $avatar->body_traits,
                'render_quality' => $avatar->render_quality,
                'external_id' => $avatar->uuid,
            ]);

        if ($response->failed()) {
            return [
                'success' => false,
                'output' => [],
                'error' => 'Enjin avatar menolak permintaan (HTTP '.$response->status().'): '.$response->body(),
            ];
        }

        $payload = $response->json();
        $remoteJobId = $payload['job_id'] ?? $payload['id'] ?? null;

        if ($remoteJobId === null) {
            return ['success' => false, 'output' => [], 'error' => 'Enjin avatar tidak mengembalikan job_id.'];
        }

        $hasil = $this->tunggu($base, (string) $remoteJobId, (int) ($cfg['poll_interval_seconds'] ?? 5), (int) ($cfg['timeout_seconds'] ?? 120));

        if (! $hasil['success']) {
            return ['success' => false, 'output' => [], 'error' => $hasil['error']];
        }

        return ['success' => true, 'output' => $hasil['output'], 'error' => null];
    }

    /**
     * Tunggu sehingga kerjaan enjin siap.
     *
     * @return array{success: bool, output: array<string, mixed>, error: ?string}
     */
    protected function tunggu(string $base, string $remoteJobId, int $pollSeconds, int $timeoutSeconds): array
    {
        $deadline = microtime(true) + $timeoutSeconds;

        while (microtime(true) < $deadline) {
            sleep(max(1, $pollSeconds));

            $response = Http::timeout(30)->get($base.'/avatars/'.$remoteJobId);

            if ($response->failed()) {
                continue;
            }

            $payload = $response->json();
            $status = $payload['status'] ?? 'unknown';

            if ($status === 'completed' || $status === 'succeeded') {
                return ['success' => true, 'output' => $payload['output'] ?? $payload, 'error' => null];
            }

            if ($status === 'failed' || $status === 'error') {
                return [
                    'success' => false,
                    'output' => [],
                    'error' => 'Enjin avatar melaporkan kegagalan: '.($payload['error'] ?? 'tidak diketahui'),
                ];
            }
        }

        return ['success' => false, 'output' => [], 'error' => 'Enjin avatar tamat masa selepas '.$timeoutSeconds.' saat.'];
    }
}
