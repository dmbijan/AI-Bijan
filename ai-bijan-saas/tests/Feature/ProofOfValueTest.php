<?php

namespace Tests\Feature;

use App\Modules\ProofOfValue\Models\PovBaseline;
use App\Modules\ProofOfValue\Models\PovExport;
use App\Modules\ProofOfValue\Models\PovMetric;
use App\Modules\ProofOfValue\Models\PovReading;
use App\Modules\ProofOfValue\Services\ProofOfValueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Proof of Value (BAB 84 / Idea #5).
 *
 * Metrik hasil per modul — corak ServiceTitan Pro Products.
 * Setiap modul mesti bolehventure "berapa nilai yang AI BIJAN hasilkan?".
 */
class ProofOfValueTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@pov.my', 'PoV Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    private function metrik(array $ganti = []): PovMetric
    {
        return PovMetric::create(array_merge([
            'module' => 'sales',
            'key' => 'conversion_rate',
            'name' => 'Kadar Penukaran',
            'description' => 'Deal ditutup',
            'unit' => 'percent',
            'direction' => 'up',
            'target_value' => 30,
            'is_active' => true,
            'is_verified' => false,
        ], $ganti));
    }

    // -------------------------------------------------------------------
    // Katalog
    // -------------------------------------------------------------------

    public function test_jana_katalog_mencipta_metrik_standard(): void
    {
        $s = app(ProofOfValueService::class);

        $dicipta = $s->janaKatalog($this->t->id);

        $this->assertGreaterThan(0, $dicipta);
        $this->assertSame(
            $dicipta,
            PovMetric::where('tenant_id', $this->t->id)->count()
        );
    }

    public function test_jana_katalog_idempotent(): void
    {
        $s = app(ProofOfValueService::class);

        $s->janaKatalog($this->t->id);
        $kedua = $s->janaKatalog($this->t->id);

        $this->assertSame(0, $kedua);
    }

    public function test_jana_katalog_untuk_modul_spesifik(): void
    {
        $s = app(ProofOfValueService::class);

        $dicipta = $s->janaKatalog($this->t->id, 'cod');

        $this->assertGreaterThan(0, $dicipta);
        $this->assertSame(['cod'], PovMetric::where('tenant_id', $this->t->id)->distinct()->pluck('module')->toArray());
    }

    public function test_katalog_mempunyai_8_modul(): void
    {
        $this->assertCount(8, ProofOfValueService::KATALOG);
    }

    public function test_modul_dengan_metrik(): void
    {
        $s = app(ProofOfValueService::class);
        $s->janaKatalog($this->t->id);

        $modul = $s->modulDenganMetrik($this->t->id);

        $this->assertContains('sales', $modul);
        $this->assertContains('cod', $modul);
    }

    // -------------------------------------------------------------------
    // Bacaan & baseline
    // -------------------------------------------------------------------

    public function test_rekod_bacaan(): void
    {
        $s = app(ProofOfValueService::class);
        $m = $this->metrik();

        $bacaan = $s->rekodBacaan($this->t->id, $m->id, 25.5);

        $this->assertSame(25.5, (float) $bacaan->value);
        $this->assertNull($bacaan->change_percent);
    }

    public function test_rekod_bacaan_kira_perubahan(): void
    {
        $s = app(ProofOfValueService::class);
        $m = $this->metrik();

        $s->rekodBacaan($this->t->id, $m->id, 20.0, now()->subDay()->toDateString());
        $bacaan = $s->rekodBacaan($this->t->id, $m->id, 25.0);

        $this->assertSame(25.0, (float) $bacaan->value);
        $this->assertSame(25.0, (float) $bacaan->change_percent);
    }

    public function test_rekod_bacaan_gagal_bila_metrik_tiada(): void
    {
        $s = app(ProofOfValueService::class);

        $this->expectException(\InvalidArgumentException::class);
        $s->rekodBacaan($this->t->id, 999999, 10.0);
    }

    public function test_bacaan_tenant_lain_tidak_dikira(): void
    {
        $s = app(ProofOfValueService::class);
        $m = $this->metrik();

        $s->rekodBacaan($this->t->id, $m->id, 20.0, now()->subDay()->toDateString());

        [$u2, $t2] = $this->daftar('lawan@pov.my', 'Lawan Sdn Bhd');
        $this->dalamTenant($t2);

        // Metrik milik tenant lain tidak boleh diakses langsung
        $this->expectException(\InvalidArgumentException::class);
        $s->rekodBacaan($t2->id, $m->id, 50.0);
    }

    public function test_rekod_baseline(): void
    {
        $s = app(ProofOfValueService::class);
        $m = $this->metrik();

        $baseline = $s->rekodBaseline($this->t->id, $m->id, 18.0);

        $this->assertSame(18.0, (float) $baseline->baseline_value);
        $this->assertNotNull($baseline->baseline_date);
    }

    public function test_bacaan_terkini_dikembalikan(): void
    {
        $s = app(ProofOfValueService::class);
        $m = $this->metrik();

        $s->rekodBacaan($this->t->id, $m->id, 10.0, now()->subDays(2)->toDateString());
        $s->rekodBacaan($this->t->id, $m->id, 30.0);

        $this->assertSame(30.0, (float) $m->fresh()->bacaanTerkini()->value);
    }

    public function test_perubahan_dihitung_terhadap_baseline(): void
    {
        $s = app(ProofOfValueService::class);
        $m = $this->metrik();

        $s->rekodBaseline($this->t->id, $m->id, 20.0);
        $s->rekodBacaan($this->t->id, $m->id, 25.0);

        $this->assertSame(25.0, (float) $m->fresh()->perubahan());
    }

    // -------------------------------------------------------------------
    // Sasaran
    // -------------------------------------------------------------------

    public function test_sasaran_tercapai(): void
    {
        $s = app(ProofOfValueService::class);
        $m = $this->metrik(['direction' => 'up', 'target_value' => 20]);

        $s->rekodBacaan($this->t->id, $m->id, 25.0);

        $this->assertTrue($m->fresh()->sasaranTercapai());
    }

    public function test_sasaran_tidak_tercapai(): void
    {
        $s = app(ProofOfValueService::class);
        $m = $this->metrik(['direction' => 'up', 'target_value' => 50]);

        $s->rekodBacaan($this->t->id, $m->id, 25.0);

        $this->assertFalse($m->fresh()->sasaranTercapai());
    }

    public function test_sasaran_direction_down(): void
    {
        $s = app(ProofOfValueService::class);
        $m = $this->metrik(['direction' => 'down', 'target_value' => 10]);

        $s->rekodBacaan($this->t->id, $m->id, 5.0);

        $this->assertTrue($m->fresh()->sasaranTercapai());
    }

    // -------------------------------------------------------------------
    // Ringkasan
    // -------------------------------------------------------------------

    public function test_ringkasan_mengandungi_statistik(): void
    {
        $s = app(ProofOfValueService::class);
        $s->janaKatalog($this->t->id);

        $ringkasan = $s->ringkasan($this->t->id);

        $this->assertArrayHasKey('modules', $ringkasan);
        $this->assertArrayHasKey('ringkasan', $ringkasan);
        $this->assertSame(8, $ringkasan['ringkasan']['jumlah_modul']);
        $this->assertGreaterThan(0, $ringkasan['ringkasan']['jumlah_metrik']);
    }

    public function test_ringkasan_kosong_tidak_error(): void
    {
        $s = app(ProofOfValueService::class);

        $ringkasan = $s->ringkasan($this->t->id);

        $this->assertSame(0, $ringkasan['ringkasan']['jumlah_metrik']);
        $this->assertSame(0.0, $ringkasan['ringkasan']['kadar_verifikasi']);
    }

    // -------------------------------------------------------------------
    // Verifikasi
    // -------------------------------------------------------------------

    public function test_sahkan_metrik(): void
    {
        $s = app(ProofOfValueService::class);
        $m = $this->metrik();

        $s->sahkanMetrik($this->t->id, $m->id, true, 'Disahkan oleh pemangku');

        $m->refresh();
        $this->assertTrue((bool) $m->is_verified);
        $this->assertSame('Disahkan oleh pemangku', $m->verification_note);
    }

    public function test_tolak_verifikasi_metrik(): void
    {
        $s = app(ProofOfValueService::class);
        $m = $this->metrik();

        $s->sahkanMetrik($this->t->id, $m->id, true);
        $s->sahkanMetrik($this->t->id, $m->id, false);

        $this->assertFalse((bool) $m->fresh()->is_verified);
    }

    // -------------------------------------------------------------------
    // Eksport
    // -------------------------------------------------------------------

    public function test_jana_eksport(): void
    {
        $s = app(ProofOfValueService::class);
        $s->janaKatalog($this->t->id);

        $export = $s->janaEksport(
            $this->t->id,
            'Laporan Q1 2026',
            now()->subMonths(3)->toDateString(),
            now()->toDateString()
        );

        $this->assertSame('draft', $export->status);
        $this->assertNotEmpty($export->metrics);
    }

    // -------------------------------------------------------------------
    // API
    // -------------------------------------------------------------------

    public function test_api_jana_katalog(): void
    {
        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/proof-of-value/catalog/generate')
            ->assertOk();

        $this->assertGreaterThan(0, $res->json('data.dicipta'));
    }

    public function test_api_senarai_metrik(): void
    {
        $this->metrik();

        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/proof-of-value/metrics')
            ->assertOk();

        $this->assertCount(1, $res->json('data'));
    }

    public function test_api_rekod_bacaan(): void
    {
        $m = $this->metrik();

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/proof-of-value/metrics/{$m->id}/reading", ['value' => 28.0])
            ->assertOk();

        $this->dalamTenant($this->t);
        $this->assertSame(28.0, (float) PovReading::where('metric_id', $m->id)->first()->value);
    }

    public function test_api_rekod_baseline(): void
    {
        $m = $this->metrik();

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/proof-of-value/metrics/{$m->id}/baseline", ['value' => 15.0])
            ->assertCreated();

        $this->dalamTenant($this->t);
        $this->assertSame(1, PovBaseline::where('metric_id', $m->id)->count());
    }

    public function test_api_sahkan_metrik(): void
    {
        $m = $this->metrik();

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/proof-of-value/metrics/{$m->id}/verify", [
                'verified' => true,
                'note' => 'Data disahkan',
            ])
            ->assertOk();

        $this->assertTrue((bool) $m->fresh()->is_verified);
    }

    public function test_api_ringkasan(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/proof-of-value/summary')
            ->assertOk();
    }

    public function test_api_jana_eksport(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/proof-of-value/exports', [
                'name' => 'Laporan NUnit',
                'period_start' => now()->subMonth()->toDateString(),
                'period_end' => now()->toDateString(),
            ])
            ->assertCreated();

        $this->dalamTenant($this->t);
        $this->assertSame(1, PovExport::where('tenant_id', $this->t->id)->count());
    }
}
