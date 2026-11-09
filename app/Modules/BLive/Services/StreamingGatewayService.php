<?php

namespace App\Modules\BLive\Services;

use App\Modules\BLive\Models\LiveSession;
use App\Modules\BLive\Models\StreamDestination;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * StreamingGatewayService - penghantaran video ke beberapa platform.
 *
 * Setiap platform ada encoder profile, credential, bitrate dan status
 * sendiri. Satu stream yang sama TIDAK dihantar secara membuta tuli -
 * setiap sasaran diurus secara berasingan.
 *
 * Platform:
 *   - youtube  : Official Live Streaming API + RTMP
 *   - facebook : Graph API / RTMPS
 *   - tiktok   : manual RTMP (akses LIVE API memerlukan kelayakan)
 *
 * Stream key dan token OAuth TIDAK disimpan dalam plaintext. Medan
 * `credential_reference` menunjuk kepada secrets vault.
 */
class StreamingGatewayService
{
    public function __construct(private TenantContext $ctx) {}

    /**
     * Platform yang disokong dan diaktifkan.
     *
     * @return array<string, array<string, mixed>>
     */
    public function platformAktif(): array
    {
        $aktif = [];

        foreach ((array) config('blive.platforms', []) as $kunci => $cfg) {
            if (($cfg['enabled'] ?? false) === true) {
                $aktif[$kunci] = $cfg;
            }
        }

        return $aktif;
    }

    /**
     * Sambung sasaran penstriman kepada sesi.
     *
     * @param  array<string, mixed>  $data
     * @return array{success: bool, destination: ?StreamDestination, error: ?string}
     */
    public function sambung(LiveSession $sesi, array $data): array
    {
        $platform = (string) ($data['platform'] ?? '');

        $cfg = config('blive.platforms.'.$platform);

        if (! is_array($cfg)) {
            return ['success' => false, 'destination' => null, 'error' => 'Platform tidak disokong: '.$platform];
        }

        if (($cfg['enabled'] ?? false) !== true) {
            return [
                'success' => false,
                'destination' => null,
                'error' => 'Platform '.$platform.' belum diaktifkan untuk akaun anda. Guna mod RTMP manual jika akaun mempunyai akses yang dibenarkan.',
            ];
        }

        $had = (int) config('blive.streaming.max_destinations_per_session', 3);

        if ($sesi->destinations()->count() >= $had) {
            return [
                'success' => false,
                'destination' => null,
                'error' => 'Had sasaran penstriman ('.$had.') telah dicapai untuk sesi ini.',
            ];
        }

        // Credential rujukan wajib - stream key tidak pernah disimpan di sini.
        if (empty($data['credential_reference'])) {
            return [
                'success' => false,
                'destination' => null,
                'error' => 'Rujukan kelayakan diperlukan. Simpan stream key dalam secrets vault dan berikan rujukan sahaja.',
            ];
        }

        $destination = StreamDestination::create([
            'uuid' => (string) Str::uuid(),
            'live_session_id' => $sesi->id,
            'platform' => $platform,
            'channel_name' => $data['channel_name'] ?? null,
            'channel_reference' => $data['channel_reference'] ?? null,
            'credential_reference' => $data['credential_reference'],
            'stream_key_reference' => $data['stream_key_reference'] ?? null,
            'ingest_url' => $data['ingest_url'] ?? null,
            'stream_status' => StreamDestination::STATUS_PENDING,
            'bitrate_kbps' => (int) ($data['bitrate_kbps'] ?? $sesi->bitrate_kbps),
            'resolution' => $data['resolution'] ?? $sesi->resolution,
            'orientation' => $data['orientation'] ?? $sesi->orientation,
            'fps' => (int) ($data['fps'] ?? $sesi->fps),
        ]);

        return ['success' => true, 'destination' => $destination, 'error' => null];
    }

    /**
     * Mulakan penstriman ke satu sasaran.
     */
    public function mula(StreamDestination $destination): array
    {
        $destination->update(['stream_status' => StreamDestination::STATUS_CONNECTING]);

        $cfg = config('blive.platforms.'.$destination->platform);

        if (! is_array($cfg)) {
            $destination->update([
                'stream_status' => StreamDestination::STATUS_FAILED,
                'last_error' => 'Platform tidak disokong.',
            ]);

            return ['success' => false, 'destination' => $destination, 'error' => 'Platform tidak disokong.'];
        }

        // Renderer sebenar (FFmpeg/RTMP) dikendalikan oleh worker luar.
        // Gateway hanya mengesahkan konfigurasi dan menanda status.
        if (empty($destination->ingest_url)) {
            $destination->update([
                'stream_status' => StreamDestination::STATUS_FAILED,
                'last_error' => 'Ingest URL tidak disediakan.',
            ]);

            return ['success' => false, 'destination' => $destination, 'error' => 'Ingest URL tidak disediakan.'];
        }

        $destination->update([
            'stream_status' => StreamDestination::STATUS_LIVE,
            'connected_at' => now(),
            'disconnected_at' => null,
            'last_error' => null,
        ]);

        Log::info('BLive: sasaran penstriman disambung', [
            'destination_id' => $destination->id,
            'platform' => $destination->platform,
            'driver' => $cfg['driver'] ?? 'rtmp',
        ]);

        return ['success' => true, 'destination' => $destination->fresh(), 'error' => null];
    }

    /**
     * Hentikan penstriman ke satu sasaran.
     */
    public function henti(StreamDestination $destination, ?string $sebab = null): array
    {
        $destination->update([
            'stream_status' => StreamDestination::STATUS_DISCONNECTED,
            'disconnected_at' => now(),
            'last_error' => $sebab,
        ]);

        return ['success' => true, 'destination' => $destination->fresh(), 'error' => null];
    }

    /**
     * Putuskan semua sasaran semasa sesi dihentikan.
     */
    public function hentiSemua(LiveSession $sesi): int
    {
        $jumlah = 0;

        foreach ($sesi->destinations as $destination) {
            if ($destination->menyiarkan() || $destination->stream_status === StreamDestination::STATUS_CONNECTING) {
                $this->henti($destination, 'Sesi dihentikan.');
                $jumlah++;
            }
        }

        return $jumlah;
    }

    /**
     * Status penstriman untuk satu sesi.
     *
     * @return array<int, array<string, mixed>>
     */
    public function status(LiveSession $sesi): array
    {
        return $sesi->destinations->map(fn (StreamDestination $d) => [
            'id' => $d->id,
            'platform' => $d->platform,
            'label' => config('blive.platforms.'.$d->platform.'.label', $d->platform),
            'channel' => $d->channel_name,
            'status' => $d->stream_status,
            'resolution' => $d->resolution,
            'bitrate_kbps' => $d->bitrate_kbps,
            'fps' => $d->fps,
            'connected_at' => $d->connected_at,
            'reconnect_count' => $d->reconnect_count,
            'last_error' => $d->last_error,
        ])->all();
    }

    /**
     * Cuba sambung semula sasaran yang terputus.
     */
    public function sambungSemula(StreamDestination $destination): array
    {
        $maks = (int) config('blive.streaming.reconnect_attempts', 3);

        if ($destination->reconnect_count >= $maks) {
            return [
                'success' => false,
                'destination' => $destination,
                'error' => 'Had percubaan sambung semula ('.$maks.') telah dicapai.',
            ];
        }

        $destination->increment('reconnect_count');

        return $this->mula($destination);
    }
}
