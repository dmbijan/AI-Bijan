<?php

namespace App\Modules\BLive\Services;

use App\Modules\BLive\Models\Avatar;
use App\Modules\BLive\Models\LiveMetric;
use App\Modules\BLive\Models\LiveSession;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * LiveSessionService - pengurusan kitaran hidup sesi live.
 *
 * Mula / henti sesi memerlukan avatar yang layak (consent granted,
 * avatar ready). Ini pagar yang menentukan sama ada hos digital
 * boleh dipaparkan kepada penonton awam atau tidak.
 */
class LiveSessionService
{
    public function __construct(
        private TenantContext $ctx,
        private AvatarService $avatarService,
    ) {}

    /**
     * Senarai sesi.
     *
     * @param  array<string, mixed>  $filter
     * @return Collection<int, LiveSession>
     */
    public function senarai(array $filter = [])
    {
        $query = LiveSession::query()->orderByDesc('created_at');

        if (! empty($filter['status'])) {
            $query->where('status', $filter['status']);
        }

        if (! empty($filter['avatar_id'])) {
            $query->where('avatar_id', (int) $filter['avatar_id']);
        }

        return $query->limit((int) ($filter['limit'] ?? 100))->get();
    }

    /**
     * Cipta sesi live.
     *
     * @param  array<string, mixed>  $data
     * @return array{success: bool, session: ?LiveSession, error: ?string}
     */
    public function cipta(array $data): array
    {
        $had = (int) config('blive.limits.max_concurrent_live_sessions', 3);

        $aktif = LiveSession::query()
            ->whereIn('status', [LiveSession::STATUS_LIVE, LiveSession::STATUS_PAUSED])
            ->count();

        if ($aktif >= $had) {
            return [
                'success' => false,
                'session' => null,
                'error' => 'Had sesi live serentak ('.$had.') telah dicapai.',
            ];
        }

        $sesi = LiveSession::create([
            'uuid' => (string) Str::uuid(),
            'avatar_id' => $data['avatar_id'] ?? null,
            'host_personality_id' => $data['host_personality_id'] ?? null,
            'title' => $data['title'] ?? 'Live Session',
            'session_mode' => $data['session_mode'] ?? LiveSession::MODE_AI_ASSISTED,
            'autonomy_level' => $data['autonomy_level'] ?? LiveSession::AUTONOMY_SUPERVISED,
            'status' => ! empty($data['scheduled_at']) ? LiveSession::STATUS_SCHEDULED : LiveSession::STATUS_DRAFT,
            'resolution' => $data['resolution'] ?? config('blive.streaming.default_resolution'),
            'orientation' => $data['orientation'] ?? 'landscape',
            'fps' => (int) ($data['fps'] ?? config('blive.streaming.default_fps')),
            'bitrate_kbps' => (int) ($data['bitrate_kbps'] ?? config('blive.streaming.default_bitrate_kbps')),
            'language' => $data['language'] ?? 'ms',
            'product_ids' => $data['product_ids'] ?? [],
            'settings' => $data['settings'] ?? [],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return ['success' => true, 'session' => $sesi, 'error' => null];
    }

    /**
     * Mulakan sesi live.
     *
     * @return array{success: bool, session: ?LiveSession, error: ?string, code: ?string}
     */
    public function mula(LiveSession $sesi): array
    {
        if ((int) $sesi->tenant_id !== (int) $this->ctx->id()) {
            return [
                'success' => false,
                'session' => $sesi,
                'error' => 'Sesi ini tidak dimiliki oleh syarikat anda.',
                'code' => 'tenant_mismatch',
            ];
        }

        if (! $sesi->bolehMula()) {
            return [
                'success' => false,
                'session' => $sesi,
                'error' => 'Sesi tidak berada dalam status yang boleh dimulakan.',
                'code' => 'invalid_state',
            ];
        }

        if ($sesi->avatar_id) {
            $avatar = Avatar::find($sesi->avatar_id);

            if ($avatar === null) {
                return [
                    'success' => false,
                    'session' => $sesi,
                    'error' => 'Avatar tidak dijumpai.',
                    'code' => 'avatar_missing',
                ];
            }

            $semak = $this->avatarService->semakKelayakan($avatar);

            if (! $semak['ok']) {
                return [
                    'success' => false,
                    'session' => $sesi,
                    'error' => $semak['reason'],
                    'code' => $semak['code'],
                ];
            }
        }

        $sesi->update([
            'status' => LiveSession::STATUS_LIVE,
            'started_at' => $sesi->started_at ?: now(),
            'ended_at' => null,
        ]);

        return ['success' => true, 'session' => $sesi->fresh(), 'error' => null, 'code' => null];
    }

    /**
     * Hentikan sesi live.
     *
     * @return array{success: bool, session: ?LiveSession, error: ?string, code: ?string}
     */
    public function henti(LiveSession $sesi): array
    {
        if ((int) $sesi->tenant_id !== (int) $this->ctx->id()) {
            return [
                'success' => false,
                'session' => $sesi,
                'error' => 'Sesi ini tidak dimiliki oleh syarikat anda.',
                'code' => 'tenant_mismatch',
            ];
        }

        if (! $sesi->aktif()) {
            return [
                'success' => false,
                'session' => $sesi,
                'error' => 'Sesi tidak aktif.',
                'code' => 'invalid_state',
            ];
        }

        $sesi->update([
            'status' => LiveSession::STATUS_ENDED,
            'ended_at' => now(),
        ]);

        $this->catatMetrik($sesi, 'render_minutes', $sesi->tempohMenit());
        $this->catatMetrik($sesi, 'speech_count', (float) $sesi->speech_count);

        return ['success' => true, 'session' => $sesi->fresh(), 'error' => null, 'code' => null];
    }

    /**
     * Catat metrik analytics.
     */
    public function catatMetrik(LiveSession $sesi, string $kunci, float $nilai, ?string $platform = null): LiveMetric
    {
        return LiveMetric::create([
            'live_session_id' => $sesi->id,
            'metric_key' => $kunci,
            'metric_scope' => $platform ? LiveMetric::SCOPE_DESTINATION : LiveMetric::SCOPE_SESSION,
            'value_numeric' => (int) round($nilai),
            'value_decimal' => $nilai,
            'platform' => $platform,
            'recorded_on' => now()->toDateString(),
        ]);
    }

    /**
     * Ringkasan analytics untuk satu sesi.
     *
     * @return array<string, mixed>
     */
    public function ringkasan(LiveSession $sesi): array
    {
        $metrik = LiveMetric::query()
            ->where('live_session_id', $sesi->id)
            ->get()
            ->groupBy('metric_key');

        $nilai = [];

        foreach ($metrik as $kunci => $baris) {
            $nilai[$kunci] = (float) $baris->sum('value_decimal');
        }

        return [
            'session' => [
                'id' => $sesi->id,
                'uuid' => $sesi->uuid,
                'title' => $sesi->title,
                'status' => $sesi->status,
                'session_mode' => $sesi->session_mode,
                'started_at' => $sesi->started_at,
                'ended_at' => $sesi->ended_at,
                'tempoh_minit' => $sesi->tempohMenit(),
            ],
            'metrics' => $nilai,
            'destinations' => $sesi->destinations->map(fn ($d) => [
                'platform' => $d->platform,
                'status' => $d->stream_status,
                'channel' => $d->channel_name,
            ])->all(),
        ];
    }
}
