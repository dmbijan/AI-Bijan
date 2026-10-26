<?php

namespace Tests\Feature;

use App\Modules\Vision\Models\CctvCamera;
use App\Modules\Vision\Models\CctvEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** B-Vision Fasa 1 (BAB 56C) — kamera, peristiwa, retensi, audit. */
class VisionTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@kedai-a.my', 'Kedai A');
        config(['services.vision.webhook_secret' => 'rahsia-ujian-123']);
    }

    private function kamera(array $ganti = []): array
    {
        $this->sebagai($this->u, $this->t);

        return $this->postJson('/api/v1/vision/cameras', array_merge([
            'name' => 'Kamera Depan',
            'location' => 'Pintu masuk',
            'rtsp_url' => 'rtsp://192.168.1.10:554/stream1',
            'retention_days' => 14,
        ], $ganti))->assertCreated()->json();
    }

    public function test_daftar_kamera_tidak_dedahkan_rtsp(): void
    {
        $c = $this->kamera();

        $this->assertSame('Kamera Depan', $c['name']);
        $this->assertTrue($c['has_stream']);
        $this->assertArrayNotHasKey('rtsp_url', $c);
        $this->assertArrayNotHasKey('rtsp_url_encrypted', $c);
    }

    public function test_rtsp_disimpan_berenkripsi(): void
    {
        $c = $this->kamera();

        $this->dalamTenant($this->t);
        $mentah = \DB::table('cctv_cameras')->where('id', $c['id'])->value('rtsp_url_encrypted');

        $this->assertNotEmpty($mentah);
        $this->assertStringNotContainsString('192.168.1.10', $mentah, 'URL RTSP tidak boleh disimpan plaintext.');
        $this->assertSame('rtsp://192.168.1.10:554/stream1', CctvCamera::find($c['id'])->rtsp());
    }

    public function test_nama_kamera_unik_per_tenant(): void
    {
        $this->kamera();
        $this->postJson('/api/v1/vision/cameras', ['name' => 'Kamera Depan'])->assertStatus(422);
    }

    public function test_ujian_sambungan_sahkan_format_rtsp(): void
    {
        $c = $this->kamera();

        $this->postJson("/api/v1/vision/cameras/{$c['id']}/test")->assertOk()->assertJson(['ok' => true]);

        $buruk = $this->kamera(['name' => 'Kamera Buruk', 'rtsp_url' => 'http://salah']);
        $this->postJson("/api/v1/vision/cameras/{$buruk['id']}/test")->assertStatus(422)->assertJson(['ok' => false]);
    }

    public function test_retensi_dalam_had(): void
    {
        $c = $this->kamera();

        $this->putJson("/api/v1/vision/cameras/{$c['id']}/retention", ['days' => 30])->assertOk();
        $this->putJson("/api/v1/vision/cameras/{$c['id']}/retention", ['days' => 200])->assertStatus(422);
        $this->putJson("/api/v1/vision/cameras/{$c['id']}/retention", ['days' => 0])->assertStatus(422);
    }

    public function test_webhook_hmac_sah_diterima(): void
    {
        $c = $this->kamera();

        $payload = json_encode([
            'event_type' => 'person',
            'confidence' => 0.94,
            'started_at' => now()->toIso8601String(),
            'clip_path' => 'clips/a.mp4',
        ]);
        $tandatangan = hash_hmac('sha256', $payload, 'rahsia-ujian-123');

        $this->call('POST', "/api/v1/vision/ingest/{$c['id']}", [], [], [],
            ['HTTP_X_VISION_SIGNATURE' => $tandatangan, 'CONTENT_TYPE' => 'application/json'], $payload)
            ->assertCreated();

        $this->dalamTenant($this->t);
        $this->assertSame(1, CctvEvent::count());
        $this->assertSame('person', CctvEvent::first()->event_type);
    }

    public function test_webhook_tandatangan_salah_ditolak(): void
    {
        $c = $this->kamera();
        $payload = json_encode(['event_type' => 'person']);

        $this->call('POST', "/api/v1/vision/ingest/{$c['id']}", [], [], [],
            ['HTTP_X_VISION_SIGNATURE' => 'salah', 'CONTENT_TYPE' => 'application/json'], $payload)
            ->assertStatus(401);

        $this->dalamTenant($this->t);
        $this->assertSame(0, CctvEvent::count());
    }

    public function test_webhook_tolak_jenis_biometrik(): void
    {
        $c = $this->kamera();
        $payload = json_encode(['event_type' => 'face_match', 'confidence' => 0.9]);
        $tandatangan = hash_hmac('sha256', $payload, 'rahsia-ujian-123');

        $this->call('POST', "/api/v1/vision/ingest/{$c['id']}", [], [], [],
            ['HTTP_X_VISION_SIGNATURE' => $tandatangan, 'CONTENT_TYPE' => 'application/json'], $payload)
            ->assertStatus(422);
    }

    public function test_peristiwa_senarai_dan_pengakuan(): void
    {
        $c = $this->kamera();
        $this->dalamTenant($this->t);
        $e = CctvEvent::create([
            'camera_id' => $c['id'],
            'event_type' => 'perimeter_breach',
            'confidence' => 0.88,
            'started_at' => now(),
        ]);

        $this->sebagai($this->u, $this->t);
        $senarai = $this->getJson('/api/v1/vision/events')->assertOk()->json('data');
        $this->assertCount(1, $senarai);

        $this->postJson("/api/v1/vision/events/{$e->id}/acknowledge", ['note' => 'Sudah disemak'])->assertOk();
        $this->postJson("/api/v1/vision/events/{$e->id}/acknowledge")->assertStatus(409);
    }

    public function test_log_tontonan_direkod(): void
    {
        $c = $this->kamera();

        $this->postJson('/api/v1/vision/view-logs', [
            'camera_id' => $c['id'],
            'action' => 'view_live',
            'purpose' => 'semakan keselamatan',
        ])->assertCreated();

        $log = $this->getJson('/api/v1/vision/view-logs')->assertOk()->json('data');
        $this->assertCount(1, $log);
        $this->assertSame('view_live', $log[0]['action']);
    }

    public function test_papan_pemuka_ringkasan(): void
    {
        $c = $this->kamera();
        $this->dalamTenant($this->t);
        CctvEvent::create(['camera_id' => $c['id'], 'event_type' => 'person', 'started_at' => now()]);
        CctvEvent::create(['camera_id' => $c['id'], 'event_type' => 'after_hours', 'started_at' => now()]);

        $this->sebagai($this->u, $this->t);
        $r = $this->getJson('/api/v1/vision/dashboard')->assertOk()->json();

        $this->assertSame(1, $r['cameras']['jumlah']);
        $this->assertSame(2, $r['events']['jumlah']);
        $this->assertSame(2, $r['events']['belum_diakui']);
        $this->assertSame(1, $r['events']['kritikal_belum_diakui']);
    }

    public function test_kamera_tidak_dipadam_hanya_dinyahaktifkan(): void
    {
        $c = $this->kamera();
        $this->deleteJson("/api/v1/vision/cameras/{$c['id']}")->assertOk();

        $this->dalamTenant($this->t);
        $this->assertSame('tidak_aktif', CctvCamera::find($c['id'])->status);
    }

    public function test_tenant_lain_tidak_nampak_kamera(): void
    {
        $this->kamera();

        [$ub, $tb] = $this->daftar('pemilik@kedai-b.my', 'Kedai B');
        $r = $this->sebagai($ub, $tb)->getJson('/api/v1/vision/cameras')->assertOk()->json();

        $this->assertCount(0, $r);
    }

    public function test_staf_tidak_boleh_daftar_kamera(): void
    {
        $staf = $this->jemput($this->u, $this->t, 'staf@kedai-a.my', ['staff']);

        $this->sebagai($staf, $this->t)
            ->postJson('/api/v1/vision/cameras', ['name' => 'Kamera Staf'])
            ->assertForbidden();
    }
}
