<?php

namespace Tests\Feature;

use App\Modules\Fleet\Models\FleetAlert;
use App\Modules\Fleet\Models\FleetDriver;
use App\Modules\Fleet\Models\FleetLocationHistory;
use App\Modules\Fleet\Models\FleetTrip;
use App\Modules\Fleet\Models\FleetVehicle;
use App\Modules\Fleet\Models\FleetVehicleLocation;
use App\Modules\Fleet\Services\FleetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Fleet & GPS (BAB 84 / Idea #10).
 *
 * Modul pengurusan armada: kenderaan, pemandu, lokasi GPS, tingkah laku
 * memandu, amaran, perjalanan & skor keselamatan.
 */
class FleetTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@fleet.my', 'Fleet Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    private function pemandu(array $ganti = []): FleetDriver
    {
        return FleetDriver::create(array_merge([
            'name' => 'Ahmed Hassan',
            'phone' => '+60123456789',
            'license_no' => 'DL-2024-001',
            'license_class' => 'D',
            'license_expiry' => now()->addYears(2)->toDateString(),
        ], $ganti));
    }

    private function kenderaan(array $ganti = []): FleetVehicle
    {
        return FleetVehicle::create(array_merge([
            'plate_number' => 'WXY ' . rand(1000, 9999),
            'make' => 'Toyota',
            'model' => 'Hiace',
            'type' => 'van',
            'odometer_km' => 50000,
            'fuel_type' => 'diesel',
            'fuel_level_percent' => 80,
            'status' => 'active',
        ], $ganti));
    }

    // -------------------------------------------------------------------
    // Kenderaan
    // -------------------------------------------------------------------

    public function test_cipta_kenderaan(): void
    {
        $k = $this->kenderaan();

        $this->assertNotNull($k->id);
        $this->assertSame('active', $k->status);
        $this->assertTrue($k->isAktif());
    }

    public function test_plate_number_unik_per_tenant(): void
    {
        $this->kenderaan(['plate_number' => 'ABC 123']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        $this->kenderaan(['plate_number' => 'ABC 123']);
    }

    public function test_kenderaan_tenant_lain_tidak_nampak(): void
    {
        $k = $this->kenderaan();

        [$u2, $t2] = $this->daftar('lawan@fleet.my', 'Lawan Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertNull(FleetVehicle::find($k->id));
    }

    // -------------------------------------------------------------------
    // Pemandu
    // -------------------------------------------------------------------

    public function test_lesen_sah(): void
    {
        $p = $this->pemandu(['license_expiry' => now()->addYear()->toDateString()]);

        $this->assertTrue($p->lesenSah());
    }

    public function test_lesen_luput(): void
    {
        $p = $this->pemandu(['license_expiry' => now()->subMonth()->toDateString()]);

        $this->assertFalse($p->lesenSah());
    }

    public function test_lesen_tiada_tarikh_luput_dianggap_sah(): void
    {
        $p = $this->pemandu(['license_expiry' => null]);

        $this->assertTrue($p->lesenSah());
    }

    // -------------------------------------------------------------------
    // Lokasi GPS
    // -------------------------------------------------------------------

    public function test_rekod_lokasi_menyimpan_koordinat(): void
    {
        $s = app(FleetService::class);
        $k = $this->kenderaan();

        $hasil = $s->rekodLokasi($this->t->id, $k->id, [
            'latitude' => 3.139,
            'longitude' => 101.6869,
            'speed_kmh' => 45.5,
            'heading' => 90,
            'battery_percent' => 88,
            'ignition_on' => true,
        ]);

        $this->assertTrue($hasil['ok']);

        $lokasi = FleetVehicleLocation::where('vehicle_id', $k->id)->first();
        $this->assertNotNull($lokasi);
        $this->assertSame(3.139, (float) $lokasi->latitude);
        $this->assertTrue((bool) $lokasi->ignition_on);
    }

    public function test_rekod_lokasi_gagal_bila_kenderaan_tiada(): void
    {
        $s = app(FleetService::class);

        $hasil = $s->rekodLokasi($this->t->id, 999999, ['latitude' => 1.0, 'longitude' => 1.0]);

        $this->assertFalse($hasil['ok']);
        $this->assertSame('vehicle_not_found', $hasil['reason']);
    }

    public function test_rekod_lokasi_menyimpan_sejarah(): void
    {
        $s = app(FleetService::class);
        $k = $this->kenderaan();

        $s->rekodLokasi($this->t->id, $k->id, [
            'latitude' => 3.139,
            'longitude' => 101.6869,
            'speed_kmh' => 50,
        ]);

        $this->assertSame(1, FleetLocationHistory::where('vehicle_id', $k->id)->count());
    }

    public function test_lokasi_terkini(): void
    {
        $s = app(FleetService::class);
        $k = $this->kenderaan();

        $s->rekodLokasi($this->t->id, $k->id, [
            'latitude' => 3.139,
            'longitude' => 101.6869,
            'speed_kmh' => 30,
        ]);

        $lokasi = $s->lokasiTerkini($this->t->id, $k->id);

        $this->assertNotNull($lokasi);
        $this->assertSame(30.0, (float) $lokasi->speed_kmh);
    }

    public function test_peta_kota_kembalikan_kenderaan(): void
    {
        $s = app(FleetService::class);
        $k = $this->kenderaan();

        $s->rekodLokasi($this->t->id, $k->id, [
            'latitude' => 3.139,
            'longitude' => 101.6869,
            'speed_kmh' => 30,
        ]);

        $peta = $s->petaKota($this->t->id);

        $this->assertIsArray($peta);
        $this->assertCount(1, $peta);
        $this->assertSame($k->id, $peta[0]['vehicle_id']);
    }

    // -------------------------------------------------------------------
    // Amaran
    // -------------------------------------------------------------------

    public function test_bahan_api_rendah_cetus_amaran(): void
    {
        $s = app(FleetService::class);
        $k = $this->kenderaan();

        $s->rekodLokasi($this->t->id, $k->id, [
            'latitude' => 3.139,
            'longitude' => 101.6869,
            'fuel_level_percent' => 5,
        ]);

        $amaran = FleetAlert::where('vehicle_id', $k->id)->where('type', 'low_fuel')->first();

        $this->assertNotNull($amaran);
        $this->assertTrue($amaran->belumSelesai());
    }

    public function test_amaran_duplikat_dicegah(): void
    {
        $s = app(FleetService::class);
        $k = $this->kenderaan();

        $pertama = $s->cetusAmaran($this->t->id, [
            'vehicle_id' => $k->id,
            'type' => 'breakdown',
            'severity' => 'high',
            'title' => 'Kerosakan enjin',
            'message' => 'Enjin tidak berfungsi',
        ]);

        $kedua = $s->cetusAmaran($this->t->id, [
            'vehicle_id' => $k->id,
            'type' => 'breakdown',
            'severity' => 'high',
            'title' => 'Kerosakan enjin',
            'message' => 'Enjin tidak berfungsi',
        ]);

        $this->assertNotNull($pertama);
        $this->assertNull($kedua);
    }

    public function test_amaran_baharu_dipicu_selepas_30_minit(): void
    {
        $s = app(FleetService::class);
        $k = $this->kenderaan();

        $s->cetusAmaran($this->t->id, [
            'vehicle_id' => $k->id,
            'type' => 'speeding',
            'severity' => 'medium',
            'title' => 'Kelajuan',
            'message' => 'Terlaju',
        ]);

        FleetAlert::where('vehicle_id', $k->id)->update(['triggered_at' => now()->subHour()]);

        $baru = $s->cetusAmaran($this->t->id, [
            'vehicle_id' => $k->id,
            'type' => 'speeding',
            'severity' => 'medium',
            'title' => 'Kelajuan',
            'message' => 'Terlaju',
        ]);

        $this->assertNotNull($baru);
    }

    // -------------------------------------------------------------------
    // Perjalanan
    // -------------------------------------------------------------------

    public function test_mula_perjalanan(): void
    {
        $s = app(FleetService::class);
        $k = $this->kenderaan();
        $p = $this->pemandu();

        $perjalanan = $s->mulaPerjalanan($this->t->id, $k->id, [
            'driver_id' => $p->id,
            'reference_no' => 'TRIP-001',
        ]);

        $this->assertSame(FleetTrip::STATUS_IN_PROGRESS, $perjalanan->status);
        $this->assertTrue($perjalanan->dalamPerjalanan());
        $this->assertSame($p->id, $perjalanan->driver_id);
    }

    public function test_tamat_perjalanan_kira_jarak(): void
    {
        $s = app(FleetService::class);
        $k = $this->kenderaan();

        $perjalanan = $s->mulaPerjalanan($this->t->id, $k->id, ['reference_no' => 'TRIP-002']);

        $selesai = $s->tamatPerjalanan($this->t->id, $perjalanan->id, ['distance_km' => 42.5]);

        $this->assertSame(FleetTrip::STATUS_COMPLETED, $selesai->status);
        $this->assertSame(42.5, (float) $selesai->distance_km);
        $this->assertNotNull($selesai->ended_at);
    }

    // -------------------------------------------------------------------
    // Skor keselamatan
    // -------------------------------------------------------------------

    public function test_skor_keselamatan_penuh_tanpa_rekod(): void
    {
        $s = app(FleetService::class);
        $p = $this->pemandu();

        $this->assertSame(100.0, $s->kiraSkorKeselamatan($this->t->id, $p->id));
    }

    public function test_skor_keselamatan_turun_bila_pelanggaran(): void
    {
        $s = app(FleetService::class);
        $p = $this->pemandu();
        $k = $this->kenderaan(['assigned_driver_id' => $p->id]);

        FleetLocationHistory::create([
            'tenant_id' => $this->t->id,
            'vehicle_id' => $k->id,
            'latitude' => 3.139,
            'longitude' => 101.6869,
            'speed_kmh' => 90,
            'harsh_braking' => true,
            'harsh_acceleration' => false,
            'harsh_cornering' => false,
            'recorded_at' => now(),
        ]);

        $skor = $s->kiraSkorKeselamatan($this->t->id, $p->id);

        $this->assertLessThan(100.0, $skor);
        $this->assertGreaterThanOrEqual(0.0, $skor);
    }

    // -------------------------------------------------------------------
    // Statistik
    // -------------------------------------------------------------------

    public function test_statistik_armada(): void
    {
        $s = app(FleetService::class);
        $this->kenderaan();
        $this->kenderaan(['status' => 'maintenance']);
        $this->pemandu();

        $stat = $s->statistik($this->t->id);

        $this->assertSame(2, $stat['total_kenderaan']);
        $this->assertSame(1, $stat['aktif']);
        $this->assertSame(1, $stat['dalam_penyelenggaraan']);
        $this->assertSame(1, $stat['pemandu']);
    }

    // -------------------------------------------------------------------
    // API
    // -------------------------------------------------------------------

    public function test_api_senarai_kenderaan(): void
    {
        $this->kenderaan();

        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/fleet/vehicles')
            ->assertOk();

        $this->assertGreaterThanOrEqual(1, count($res->json('data')));
    }

    public function test_api_cipta_kenderaan(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/fleet/vehicles', [
                'plate_number' => 'ZZZ 9999',
                'make' => 'Nissan',
                'model' => 'Urvan',
                'type' => 'van',
            ])
            ->assertCreated();

        $this->dalamTenant($this->t);
        $this->assertSame(1, FleetVehicle::where('tenant_id', $this->t->id)->count());
    }

    public function test_api_rekod_lokasi(): void
    {
        $k = $this->kenderaan();

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/fleet/vehicles/{$k->id}/location", [
                'latitude' => 3.139,
                'longitude' => 101.6869,
                'speed_kmh' => 40,
            ])
            ->assertOk();

        $this->dalamTenant($this->t);
        $this->assertNotNull(FleetVehicleLocation::where('vehicle_id', $k->id)->first());
    }

    public function test_api_peta(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/fleet/map')
            ->assertOk();
    }

    public function test_api_senarai_pemandu(): void
    {
        $this->pemandu();

        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/fleet/drivers')
            ->assertOk();

        $this->assertGreaterThanOrEqual(1, count($res->json('data')));
    }

    public function test_api_statistik(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/fleet/statistics')
            ->assertOk();
    }

    public function test_api_senarai_amaran(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/fleet/alerts')
            ->assertOk();
    }
}
