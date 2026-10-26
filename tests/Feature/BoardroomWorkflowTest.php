<?php

namespace Tests\Feature;

use App\Modules\Boardroom\Models\BoardroomFinding;
use App\Modules\Boardroom\Models\BoardroomSession;
use App\Modules\Boardroom\Services\BoardroomOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Ujian aliran 7 langkah Boardroom (BAB 20).
 */
class BoardroomWorkflowTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected BoardroomOrchestrator $orch;
    protected int $tenantId;
    protected int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();

        [$user, $tenant] = $this->daftar('br@ujian.test', 'Syarikat Boardroom');
        $this->userId = $user->id;
        $this->tenantId = $tenant->id;
        $this->dalamTenant($tenant);
        $this->orch = app(BoardroomOrchestrator::class);
    }

    public function test_langkah_1_buka_sesi(): void
    {
        $s = $this->orch->openSession($this->tenantId, $this->userId, 'Patutkah kita buka cawangan baru?');

        $this->assertSame(1, $s->current_step);
        $this->assertSame(BoardroomSession::STATUS_DRAFT, $s->status);
        $this->assertNotEmpty($s->question);
    }

    public function test_persoalan_kosong_ditolak(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->orch->openSession($this->tenantId, $this->userId, '   ');
    }

    public function test_kategori_dikesan_automatik(): void
    {
        $s1 = $this->orch->openSession($this->tenantId, $this->userId, 'Berapa untung bulan ini?');
        $this->assertSame('financial', $s1->category);

        $s2 = $this->orch->openSession($this->tenantId, $this->userId, 'Bagaimana kempen pemasaran kita?');
        $this->assertSame('marketing', $s2->category);

        $s3 = $this->orch->openSession($this->tenantId, $this->userId, 'Stok barang kritikal');
        $this->assertSame('operations', $s3->category);
    }

    public function test_langkah_2_pilih_ejen(): void
    {
        $s = $this->orch->openSession($this->tenantId, $this->userId, 'Isu kewangan bulan ini');
        $s = $this->orch->selectAgents($s);

        $this->assertSame(2, $s->current_step);
        $this->assertNotEmpty($s->agents_involved);
        $this->assertContains('cfo', $s->agents_involved);
    }

    public function test_langkah_3_edar_konteks_sanitasi(): void
    {
        $s = $this->orch->openSession($this->tenantId, $this->userId, 'Ujian konteks', [
            'api_key' => 'asai_sk_rahsia',
            'password' => 'rahsia123',
            'produk' => 'Kasut',
        ]);
        $this->orch->selectAgents($s);

        $ctx = $this->orch->distributeContext($s);

        $this->assertArrayNotHasKey('api_key', $ctx['context']['context']);
        $this->assertArrayNotHasKey('password', $ctx['context']['context']);
        $this->assertSame('Kasut', $ctx['context']['context']['produk']);
    }

    public function test_langkah_4_fakta_tanpa_sumber_ditolak(): void
    {
        $s = $this->orch->openSession($this->tenantId, $this->userId, 'Isu kewangan');
        $this->orch->selectAgents($s);

        $this->expectException(\InvalidArgumentException::class);
        $this->orch->recordFinding($s, 'cfo', [
            'finding_type' => 'fact',
            'title' => 'Margin bulan lepas',
            'content' => 'Margin 32%',
            // source_reference SENGAJA tiada
        ]);
    }

    public function test_langkah_4_fakta_dengan_sumber_diterima(): void
    {
        $s = $this->orch->openSession($this->tenantId, $this->userId, 'Isu kewangan');
        $this->orch->selectAgents($s);

        $f = $this->orch->recordFinding($s, 'cfo', [
            'finding_type' => 'fact',
            'title' => 'Margin bulan lepas',
            'content' => 'Margin 32%',
            'source_reference' => 'finance:report:2026-09',
            'confidence_score' => 0.95,
        ]);

        $this->assertSame('fact', $f->finding_type);
        $this->assertTrue($f->isValid());
    }

    public function test_andaian_tanpa_sumber_dibenarkan(): void
    {
        $s = $this->orch->openSession($this->tenantId, $this->userId, 'Isu kewangan');
        $this->orch->selectAgents($s);

        $f = $this->orch->recordFinding($s, 'cfo', [
            'finding_type' => 'assumption',
            'title' => 'Kos staf baru',
            'content' => 'Anggaran RM3,500/bulan',
            'assumptions' => ['Tidak termasuk OT'],
        ]);

        $this->assertSame('assumption', $f->finding_type);
        $this->assertTrue($f->requiresReview);
    }

    public function test_ejen_bukan_ahli_ditolak(): void
    {
        $s = $this->orch->openSession($this->tenantId, $this->userId, 'Isu kewangan');
        $this->orch->selectAgents($s, ['cfo', 'ceo']);

        $this->expectException(\InvalidArgumentException::class);
        $this->orch->recordFinding($s, 'cmo', [
            'finding_type' => 'recommendation',
            'title' => 'Kempen baru',
            'content' => 'Buat kempen',
        ]);
    }

    public function test_langkah_5_kesan_percanggahan(): void
    {
        $s = $this->orch->openSession($this->tenantId, $this->userId, 'Isu kewangan');
        $this->orch->selectAgents($s, ['cfo', 'ceo']);

        // Skor jauh berbeza — patut dikesan sebagai konflik
        $this->orch->recordFinding($s, 'cfo', [
            'finding_type' => 'estimate',
            'title' => 'anggaran kos',
            'content' => 'RM3,500',
            'source_reference' => 'hr:estimate',
            'confidence_score' => 0.95,
        ]);
        $this->orch->recordFinding($s, 'ceo', [
            'finding_type' => 'estimate',
            'title' => 'anggaran kos',
            'content' => 'RM5,000',
            'source_reference' => 'ceo:estimate',
            'confidence_score' => 0.40,
        ]);

        $result = $this->orch->detectConflicts($s);

        $this->assertTrue($result['conflict_detected']);
        $this->assertSame(BoardroomSession::STATUS_AWAITING_REVIEW, $s->fresh()->status);
    }

    public function test_langkah_6_ringkasan_mengasingkan_jenis_dapatan(): void
    {
        $s = $this->orch->openSession($this->tenantId, $this->userId, 'Isu kewangan');
        $this->orch->selectAgents($s, ['cfo']);

        $this->orch->recordFinding($s, 'cfo', [
            'finding_type' => 'fact',
            'title' => 'F1',
            'content' => 'C1',
            'source_reference' => 'src:1',
        ]);
        $this->orch->recordFinding($s, 'cfo', [
            'finding_type' => 'assumption',
            'title' => 'A1',
            'content' => 'C2',
            'assumptions' => ['andaian 1'],
        ]);

        $s = $this->orch->prepareSummary($s);

        $this->assertSame(BoardroomSession::STATUS_AWAITING_OWNER_DECISION, $s->status);
        $this->assertSame(1, $s->summary['counts']['facts']);
        $this->assertSame(1, $s->summary['counts']['assumptions']);
        $this->assertArrayHasKey('disclaimer', $s->summary);
    }

    public function test_langkah_7_keputusan_direkod(): void
    {
        $s = $this->orch->openSession($this->tenantId, $this->userId, 'Isu kewangan');
        $this->orch->selectAgents($s);
        $this->orch->prepareSummary($s);

        $s = $this->orch->recordDecision($s, $this->userId, 'approve', 'Margin kukuh');

        $this->assertSame(BoardroomSession::STATUS_DECIDED, $s->status);
        $this->assertSame('approve', $s->decision);
        $this->assertNotNull($s->decision_at);
    }

    public function test_keputusan_ditolak_jika_status_salah(): void
    {
        $s = $this->orch->openSession($this->tenantId, $this->userId, 'Isu kewangan');
        // Belum sampai langkah 6

        $this->expectException(\RuntimeException::class);
        $this->orch->recordDecision($s, $this->userId, 'approve');
    }

    public function test_defer_kekalkan_sesi_terbuka(): void
    {
        $s = $this->orch->openSession($this->tenantId, $this->userId, 'Isu kewangan');
        $this->orch->selectAgents($s);
        $this->orch->prepareSummary($s);

        $s = $this->orch->recordDecision($s, $this->userId, 'defer', 'Perlu data tambahan');

        $this->assertSame(BoardroomSession::STATUS_IN_PROGRESS, $s->status);
        $this->assertTrue($s->isOpen());
    }

    public function test_keputusan_tidak_sah_ditolak(): void
    {
        $s = $this->orch->openSession($this->tenantId, $this->userId, 'Isu kewangan');
        $this->orch->selectAgents($s);
        $this->orch->prepareSummary($s);

        $this->expectException(\InvalidArgumentException::class);
        $this->orch->recordDecision($s, $this->userId, 'lulus_budak_budak');
    }
}
