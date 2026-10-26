<?php

namespace App\Modules\Vision\Services;

use App\Modules\Vision\Models\CctvCamera;
use App\Modules\Vision\Models\CctvEvent;
use App\Modules\Vision\Models\CctvRetention;
use App\Modules\Vision\Models\CctvViewLog;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Ingestion peristiwa dari Vision Service (Frigate/YOLO) ke AI BIJAN.
 *
 * Laravel TIDAK memproses video — ia hanya menerima metadata peristiwa.
 * Webhook ditandatangani HMAC-SHA256 dengan kunci per tenant.
 */
class VisionIngestService
{
    /** Sahkan tandatangan HMAC webhook. */
    public function sahkanTandatangan(string $body, ?string $tandatangan, string $rahsia): bool
    {
        if (blank($tandatangan)) {
            return false;
        }

        $jangkaan = hash_hmac('sha256', $body, $rahsia);

        return hash_equals($jangkaan, $tandatangan);
    }

    /**
     * Rekod peristiwa. Idempoten melalui `external_id` jika diberi
     * (diperiksa oleh pemanggil melalui klip_path + started_at).
     *
     * @throws ValidationException
     */
    public function rekod(CctvCamera $kamera, array $d): CctvEvent
    {
        if (in_array($d['event_type'], ['face', 'face_match', 'identity'], true)) {
            throw ValidationException::withMessages([
                'event_type' => ['Pengenalan wajah tidak diterima melalui laluan ini. Gunakan pagar biometrik BAB 56C.6A.'],
            ]);
        }

        if (! in_array($d['event_type'], CctvEvent::JENIS, true)) {
            throw ValidationException::withMessages([
                'event_type' => ['Jenis peristiwa tidak sah.'],
            ]);
        }

        return DB::transaction(function () use ($kamera, $d) {
            $e = CctvEvent::create([
                'camera_id' => $kamera->id,
                'event_type' => $d['event_type'],
                'confidence' => $d['confidence'] ?? 0,
                'started_at' => $d['started_at'] ?? now(),
                'ended_at' => $d['ended_at'] ?? null,
                'clip_path' => $d['clip_path'] ?? null,
                'thumbnail_path' => $d['thumbnail_path'] ?? null,
            ]);

            $kamera->forceFill(['last_seen_at' => now()])->save();

            app(AuditLogger::class)->log('vision.event_received', $e, null, [
                'camera_id' => $kamera->id,
                'event_type' => $e->event_type,
                'confidence' => (string) $e->confidence,
            ]);

            // Amaran selepas commit tidak diperlukan — perkhidmatan amaran
            // menelan semua ralat supaya ingestion tidak pernah gagal kerananya.
            app(VisionAlertService::class)->amaran($e->load('camera'));

            return $e;
        });
    }

    /** Rekod tontonan (audit wajib). */
    public function logTontonan(?int $cameraId, ?int $eventId, ?int $userId, string $action, ?string $ip, ?string $purpose = null): CctvViewLog
    {
        return CctvViewLog::create([
            'camera_id' => $cameraId,
            'event_id' => $eventId,
            'user_id' => $userId,
            'action' => $action,
            'ip_address' => $ip,
            'purpose' => $purpose,
        ]);
    }

    /** Tetapkan polisi retensi untuk kamera. */
    public function tetapkanRetensi(CctvCamera $kamera, int $hari): CctvRetention
    {
        $had = CctvRetention::HAD_HARI;
        if ($hari < $had['min'] || $hari > $had['max']) {
            throw ValidationException::withMessages([
                'days' => ["Retensi mesti antara {$had['min']} dan {$had['max']} hari."],
            ]);
        }

        return CctvRetention::updateOrCreate(
            ['camera_id' => $kamera->id],
            ['days' => $hari]
        );
    }

    /**
     * Ringkasan papan pemuka berbilang cawangan.
     *
     * @return array<string, mixed>
     */
    public function ringkasan(int $hariTerakhir = 7): array
    {
        $sejak = now()->subDays($hariTerakhir);

        return [
            'cameras' => [
                'jumlah' => CctvCamera::count(),
                'aktif' => CctvCamera::where('status', 'aktif')->count(),
                'rosak' => CctvCamera::where('status', 'rosak')->count(),
            ],
            'events' => [
                'jumlah' => CctvEvent::where('started_at', '>=', $sejak)->count(),
                'belum_diakui' => CctvEvent::whereNull('acknowledged_at')->count(),
                'kritikal_belum_diakui' => CctvEvent::whereNull('acknowledged_at')
                    ->whereIn('event_type', CctvEvent::KRITIKAL)->count(),
            ],
            'mengikut_jenis' => CctvEvent::where('started_at', '>=', $sejak)
                ->selectRaw('event_type, COUNT(*) as jumlah')
                ->groupBy('event_type')
                ->pluck('jumlah', 'event_type')
                ->toArray(),
            'tempoh_hari' => $hariTerakhir,
        ];
    }
}
