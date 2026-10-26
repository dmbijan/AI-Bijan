<?php

namespace Tests\Feature;

use App\Modules\Vision\Models\CctvCamera;
use App\Modules\Vision\Models\CctvEvent;
use App\Modules\Vision\Services\VisionAlertService;
use App\Modules\Vision\Services\VisionIngestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Amaran peristiwa CCTV (BAB 56C Fasa 2). */
class VisionAlertTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;
    private CctvCamera $kamera;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@kedai-a.my', 'Kedai A');
        $this->dalamTenant($this->t);

        $this->kamera = CctvCamera::create([
            'name' => 'Kamera Depan',
            'status' => 'aktif',
            'record_events' => true,
        ]);
    }

    private function peristiwa(string $jenis, float $keyakinan = 0.9): CctvEvent
    {
        return CctvEvent::create([
            'camera_id' => $this->kamera->id,
            'event_type' => $jenis,
            'confidence' => $keyakinan,
            'started_at' => now(),
        ]);
    }

    public function test_peristiwa_kritikal_sentiasa_amaran_walau_keyakinan_rendah(): void
    {
        $svc = app(VisionAlertService::class);

        $this->assertTrue($svc->patutAmaran($this->peristiwa('perimeter_breach', 0.3)));
        $this->assertTrue($svc->patutAmaran($this->peristiwa('after_hours', 0.2)));
    }

    public function test_peristiwa_biasa_perlu_keyakinan_tinggi(): void
    {
        $svc = app(VisionAlertService::class);

        $this->assertTrue($svc->patutAmaran($this->peristiwa('person', 0.9)));
        $this->assertFalse($svc->patutAmaran($this->peristiwa('person', 0.5)), 'Keyakinan rendah tidak patut amaran.');
    }

    public function test_kamera_tidak_rekod_tidak_amaran(): void
    {
        $this->kamera->update(['record_events' => false]);
        $svc = app(VisionAlertService::class);

        $this->assertFalse($svc->patutAmaran($this->peristiwa('perimeter_breach', 0.99)));
    }

    public function test_amaran_dihantar_dan_direkod(): void
    {
        $hasil = app(VisionAlertService::class)->amaran($this->peristiwa('perimeter_breach', 0.95)->load('camera'));

        $this->assertTrue($hasil['dihantar']);
        $this->assertContains('log', $hasil['saluran']);
    }

    public function test_had_kadar_menghalang_spam(): void
    {
        config(['services.vision.alerts_per_camera_per_hour' => 2]);
        $svc = app(VisionAlertService::class);

        $this->assertTrue($svc->amaran($this->peristiwa('perimeter_breach')->load('camera'))['dihantar']);
        $this->assertTrue($svc->amaran($this->peristiwa('perimeter_breach')->load('camera'))['dihantar']);

        $ketiga = $svc->amaran($this->peristiwa('perimeter_breach')->load('camera'));
        $this->assertFalse($ketiga['dihantar']);
        $this->assertSame('had_kadar', $ketiga['sebab']);
    }

    public function test_amaran_gagal_tidak_gagalkan_ingestion(): void
    {
        // Kunci config ke nilai tidak sah untuk cetus ralat dalam perkhidmatan amaran,
        // tetapi ingest mesti tetap berjaya.
        config(['services.vision.alert_log_channel' => 'saluran_tidak_wujud']);

        $e = app(VisionIngestService::class)->rekod($this->kamera, [
            'event_type' => 'perimeter_breach',
            'confidence' => 0.99,
            'started_at' => now(),
        ]);

        $this->assertNotNull($e->id);
        $this->assertSame(1, CctvEvent::count());
    }

    public function test_endpoint_amaran_tertunggak(): void
    {
        // Kritikal, 60 minit lalu, belum diakui.
        CctvEvent::create([
            'camera_id' => $this->kamera->id,
            'event_type' => 'perimeter_breach',
            'confidence' => 0.9,
            'started_at' => now()->subMinutes(60),
        ]);
        // Kritikal tetapi baru — tidak patut muncul dengan ambang 30 minit.
        $this->peristiwa('after_hours');
        // Sudah diakui — tidak patut muncul.
        CctvEvent::create([
            'camera_id' => $this->kamera->id,
            'event_type' => 'perimeter_breach',
            'confidence' => 0.9,
            'started_at' => now()->subHours(2),
            'acknowledged_at' => now(),
            'acknowledged_by' => $this->u->id,
        ]);

        $this->sebagai($this->u, $this->t);
        $r = $this->getJson('/api/v1/vision/alerts/pending?minutes=30')->assertOk()->json();

        $this->assertSame(30, $r['threshold_minutes']);
        $this->assertCount(1, $r['events']);
    }

    public function test_tenant_lain_tidak_nampak_amaran(): void
    {
        CctvEvent::create([
            'camera_id' => $this->kamera->id,
            'event_type' => 'perimeter_breach',
            'confidence' => 0.9,
            'started_at' => now()->subHour(),
        ]);

        [$ub, $tb] = $this->daftar('pemilik@kedai-b.my', 'Kedai B');
        $r = $this->sebagai($ub, $tb)->getJson('/api/v1/vision/alerts/pending')->assertOk()->json();

        $this->assertCount(0, $r['events']);
    }
}
