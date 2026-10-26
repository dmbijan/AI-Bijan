<?php

namespace Tests\Feature;

use App\Modules\PersonalAssistant\Models\KnowledgeChunk;
use App\Modules\PersonalAssistant\Models\KnowledgeDocument;
use App\Modules\PersonalAssistant\Services\EmbeddingService;
use App\Modules\PersonalAssistant\Services\VectorStoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Vector store & carian semantik (BAB 17A.4 / T-08). */
class KnowledgeVectorTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('ai@kedai-a.my', 'Kedai A');
    }

    private function dokumen(string $tajuk, string $kandungan, string $jenis = 'sop'): array
    {
        $this->sebagai($this->u, $this->t);

        return $this->postJson('/api/v1/knowledge', [
            'title' => $tajuk,
            'content' => $kandungan,
            'doc_type' => $jenis,
        ])->assertCreated()->json();
    }

    // ---------------- Embedding ----------------

    public function test_embedding_deterministik_dan_ternormal(): void
    {
        $svc = app(EmbeddingService::class);

        $a = $svc->embed('prosedur pembukaan kedai pada waktu pagi');
        $b = $svc->embed('prosedur pembukaan kedai pada waktu pagi');

        $this->assertSame($a, $b, 'Embedding mesti deterministik.');
        $this->assertSame($svc->dimensi(), count($a));

        $norm = sqrt(array_sum(array_map(fn ($x) => $x * $x, $a)));
        $this->assertEqualsWithDelta(1.0, $norm, 0.001, 'Vektor mesti L2-ternormal.');
    }

    public function test_teks_serupa_lebih_tinggi_skor(): void
    {
        $svc = app(EmbeddingService::class);
        $soalan = $svc->embed('cara buka kedai');

        $serupa = $svc->embed('cara buka kedai pada waktu pagi');
        $berbeza = $svc->embed('polis insurans kenderaan syarikat');

        $this->assertGreaterThan(
            $svc->kosinus($soalan, $berbeza),
            $svc->kosinus($soalan, $serupa),
            'Teks serupa mesti skor lebih tinggi.'
        );
    }

    public function test_pecah_teks_panjang(): void
    {
        $svc = app(EmbeddingService::class);
        $serpihan = $svc->pecah(str_repeat('perkataan ujian ', 300), 800, 100);

        $this->assertGreaterThan(1, count($serpihan));
        $this->assertLessThanOrEqual(800 + 1, mb_strlen($serpihan[0]));
    }

    // ---------------- Indeks ----------------

    public function test_indeks_dokumen_cipta_serpihan(): void
    {
        $r = $this->dokumen('SOP Pembukaan Kedai', str_repeat('Buka pintu pada 9 pagi dan nyalakan lampu. ', 40));

        $this->assertGreaterThan(0, $r['chunk_count']);

        $this->dalamTenant($this->t);
        $this->assertSame($r['chunk_count'], KnowledgeChunk::count());
        $this->assertNotNull(KnowledgeChunk::first()->embedding);
    }

    public function test_dokumen_sama_tidak_diduplikasi(): void
    {
        $this->dokumen('SOP A', 'Kandungan yang sama untuk ujian pendua dokumen.');
        $this->dokumen('SOP A', 'Kandungan yang sama untuk ujian pendua dokumen.');

        $this->dalamTenant($this->t);
        $this->assertSame(1, KnowledgeDocument::count());
    }

    // ---------------- Carian ----------------

    public function test_carian_semantik_jumpa_dokumen_relevan(): void
    {
        $this->dokumen('Prosedur Buka Kedai', 'Buka pintu kedai pada pukul 9 pagi, nyalakan lampu dan kira tunai awal.');
        $this->dokumen('Polisi Cuti', 'Pekerja mesti memohon cuti tahunan tujuh hari lebih awal.');

        $r = $this->getJson('/api/v1/knowledge/search?q=buka kedai pagi')->assertOk()->json();

        $this->assertGreaterThan(0, $r['count']);
        $this->assertSame('Prosedur Buka Kedai', $r['hits'][0]['title']);
    }

    public function test_carian_tiada_hasil_untuk_pertanyaan_tidak_berkaitan(): void
    {
        $this->dokumen('Polisi Cuti', 'Pekerja memohon cuti tahunan.');

        $r = $this->getJson('/api/v1/knowledge/search?q=resipi kek coklat')->assertOk()->json();

        $this->assertSame(0, $r['count']);
    }

    public function test_dokumen_nyahaktif_tidak_muncul_dalam_carian(): void
    {
        $d = $this->dokumen('SOP Lama', 'Prosedur lama yang tidak lagi digunakan untuk membuka kedai.');

        $this->deleteJson("/api/v1/knowledge/{$d['id']}")->assertOk();

        $r = $this->getJson('/api/v1/knowledge/search?q=prosedur kedai')->assertOk()->json();
        $this->assertSame(0, $r['count']);
    }

    // ---------------- Isolasi tenant (paling kritikal) ----------------

    public function test_tenant_lain_tidak_boleh_cari_dokumen(): void
    {
        $this->dokumen('Rahsia A', 'Prosedur dalaman syarikat A tentang pembukaan kedai.');

        [$ub, $tb] = $this->daftar('ai@kedai-b.my', 'Kedai B');
        $r = $this->sebagai($ub, $tb)->getJson('/api/v1/knowledge/search?q=prosedur kedai')->assertOk()->json();

        $this->assertSame(0, $r['count'], 'Bocor tenant!');
    }

    public function test_tenant_lain_tidak_nampak_senarai_dokumen(): void
    {
        $this->dokumen('Rahsia A', 'Kandungan sulit syarikat A untuk ujian isolasi tenant.');

        [$ub, $tb] = $this->daftar('ai@kedai-b.my', 'Kedai B');
        $r = $this->sebagai($ub, $tb)->getJson('/api/v1/knowledge')->assertOk()->json('data');

        $this->assertCount(0, $r);
    }

    public function test_tenant_lain_tidak_boleh_lihat_dokumen_by_id(): void
    {
        $d = $this->dokumen('Rahsia A', 'Kandungan sulit untuk ujian akses langsung.');

        [$ub, $tb] = $this->daftar('ai@kedai-b.my', 'Kedai B');
        $this->sebagai($ub, $tb)->getJson("/api/v1/knowledge/{$d['id']}")->assertNotFound();
    }

    // ---------------- Statistik & RBAC ----------------

    public function test_statistik_indeks(): void
    {
        $this->dokumen('SOP A', 'Kandungan dokumen pertama untuk statistik.');

        $r = $this->getJson('/api/v1/knowledge/stats')->assertOk()->json();

        $this->assertSame(1, $r['documents']);
        $this->assertGreaterThan(0, $r['chunks']);
        $this->assertSame($r['chunks'], $r['embedded']);
        $this->assertSame('hash-v1', $r['model']);
    }

    public function test_peranan_tanpa_permohonan_ai_tidak_boleh_tambah_dokumen(): void
    {
        // 'cashier' tiada personal_ai.configure (diketatkan 2026-10-24).
        $kasir = $this->jemput($this->u, $this->t, 'kasir@kedai-a.my', ['cashier']);

        $this->sebagai($kasir, $this->t)
            ->postJson('/api/v1/knowledge', ['title' => 'X', 'content' => str_repeat('kandungan ', 20)])
            ->assertForbidden();
    }

    public function test_vector_search_graceful_bila_kosong(): void
    {
        // Tiada dokumen — carian mesti pulangkan kosong, bukan ralat.
        $this->sebagai($this->u, $this->t);
        $r = $this->getJson('/api/v1/knowledge/search?q=apa apa sahaja')->assertOk()->json();

        $this->assertSame([], $r['hits']);
    }
}
