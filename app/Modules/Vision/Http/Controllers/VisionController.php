<?php

namespace App\Modules\Vision\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Vision\Models\CctvCamera;
use App\Modules\Vision\Models\CctvEvent;
use App\Modules\Vision\Services\VisionIngestService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * B-Vision Fasa 1 — daftar kamera, peristiwa, retensi, audit tontonan.
 *
 * Tiada strim video diproses di sini. Klip/thumbnail ialah rujukan storan.
 */
class VisionController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$request->tenant_id` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    public function __construct(private VisionIngestService $svc) {}

    // ---------------- Kamera ----------------

    public function cameras(Request $r)
    {
        $q = CctvCamera::query()
            ->when($r->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($r->query('branch_id'), fn ($q, $b) => $q->where('branch_id', $b))
            ->orderBy('name');

        return response()->json($q->get()->map(fn ($c) => $c->toApiArray())->values());
    }

    public function showCamera(int $id)
    {
        $c = CctvCamera::with('retention')->findOrFail($id);

        return response()->json($c->toApiArray() + [
            'retention_days' => $c->retention?->days,
        ]);
    }

    public function storeCamera(Request $r)
    {
        $d = $this->validasiKamera($r);
        $c = new CctvCamera(collect($d)->except(['rtsp_url', 'retention_days'])->all());
        $c->setRtsp($d['rtsp_url'] ?? null);
        $c->save();

        if (! empty($d['retention_days'])) {
            $this->svc->tetapkanRetensi($c, (int) $d['retention_days']);
        }

        app(AuditLogger::class)->log('vision.camera_created', $c, null, ['name' => $c->name]);

        return response()->json($c->toApiArray(), 201);
    }

    public function updateCamera(Request $r, int $id)
    {
        $c = CctvCamera::findOrFail($id);
        $sebelum = $c->toApiArray();
        $d = $this->validasiKamera($r, $id);

        $c->fill(collect($d)->except(['rtsp_url', 'retention_days'])->all());
        if (array_key_exists('rtsp_url', $d)) {
            $c->setRtsp($d['rtsp_url']);
        }
        $c->save();

        if (! empty($d['retention_days'])) {
            $this->svc->tetapkanRetensi($c, (int) $d['retention_days']);
        }

        app(AuditLogger::class)->log('vision.camera_updated', $c, $sebelum, ['name' => $c->name]);

        return response()->json($c->toApiArray());
    }

    /** Kamera tidak dipadam — ditanda rosak/tidak aktif supaya peristiwa lama kekal. */
    public function destroyCamera(int $id)
    {
        $c = CctvCamera::findOrFail($id);
        $c->update(['status' => 'tidak_aktif']);
        app(AuditLogger::class)->log('vision.camera_deactivated', $c);

        return response()->json(['message' => 'Kamera dinyahaktifkan (peristiwa lama dikekalkan).']);
    }

    /** Ujian sambungan — sahkan URL RTSP diterima oleh Vision Service. */
    public function testCamera(Request $r, int $id)
    {
        $c = CctvCamera::findOrFail($id);

        if (blank($c->rtsp())) {
            return response()->json(['ok' => false, 'message' => 'Kamera tiada URL strim.'], 422);
        }

        // Fasa 1: sahkan format sahaja. Ping RTSP sebenar dilakukan oleh Vision Service.
        $sah = (bool) preg_match('#^rtsps?://[^\s]+$#i', $c->rtsp());

        if ($sah) {
            $c->forceFill(['last_seen_at' => now()])->save();
        }

        return response()->json([
            'ok' => $sah,
            'message' => $sah ? 'Format URL sah. Vision Service akan mengesahkan sambungan sebenar.' : 'Format URL tidak sah.',
        ], $sah ? 200 : 422);
    }

    // ---------------- Peristiwa ----------------

    public function events(Request $r)
    {
        $q = CctvEvent::with('camera:id,name,location')
            ->when($r->query('camera_id'), fn ($q, $c) => $q->where('camera_id', $c))
            ->when($r->query('event_type'), fn ($q, $t) => $q->where('event_type', $t))
            ->when($r->query('unacknowledged'), fn ($q) => $q->whereNull('acknowledged_at'))
            ->when($r->query('from'), fn ($q, $d) => $q->where('started_at', '>=', $d))
            ->when($r->query('to'), fn ($q, $d) => $q->where('started_at', '<=', $d))
            ->orderByDesc('started_at');

        return response()->json($q->paginate((int) $r->query('per_page', 50)));
    }

    public function acknowledgeEvent(Request $r, int $id)
    {
        $e = CctvEvent::findOrFail($id);

        abort_if($e->sudahDiakui(), 409, 'Peristiwa sudah diakui.');

        $d = $r->validate(['note' => 'nullable|string|max:500']);
        $e->update([
            'acknowledged_by' => $r->user()->id,
            'acknowledged_at' => now(),
            'note' => $d['note'] ?? null,
        ]);

        app(AuditLogger::class)->log('vision.event_acknowledged', $e, null, ['event_type' => $e->event_type]);

        return response()->json($e);
    }

    /** Webhook dari Vision Service (HMAC). Tiada sesi pengguna. */
    public function ingest(Request $r, int $cameraId)
    {
        // Webhook tiada sesi: cari kamera tanpa tenant scope, kemudian tetapkan konteks.
        $kamera = CctvCamera::withoutGlobalScope('tenant')->find($cameraId);
        abort_unless($kamera, 404);

        $tenant = \App\Models\Tenant::find($kamera->tenant_id);
        abort_unless($tenant && $tenant->isUsable(), 403, 'Akaun syarikat tidak aktif.');

        app(\App\Support\Tenancy\TenantContext::class)->set($tenant);

        $rahsia = config('services.vision.webhook_secret');
        abort_if(blank($rahsia), 503, 'Kunci webhook Vision belum dikonfigurasi.');

        $body = $r->getContent();
        abort_unless(
            $this->svc->sahkanTandatangan($body, $r->header('X-Vision-Signature'), $rahsia),
            401,
            'Tandatangan webhook tidak sah.'
        );

        $d = $r->validate([
            'event_type' => ['required', 'string', Rule::in(CctvEvent::JENIS)],
            'confidence' => 'nullable|numeric|min:0|max:1',
            'started_at' => 'nullable|date',
            'ended_at' => 'nullable|date|after_or_equal:started_at',
            'clip_path' => 'nullable|string|max:255',
            'thumbnail_path' => 'nullable|string|max:255',
        ]);

        $e = $this->svc->rekod($kamera, $d);

        return response()->json(['id' => $e->id, 'event_type' => $e->event_type], 201);
    }

    // ---------------- Retensi & audit ----------------

    public function setRetention(Request $r, int $cameraId)
    {
        $c = CctvCamera::findOrFail($cameraId);
        $d = $r->validate(['days' => 'required|integer|min:1|max:90']);

        return response()->json($this->svc->tetapkanRetensi($c, (int) $d['days']));
    }

    /** Log tontonan — wajib sebelum klien memainkan klip. */
    public function logView(Request $r)
    {
        $d = $r->validate([
            'camera_id' => 'nullable|integer|exists:cctv_cameras,id',
            'event_id' => 'nullable|integer|exists:cctv_events,id',
            'action' => 'required|in:view_live,view_clip,export',
            'purpose' => 'nullable|string|max:100',
        ]);

        return response()->json($this->svc->logTontonan(
            $d['camera_id'] ?? null,
            $d['event_id'] ?? null,
            $r->user()->id,
            $d['action'],
            $r->ip(),
            $d['purpose'] ?? null
        ), 201);
    }

    public function viewLogs(Request $r)
    {
        $q = \App\Modules\Vision\Models\CctvViewLog::query()
            ->when($r->query('camera_id'), fn ($q, $c) => $q->where('camera_id', $c))
            ->when($r->query('event_id'), fn ($q, $e) => $q->where('event_id', $e))
            ->orderByDesc('id');

        return response()->json($q->paginate((int) $r->query('per_page', 50)));
    }

    public function dashboard(Request $r)
    {
        return response()->json($this->svc->ringkasan((int) $r->query('days', 7)));
    }

    /** Peristiwa kritikal belum diakui melebihi N minit (Fasa 2). */
    public function belumDiakui(Request $r)
    {
        $minit = (int) $r->query('minutes', 30);

        return response()->json([
            'threshold_minutes' => $minit,
            'events' => app(\App\Modules\Vision\Services\VisionAlertService::class)->belumDiakui($minit),
        ]);
    }

    private function validasiKamera(Request $r, ?int $abaikanId = null): array
    {
        return $r->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('cctv_cameras', 'name')->ignore($abaikanId)],
            'location' => 'nullable|string|max:200',
            'branch_id' => 'nullable|integer|min:1',
            'rtsp_url' => 'nullable|string|max:1000',
            'status' => 'sometimes|in:aktif,tidak_aktif,rosak',
            'record_events' => 'sometimes|boolean',
            'retention_days' => 'sometimes|integer|min:1|max:90',
        ]);
    }
}
