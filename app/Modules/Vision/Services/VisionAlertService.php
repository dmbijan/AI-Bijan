<?php

namespace App\Modules\Vision\Services;

use App\Modules\Vision\Models\CctvCamera;
use App\Modules\Vision\Models\CctvEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Amaran peristiwa CCTV (BAB 56C Fasa 2).
 *
 * Peraturan reka bentuk:
 * - Hanya peristiwa KRITIKAL atau yang belum diakui melebihi tempoh dihantar.
 * - Amaran TIDAK boleh menyebabkan kegagalan ingestion — semua ralat ditelan
 *   dan dilog. Ingest mesti berjaya walaupun saluran amaran mati.
 * - Kadar dihadkan per kamera per jam untuk elak spam.
 */
class VisionAlertService
{
    /** Kunci cache untuk had kadar. */
    private function kunciKadar(int $cameraId): string
    {
        return "vision:alert:camera:{$cameraId}:".now()->format('YmdH');
    }

    /**
     * Tentukan sama ada peristiwa patut dicetuskan amaran.
     */
    public function patutAmaran(CctvEvent $e): bool
    {
        if (! $e->camera?->record_events) {
            return false;
        }

        // Peristiwa kritikal sentiasa dihantar.
        if (in_array($e->event_type, CctvEvent::KRITIKAL, true)) {
            return true;
        }

        // Keyakinan rendah tidak dihantar — elak positif palsu.
        return (float) $e->confidence >= (float) config('services.vision.alert_min_confidence', 0.8);
    }

    /**
     * Hantar amaran jika layak. Selamat: tidak pernah membaling exception.
     *
     * @return array{dihantar: bool, sebab?: string, saluran: array<int, string>}
     */
    public function amaran(CctvEvent $e): array
    {
        try {
            if (! $this->patutAmaran($e)) {
                return ['dihantar' => false, 'sebab' => 'tidak_layak', 'saluran' => []];
            }

            // Had kadar: maksimum N amaran per kamera per jam.
            $had = (int) config('services.vision.alerts_per_camera_per_hour', 10);
            $kunci = $this->kunciKadar($e->camera_id);
            $bil = (int) cache()->get($kunci, 0);

            if ($bil >= $had) {
                return ['dihantar' => false, 'sebab' => 'had_kadar', 'saluran' => []];
            }
            cache()->put($kunci, $bil + 1, now()->addHour());

            // Saluran diaktifkan melalui config; tiada saluran = hanya log.
            $saluran = $this->hantar($e);
            cache()->put("vision:last_alert:{$e->id}", now()->toIso8601String(), now()->addDay());

            return ['dihantar' => true, 'saluran' => $saluran];
        } catch (\Throwable $ex) {
            // JANGAN gagalkan ingestion kerana amaran.
            Log::warning('Vision alert gagal', [
                'event_id' => $e->id,
                'error' => $ex->getMessage(),
            ]);

            return ['dihantar' => false, 'sebab' => 'ralat: '.$ex->getMessage(), 'saluran' => []];
        }
    }

    /**
     * Saluran amaran. Dilaksanakan sebagai titik sambungan — B-Notify belum
     * mempunyai pemacu WhatsApp/SMS sebenar, jadi buat masa ini hanya log.
     *
     * @return array<int, string>
     */
    private function hantar(CctvEvent $e): array
    {
        $saluran = [];

        // Log struktur — boleh ditelan oleh pemantau log.
        Log::channel(config('services.vision.alert_log_channel', 'single'))->warning('CCTV alert', [
            'tenant_id' => $e->tenant_id,
            'camera' => $e->camera?->name,
            'event_type' => $e->event_type,
            'confidence' => (string) $e->confidence,
            'started_at' => $e->started_at?->toIso8601String(),
            'clip' => $e->clip_path,
        ]);
        $saluran[] = 'log';

        // Titik sambungan: bila B-Notify ada pemacu sebenar, daftar di sini.
        if (config('services.vision.alert_via_notify', false)) {
            // TODO: hantar melalui B-Notify (WhatsApp/SMS/push) setelah pemacu wujud.
            $saluran[] = 'notify_placeholder';
        }

        return $saluran;
    }

    /** Peristiwa kritikal yang belum diakui melebihi N minit. */
    public function belumDiakui(int $minit = 30): array
    {
        return CctvEvent::with('camera:id,name')
            ->whereNull('acknowledged_at')
            ->whereIn('event_type', CctvEvent::KRITIKAL)
            ->where('started_at', '<=', now()->subMinutes($minit))
            ->orderBy('started_at')
            ->get()
            ->toArray();
    }
}
