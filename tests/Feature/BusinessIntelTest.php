<?php

namespace Tests\Feature;

use App\Modules\BusinessIntel\Models\BiCompetitor;
use App\Modules\BusinessIntel\Models\BiCompetitorSnapshot;
use App\Modules\BusinessIntel\Models\BiHealthSnapshot;
use App\Modules\BusinessIntel\Models\BiIdea;
use App\Modules\BusinessIntel\Models\BiLearning;
use App\Modules\BusinessIntel\Models\BiMarketSignal;
use App\Modules\BusinessIntel\Models\BiStudy;
use App\Modules\BusinessIntel\Services\BusinessIntelService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Business Intelligence (BAB 86 / Idea #23, #24, #25).
 *
 * AI self-improve + kajian bisnes + competitor auto-audit + idea feed.
 */
class BusinessIntelTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@bi.my', 'BI Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    private function pesaing(array $ganti = []): BiCompetitor
    {
        return BiCompetitor::create(array_merge([
            'tenant_id' => $this->t->id,
            'name' => 'Pesaing A',
            'industry' => 'retail',
            'threat_level' => BiCompetitor::THREAT_MEDIUM,
        ], $ganti));
    }

    // -------------------------------------------------------------------
    // Skor kesihatan
    // -------------------------------------------------------------------

    public function test_kira_kesihatan_kembalikan_struktur(): void
    {
        $s = app(BusinessIntelService::class);

        $hasil = $s->kiraKesihatan($this->t->id);

        $this->assertArrayHasKey('overall_score', $hasil);
        $this->assertArrayHasKey('grade', $hasil);
        $this->assertArrayHasKey('dimensions', $hasil);
        $this->assertArrayHasKey('weakest', $hasil);
        $this->assertCount(6, $hasil['dimensions']);
    }

    public function test_kira_kesihatan_kosong_neutral(): void
    {
        $s = app(BusinessIntelService::class);

        $hasil = $s->kiraKesihatan($this->t->id);

        // Tiada data → skor neutral sekitar 50
        $this->assertGreaterThan(0, $hasil['overall_score']);
        $this->assertLessThanOrEqual(100, $hasil['overall_score']);
    }

    public function test_gred_daripada_skor(): void
    {
        $this->assertSame('A', BiHealthSnapshot::gredDaripada(90));
        $this->assertSame('B', BiHealthSnapshot::gredDaripada(75));
        $this->assertSame('C', BiHealthSnapshot::gredDaripada(60));
        $this->assertSame('D', BiHealthSnapshot::gredDaripada(45));
        $this->assertSame('E', BiHealthSnapshot::gredDaripada(20));
    }

    public function test_simpan_kesihatan_snapshot(): void
    {
        $s = app(BusinessIntelService::class);

        $snapshot = $s->simpanKesihatan($this->t->id);

        $this->assertNotNull($snapshot->id);
        $this->assertSame(now()->toDateString(), $snapshot->snapshot_date->toDateString());
        $this->assertNotEmpty($snapshot->grade);
    }

    public function test_simpan_kesihatan_idempotent_harian(): void
    {
        $s = app(BusinessIntelService::class);

        $s->simpanKesihatan($this->t->id);
        $s->simpanKesihatan($this->t->id);

        $this->assertSame(1, BiHealthSnapshot::where('tenant_id', $this->t->id)->count());
    }

    public function test_dimensi_terlemah(): void
    {
        $snapshot = BiHealthSnapshot::create([
            'tenant_id' => $this->t->id,
            'snapshot_date' => now()->toDateString(),
            'sales_score' => 90,
            'cashflow_score' => 30,
            'operations_score' => 80,
            'customer_score' => 25,
            'marketing_score' => 70,
            'compliance_score' => 60,
        ]);

        $terlemah = $snapshot->dimensiTerlemah();

        $this->assertCount(2, $terlemah);
        $this->assertArrayHasKey('customer', $terlemah);
    }

    public function test_trend_kesihatan(): void
    {
        $s = app(BusinessIntelService::class);

        BiHealthSnapshot::create([
            'tenant_id' => $this->t->id,
            'snapshot_date' => now()->subDays(10)->toDateString(),
            'overall_score' => 50,
        ]);
        BiHealthSnapshot::create([
            'tenant_id' => $this->t->id,
            'snapshot_date' => now()->toDateString(),
            'overall_score' => 70,
        ]);

        $trend = $s->trendKesihatan($this->t->id);

        $this->assertSame('up', $trend['trend']);
        $this->assertSame(20.0, $trend['perubahan']);
    }

    public function test_trend_kosong(): void
    {
        $s = app(BusinessIntelService::class);

        $trend = $s->trendKesihatan($this->t->id);

        $this->assertSame('stable', $trend['trend']);
        $this->assertSame(0.0, $trend['perubahan']);
    }

    // -------------------------------------------------------------------
    // Kumpul data
    // -------------------------------------------------------------------

    public function test_kumpul_data_perniagaan(): void
    {
        $s = app(BusinessIntelService::class);

        $data = $s->kumpulData($this->t->id);

        $this->assertArrayHasKey('jumlah_jualan_30', $data);
        $this->assertArrayHasKey('margin_percent', $data);
        $this->assertArrayHasKey('ikut_platform', $data);
        $this->assertArrayHasKey('produk_teratas', $data);
    }

    public function test_kumpul_data_ambil_pesanan_sebenar(): void
    {
        $orders = app(\App\Modules\Orders\Services\UnifiedOrderService::class);
        $orders->ingest($this->t->id, [
            'source_platform' => 'shopee',
            'items' => [['name' => 'Produk A', 'quantity' => 2, 'unit_price' => 100, 'unit_cost' => 60]],
        ]);

        $data = app(BusinessIntelService::class)->kumpulData($this->t->id);

        $this->assertSame(200.0, $data['jumlah_jualan_30']);
        $this->assertSame(80.0, $data['margin_30']);
        $this->assertSame(40.0, $data['margin_percent']);
    }

    // -------------------------------------------------------------------
    // Kajian
    // -------------------------------------------------------------------

    public function test_jalankan_kajian_fallback(): void
    {
        $s = app(BusinessIntelService::class);

        $study = $s->jalankanKajian($this->t->id, [
            'title' => 'Kajian Margin',
            'study_type' => 'pricing',
        ]);

        $this->assertSame(BiStudy::STATUS_COMPLETED, $study->status);
        $this->assertNotEmpty($study->summary);
        $this->assertIsArray($study->findings);
        $this->assertIsArray($study->recommendations);
    }

    public function test_kajian_jana_idea(): void
    {
        $s = app(BusinessIntelService::class);

        $study = $s->jalankanKajian($this->t->id, ['title' => 'Kajian']);

        $this->assertGreaterThan(0, BiIdea::where('bi_study_id', $study->id)->count());
    }

    public function test_kajian_simpan_snapshot_data(): void
    {
        $s = app(BusinessIntelService::class);

        $study = $s->jalankanKajian($this->t->id, ['title' => 'Kajian']);

        $this->assertNotNull($study->data_snapshot);
        $this->assertArrayHasKey('jumlah_jualan_30', $study->data_snapshot);
    }

    // -------------------------------------------------------------------
    // Idea feed
    // -------------------------------------------------------------------

    public function test_idea_feed_dengan_keutamaan(): void
    {
        $s = app(BusinessIntelService::class);

        BiIdea::create([
            'tenant_id' => $this->t->id,
            'title' => 'Idea Impak Tinggi Effort Rendah',
            'impact' => 'high',
            'effort' => 'low',
            'impact_score' => 90,
        ]);

        BiIdea::create([
            'tenant_id' => $this->t->id,
            'title' => 'Idea Impak Rendah Effort Tinggi',
            'impact' => 'low',
            'effort' => 'high',
            'impact_score' => 20,
        ]);

        $feed = $s->ideaFeed($this->t->id);

        $this->assertSame(2, $feed['jumlah']);
        // Yang pertama mesti nisbah keutamaan tertinggi
        $this->assertSame('Idea Impak Tinggi Effort Rendah', $feed['ideas'][0]['title']);
    }

    public function test_terima_idea(): void
    {
        $s = app(BusinessIntelService::class);

        $idea = BiIdea::create([
            'tenant_id' => $this->t->id,
            'title' => 'Idea Uji',
            'category' => 'growth',
        ]);

        $hasil = $s->terimaIdea($this->t->id, $idea->id);

        $this->assertSame(BiIdea::STATUS_ACCEPTED, $hasil->status);
        $this->assertNotNull($hasil->accepted_at);
    }

    public function test_terima_idea_rekod_pembelajaran(): void
    {
        $s = app(BusinessIntelService::class);

        $idea = BiIdea::create(['tenant_id' => $this->t->id, 'title' => 'X', 'category' => 'pricing']);

        $s->terimaIdea($this->t->id, $idea->id);

        $this->assertSame(1, BiLearning::where('tenant_id', $this->t->id)->count());
        $this->assertSame(BiLearning::TYPE_PREFERENCE, BiLearning::where('tenant_id', $this->t->id)->value('learning_type'));
    }

    public function test_tolak_idea_rekod_pembetulan(): void
    {
        $s = app(BusinessIntelService::class);

        $idea = BiIdea::create(['tenant_id' => $this->t->id, 'title' => 'X']);

        $hasil = $s->tolakIdea($this->t->id, $idea->id, 'Tidak relevan');

        $this->assertSame(BiIdea::STATUS_REJECTED, $hasil->status);
        $this->assertSame(BiLearning::TYPE_CORRECTION, BiLearning::where('tenant_id', $this->t->id)->value('learning_type'));
    }

    public function test_lengkapkan_idea_dengan_hasil(): void
    {
        $s = app(BusinessIntelService::class);

        $idea = BiIdea::create(['tenant_id' => $this->t->id, 'title' => 'X']);

        $hasil = $s->lengkapkanIdea($this->t->id, $idea->id, 1500.00);

        $this->assertSame(BiIdea::STATUS_DONE, $hasil->status);
        $this->assertSame(1500.0, (float) $hasil->actual_outcome);
        $this->assertSame(BiLearning::TYPE_OUTCOME, BiLearning::where('tenant_id', $this->t->id)->value('learning_type'));
    }

    public function test_nisbah_keutamaan_idea(): void
    {
        $idea = BiIdea::create([
            'tenant_id' => $this->t->id,
            'title' => 'X',
            'impact' => 'high',
            'effort' => 'low',
            'impact_score' => 90,
        ]);

        $this->assertSame(90.0, $idea->nisbahKeutamaan());
    }

    // -------------------------------------------------------------------
    // Pesaing
    // -------------------------------------------------------------------

    public function test_cipta_pesaing(): void
    {
        $p = $this->pesaing();

        $this->assertNotNull($p->id);
        $this->assertTrue($p->is_active);
    }

    public function test_snapshot_pesaing_kosong(): void
    {
        $s = app(BusinessIntelService::class);
        $p = $this->pesaing();

        $snapshot = $s->rekodSnapshotPesaing($this->t->id, $p->id, [
            'observed_data' => ['price' => 100],
        ]);

        $this->assertNotNull($snapshot->id);
        $this->assertSame($p->id, $snapshot->bi_competitor_id);
    }

    public function test_snapshot_pesaing_kesan_perubahan(): void
    {
        $s = app(BusinessIntelService::class);
        $p = $this->pesaing();

        BiCompetitorSnapshot::create([
            'tenant_id' => $this->t->id,
            'bi_competitor_id' => $p->id,
            'snapshot_date' => now()->subDay()->toDateString(),
            'observed_data' => ['price' => 100, 'product_count' => 10],
        ]);

        $snapshot = $s->rekodSnapshotPesaing($this->t->id, $p->id, [
            'observed_data' => ['price' => 120, 'product_count' => 10],
        ]);

        $this->assertNotEmpty($snapshot->changes);
        $this->assertSame('price', $snapshot->changes[0]['field']);
    }

    public function test_snapshot_pesaing_jana_idea_peluang(): void
    {
        $s = app(BusinessIntelService::class);
        $p = $this->pesaing();

        $s->rekodSnapshotPesaing($this->t->id, $p->id, [
            'observed_data' => ['price' => 100],
            'opportunities' => ['Mereka tiada COD — kita ada'],
        ]);

        $this->assertSame(1, BiIdea::where('tenant_id', $this->t->id)->where('source', 'competitor')->count());
    }

    public function test_ringkasan_persaingan(): void
    {
        $s = app(BusinessIntelService::class);

        $this->pesaing(['name' => 'A', 'threat_level' => BiCompetitor::THREAT_HIGH]);
        $this->pesaing(['name' => 'B', 'threat_level' => BiCompetitor::THREAT_LOW]);

        $ringkasan = $s->ringkasanPersaingan($this->t->id);

        $this->assertSame(2, $ringkasan['jumlah_pesaing']);
        $this->assertSame(1, $ringkasan['ancaman_tinggi'] ? count($ringkasan['ancaman_tinggi']) : 0);
    }

    // -------------------------------------------------------------------
    // Sinyal pasaran
    // -------------------------------------------------------------------

    public function test_jana_sinyal_pasaran(): void
    {
        $orders = app(\App\Modules\Orders\Services\UnifiedOrderService::class);
        $orders->ingest($this->t->id, [
            'source_platform' => 'shopee',
            'items' => [['name' => 'Produk Laris', 'quantity' => 5, 'unit_price' => 100]],
        ]);

        $sinyal = app(BusinessIntelService::class)->janaSinyalPasaran($this->t->id);

        $this->assertGreaterThan(0, count($sinyal));
        $this->assertGreaterThan(0, BiMarketSignal::where('tenant_id', $this->t->id)->count());
    }

    public function test_sinyal_tanpa_data_kosong(): void
    {
        $sinyal = app(BusinessIntelService::class)->janaSinyalPasaran($this->t->id);

        $this->assertIsArray($sinyal);
    }

    // -------------------------------------------------------------------
    // Pembelajaran
    // -------------------------------------------------------------------

    public function test_pembelajaran_aktif(): void
    {
        $s = app(BusinessIntelService::class);

        $s->rekodPembelajaran($this->t->id, [
            'learning_type' => BiLearning::TYPE_PATTERN,
            'insight' => 'Tenant suka idea kategori pricing',
            'confidence' => 80,
        ]);

        $hasil = $s->pembelajaranAktif($this->t->id);

        $this->assertCount(1, $hasil);
        $this->assertSame('Tenant suka idea kategori pricing', $hasil[0]['insight']);
    }

    public function test_pembelajaran_tidak_aktif_tidak_disenaraikan(): void
    {
        $s = app(BusinessIntelService::class);

        BiLearning::create([
            'tenant_id' => $this->t->id,
            'insight' => 'Tidak aktif',
            'is_active' => false,
        ]);

        $this->assertCount(0, $s->pembelajaranAktif($this->t->id));
    }

    // -------------------------------------------------------------------
    // Tenant isolation
    // -------------------------------------------------------------------

    public function test_pesaing_tenant_lain_tidak_nampak(): void
    {
        $p = $this->pesaing();

        [$u2, $t2] = $this->daftar('lawan@bi.my', 'Lawan Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertNull(BiCompetitor::find($p->id));
    }

    // -------------------------------------------------------------------
    // API
    // -------------------------------------------------------------------

    public function test_api_kesihatan(): void
    {
        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/business-intel/health')
            ->assertOk();

        $this->assertArrayHasKey('overall_score', $res->json('data'));
    }

    public function test_api_simpan_kesihatan(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/business-intel/health/snapshot')
            ->assertCreated();
    }

    public function test_api_trend(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/business-intel/health/trend')
            ->assertOk();
    }

    public function test_api_jalankan_kajian(): void
    {
        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/business-intel/studies', ['title' => 'Kajian API'])
            ->assertCreated();

        $this->assertSame(BiStudy::STATUS_COMPLETED, $res->json('data.status'));
    }

    public function test_api_idea_feed(): void
    {
        BiIdea::create(['tenant_id' => $this->t->id, 'title' => 'Idea A']);

        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/business-intel/ideas')
            ->assertOk();

        $this->assertSame(1, $res->json('data.jumlah'));
    }

    public function test_api_terima_idea(): void
    {
        $idea = BiIdea::create(['tenant_id' => $this->t->id, 'title' => 'A']);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/business-intel/ideas/{$idea->id}/accept")
            ->assertOk();

        $this->dalamTenant($this->t);
        $this->assertSame(BiIdea::STATUS_ACCEPTED, $idea->fresh()->status);
    }

    public function test_api_simpan_pesaing(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/business-intel/competitors', [
                'name' => 'Pesaing API',
                'industry' => 'retail',
                'threat_level' => 'high',
            ])
            ->assertCreated();
    }

    public function test_api_ringkasan_persaingan(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/business-intel/competitors/summary')
            ->assertOk();
    }

    public function test_api_sinyal(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/business-intel/signals')
            ->assertOk();
    }

    public function test_api_pembelajaran(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/business-intel/learnings')
            ->assertOk();
    }

    public function test_api_data_bisnes(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/business-intel/data')
            ->assertOk();
    }
}
