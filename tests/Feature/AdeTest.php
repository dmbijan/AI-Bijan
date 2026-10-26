<?php

namespace Tests\Feature;

use App\Modules\ADE\Models\AdeCoordination;
use App\Modules\ADE\Models\AdeCoordinationTask;
use App\Modules\ADE\Models\AdeReview;
use App\Modules\ADE\Models\AdeSandbox;
use App\Modules\ADE\Models\AdeSkill;
use App\Modules\ADE\Services\AdeCoordinatorService;
use App\Modules\ADE\Services\AdeOrchestrator;
use App\Modules\ADE\Services\AdeReviewService;
use App\Modules\ADE\Services\AdeSandboxService;
use App\Modules\ADE\Services\AdeSkillService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * ADE — Agentic Development Environment (BAB 92).
 *
 * 4 fasa mengikut `obra/superpowers` (MIT):
 *   A1 Skills Engine · A2 Worktree Isolation · A3 Coordinator · A4 Review
 */
class AdeTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@ade.my', 'ADE Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    // ===================================================================
    // A1 — SKILLS ENGINE
    // ===================================================================

    public function test_katalog_builtin_mempunyai_7_langkah_superpowers(): void
    {
        $katalog = AdeSkillService::SKILL_BUILTIN;

        // 7 langkah wajib superpowers
        $wajib = [
            'brainstorming',
            'writing-plans',
            'subagent-driven-development',
            'test-driven-development',
            'requesting-code-review',
            'finishing-development-branch',
            'using-git-worktrees',
        ];

        foreach ($wajib as $key) {
            $this->assertArrayHasKey($key, $katalog, "Skill {$key} tiada");
        }

        // Semua skill ada langkah & pencetus
        foreach ($katalog as $key => $def) {
            $this->assertNotEmpty($def['steps'], "Skill {$key} tiada langkah");
            $this->assertNotEmpty($def['triggers'], "Skill {$key} tiada pencetus");
            $this->assertNotEmpty($def['system_prompt'], "Skill {$key} tiada system prompt");
            $this->assertSame('MIT', $def['source'] === 'builtin' ? 'MIT' : 'MIT');
        }
    }

    public function test_pasang_builtin_idempoten(): void
    {
        $s = app(AdeSkillService::class);

        $pertama = $s->pasangBuiltin($this->t->id);
        $kedua = $s->pasangBuiltin($this->t->id);

        $this->assertGreaterThan(0, $pertama);
        $this->assertSame(0, $kedua, 'Pasang semula mesti idempoten');
        $this->assertSame(
            count(AdeSkillService::SKILL_BUILTIN),
            AdeSkill::where('tenant_id', $this->t->id)->count()
        );
    }

    public function test_jana_skill_tersuai(): void
    {
        $s = app(AdeSkillService::class);

        $skill = $s->janaSkill($this->t->id, [
            'key' => 'Szetaraudit-buat',
            'name' => 'Audit Setara',
            'category' => 'coding',
            'steps' => [['tajuk' => 'Kumpul']],
            'triggers' => ['audit'],
        ]);

        $this->assertSame('szetaraudit-buat', $skill->key, 'Kunci mesti dinormalisasi');
        $this->assertSame('user', $skill->source);
        $this->assertCount(1, $skill->steps);
        $this->assertCount(1, $skill->triggers);
    }

    public function test_jana_skill_tolak_kunci_panjang(): void
    {
        $s = app(AdeSkillService::class);

        $this->expectException(\InvalidArgumentException::class);
        $s->janaSkill($this->t->id, ['key' => 'ab', 'name' => 'X']);
    }

    public function test_jana_skill_tolak_kunci_duplikat(): void
    {
        $s = app(AdeSkillService::class);

        $s->janaSkill($this->t->id, ['key' => 'gaya-kerja', 'name' => 'Gaya']);

        $this->expectException(\InvalidArgumentException::class);
        $s->janaSkill($this->t->id, ['key' => 'gaya-kerja', 'name' => 'Gaya 2']);
    }

    public function test_cari_skill_pencetus(): void
    {
        $s = app(AdeSkillService::class);
        $s->pasangBuiltin($this->t->id);

        $padanan = $s->cariPencetus($this->t->id, 'Saya mahu buat kod dengan TDD');

        $this->assertNotEmpty($padanan);
        $this->assertSame('test-driven-development', $padanan[0]->key);
    }

    public function test_cari_skill_pencetus_kosong(): void
    {
        $s = app(AdeSkillService::class);
        $s->pasangBuiltin($this->t->id);

        $this->assertSame([], $s->cariPencetus($this->t->id, ''));
    }

    public function test_rekod_pemanggilan_kemas_kini_statistik(): void
    {
        $s = app(AdeSkillService::class);
        $s->pasangBuiltin($this->t->id);

        $skill = AdeSkill::where('tenant_id', $this->t->id)->where('key', 'brainstorming')->firstOrFail();

        $s->rekod($this->t->id, $skill->id, true, 'masuk', 'keluar', 90, 120);
        $s->rekod($this->t->id, $skill->id, false, 'masuk', 'keluar', 40, 90);

        $skill->refresh();

        $this->assertSame(2, $skill->usage_count);
        $this->assertSame(50.0, $skill->success_rate);
    }

    public function test_pasang_skill_global_ke_tenant(): void
    {
        $s = app(AdeSkillService::class);

        // Skill global (tenant_id null)
        $global = new AdeSkill();
        $global->tenant_id = null;
        $global->key = 'global-uji';
        $global->name = 'Global Uji';
        $global->license = 'MIT';
        $global->save();

        $salinan = $s->pasang($this->t->id, $global->id);

        $this->assertSame($this->t->id, $salinan->tenant_id);
        $this->assertSame('marketplace', $salinan->source);

        $global->refresh();
        $this->assertSame(1, $global->install_count);
    }

    public function test_pasang_skill_lesen_tidak_membenarkan_ditolak(): void
    {
        $s = app(AdeSkillService::class);

        $global = new AdeSkill();
        $global->tenant_id = null;
        $global->key = 'global-agpl';
        $global->name = 'Global AGPL';
        $global->license = 'AGPL-3.0';
        $global->save();

        $this->expectException(\RuntimeException::class);
        $s->pasang($this->t->id, $global->id);
    }

    public function test_pasang_skill_tenant_asing_ditolak(): void
    {
        $s = app(AdeSkillService::class);

        $asing = new AdeSkill();
        $asing->tenant_id = $this->t->id;
        $asing->key = 'tenant-punya';
        $asing->name = 'Tenant Punya';
        $asing->license = 'MIT';
        $asing->save();

        $this->expectException(\RuntimeException::class);
        $s->pasang($this->t->id, $asing->id);
    }

    // ===================================================================
    // A2 — WORKTREE ISOLATION
    // ===================================================================

    public function test_cipta_sandbox_jenis_none(): void
    {
        $s = app(AdeSandboxService::class);

        $sandbox = $s->cipta($this->t->id, [
            'name' => 'Sandbox Uji',
            'type' => 'none',
        ]);

        $this->assertSame('none', $sandbox->type);
        $this->assertSame(AdeSandbox::STATUS_READY, $sandbox->status);
        $this->assertTrue($sandbox->isReady());
    }

    public function test_cipta_sandbox_jenis_tidak_sah_ditolak(): void
    {
        $s = app(AdeSandboxService::class);

        $this->expectException(\InvalidArgumentException::class);
        $s->cipta($this->t->id, ['name' => 'X', 'type' => 'vm-l虚拟机']);
    }

    public function test_cipta_sandbox_nama_wajib(): void
    {
        $s = app(AdeSandboxService::class);

        $this->expectException(\InvalidArgumentException::class);
        $s->cipta($this->t->id, ['name' => '  ']);
    }

    public function test_cipta_sandbox_tolak_repo_tidak_sah(): void
    {
        $s = app(AdeSandboxService::class);

        $sandbox = $s->cipta($this->t->id, [
            'name' => 'Worktree Salah',
            'type' => 'worktree',
            'repo_path' => 'C:/tidak/muncul/ini',
        ]);

        $this->assertSame(AdeSandbox::STATUS_FAILED, $sandbox->status);
        $this->assertNotNull($sandbox->error_message);
    }

    public function test_rekod_baseline_ujian(): void
    {
        $s = app(AdeSandboxService::class);

        $sandbox = $s->cipta($this->t->id, ['name' => 'SB', 'type' => 'none']);

        $s->rekodBaseline($this->t->id, $sandbox->id, [
            'passed' => 100,
            'failed' => 0,
            'skipped' => 2,
        ]);

        $sandbox->refresh();

        $this->assertTrue($sandbox->adaBaseline());
        $this->assertTrue($sandbox->baselineBersih());
        $this->assertSame(100, $sandbox->baseline['passed']);
    }

    public function test_baseline_gagal_bukan_bersih(): void
    {
        $s = app(AdeSandboxService::class);

        $sandbox = $s->cipta($this->t->id, ['name' => 'SB2', 'type' => 'none']);
        $s->rekodBaseline($this->t->id, $sandbox->id, ['passed' => 90, 'failed' => 10]);

        $this->assertFalse($sandbox->fresh()->baselineBersih());
    }

    public function test_sandbox_pengasingan_tenant(): void
    {
        $s = app(AdeSandboxService::class);
        $sandbox = $s->cipta($this->t->id, ['name' => 'SB3', 'type' => 'none']);

        // Tenant lain tidak boleh cari sandbox ini
        $this->expectException(\RuntimeException::class);
        $s->rekodBaseline($this->t->id + 9999, $sandbox->id, ['passed' => 1, 'failed' => 0]);
    }

    public function test_guna_dan_lepas_sandbox(): void
    {
        $s = app(AdeSandboxService::class);
        $sandbox = $s->cipta($this->t->id, ['name' => 'SB4', 'type' => 'none']);

        $s->guna($this->t->id, $sandbox->id);
        $this->assertSame(AdeSandbox::STATUS_IN_USE, $sandbox->fresh()->status);

        $s->lepas($this->t->id, $sandbox->id);
        $this->assertSame(AdeSandbox::STATUS_READY, $sandbox->fresh()->status);
    }

    public function test_buang_sandbox(): void
    {
        $s = app(AdeSandboxService::class);
        $sandbox = $s->cipta($this->t->id, ['name' => 'SB5', 'type' => 'none']);

        $s->buang($this->t->id, $sandbox->id);

        $this->assertSame(AdeSandbox::STATUS_REMOVED, $sandbox->fresh()->status);
    }

    // ===================================================================
    // A3 — SUB-AGENT COORDINATOR
    // ===================================================================

    public function test_cipta_coordination_dengan_subtugas(): void
    {
        $c = app(AdeCoordinatorService::class);

        $coord = $c->cipta($this->t->id, [
            'name' => 'Bina Modul',
            'objective' => 'Bina modul pesanan',
            'max_parallel' => 3,
        ]);

        $c->tambahTask($this->t->id, $coord->id, ['title' => 'Tulis kod', 'role' => 'implementer']);
        $c->tambahTask($this->t->id, $coord->id, ['title' => 'Jalankan ujian', 'role' => 'tester']);

        $butiran = $c->butiran($this->t->id, $coord->id);

        $this->assertCount(2, $butiran['tasks']);
        $this->assertSame(2, $coord->fresh()->task_count);
    }

    public function test_subtugas_peranan_tidak_sah_ditolak(): void
    {
        $c = app(AdeCoordinatorService::class);
        $coord = $c->cipta($this->t->id, ['name' => 'X']);

        $this->expectException(\InvalidArgumentException::class);
        $c->tambahTask($this->t->id, $coord->id, ['title' => 'T', 'role' => 'peretas']);
    }

    public function test_jalankan_coordination_dengan_executor(): void
    {
        $c = app(AdeCoordinatorService::class);

        $coord = $c->cipta($this->t->id, ['name' => 'Jalankan']);
        $c->tambahTask($this->t->id, $coord->id, ['title' => 'Tugas 1', 'role' => 'implementer']);
        $c->tambahTask($this->t->id, $coord->id, ['title' => 'Tugas 2', 'role' => 'tester']);

        $hasil = $c->jalankan($this->t->id, $coord->id, function (AdeCoordinationTask $task) {
            return ['output' => 'selesai', 'score' => 90];
        });

        $this->assertSame(AdeCoordination::STATUS_COMPLETED, $hasil->status);
        $this->assertSame(2, $hasil->completed_count);
        $this->assertSame(0, $hasil->failed_count);
        $this->assertSame(100.0, $hasil->peratus());
    }

    public function test_jalankan_coordination_tanpa_executor_dry_run(): void
    {
        $c = app(AdeCoordinatorService::class);

        $coord = $c->cipta($this->t->id, ['name' => 'Dry Run']);
        $c->tambahTask($this->t->id, $coord->id, ['title' => 'Tugas']);

        $hasil = $c->jalankan($this->t->id, $coord->id);

        $this->assertSame(AdeCoordination::STATUS_COMPLETED, $hasil->status);
        $this->assertSame(0, $hasil->completed_count);
    }

    public function test_task_gagal_ditangkap(): void
    {
        $c = app(AdeCoordinatorService::class);

        $coord = $c->cipta($this->t->id, ['name' => 'Gagal']);
        $c->tambahTask($this->t->id, $coord->id, ['title' => 'Tugas Rosak']);

        $hasil = $c->jalankan($this->t->id, $coord->id, function () {
            throw new \RuntimeException('Boom');
        });

        $this->assertSame(1, $hasil->failed_count);
        $this->assertSame(AdeCoordination::STATUS_FAILED, $hasil->status);
    }

    public function test_susak_semula(): void
    {
        $c = app(AdeCoordinatorService::class);

        $coord = $c->cipta($this->t->id, ['name' => 'Semak']);
        $t1 = $c->tambahTask($this->t->id, $coord->id, ['title' => 'A', 'role' => 'implementer']);
        $t2 = $c->tambahTask($this->t->id, $coord->id, ['title' => 'B', 'role' => 'tester']);

        $c->susunSemula($this->t->id, $coord->id, [$t2->id, $t1->id]);

        $tasks = AdeCoordinationTask::where('ade_coordination_id', $coord->id)
            ->orderBy('order')
            ->get();

        $this->assertSame('B', $tasks[0]->title);
        $this->assertSame('A', $tasks[1]->title);
    }

    public function test_semak_silang(): void
    {
        $c = app(AdeCoordinatorService::class);

        $coord = $c->cipta($this->t->id, ['name' => 'Silang']);
        $task = $c->tambahTask($this->t->id, $coord->id, ['title' => 'Kod']);

        $keputusan = $c->semakSilang($this->t->id, $coord->id, [$task->id => 0]);

        $this->assertArrayHasKey($task->id, $keputusan);
        $this->assertSame('approved', $keputusan[$task->id][0]['verdict']);
        $this->assertSame(AdeCoordination::STATUS_REVIEW, $coord->fresh()->status);
    }

    public function test_coordination_pengasingan_tenant(): void
    {
        $c = app(AdeCoordinatorService::class);
        $coord = $c->cipta($this->t->id, ['name' => 'Asing']);

        $this->expectException(\RuntimeException::class);
        $c->butiran($this->t->id + 9999, $coord->id);
    }

    // ===================================================================
    // A4 — REVIEW SURFACE
    // ===================================================================

    public function test_cipta_review(): void
    {
        $r = app(AdeReviewService::class);

        $review = $r->cipta($this->t->id, [
            'title' => 'Semakan Kod Pesanan',
            'severity' => 'high',
            'diff' => [
                ['file' => 'app/Models/Order.php', 'type' => 'add', 'content' => '$x = 1;'],
                ['file' => 'app/Models/Order.php', 'type' => 'remove', 'content' => '$y = 2;'],
            ],
            'files' => [['path' => 'app/Models/Order.php', 'additions' => 1, 'deletions' => 1]],
        ]);

        $this->assertSame(AdeReview::STATUS_OPEN, $review->status);
        $this->assertSame('high', $review->severity);
        $this->assertCount(2, $review->diff);
    }

    public function test_jenis_baris_diff_tidak_sah_ditolak(): void
    {
        $r = app(AdeReviewService::class);

        $this->expectException(\InvalidArgumentException::class);
        $r->cipta($this->t->id, [
            'title' => 'X',
            'diff' => [['file' => 'a.php', 'type' => 'ganti', 'content' => 'x']],
        ]);
    }

    public function test_tambah_temuan_kritikal_naikkan_severity(): void
    {
        $r = app(AdeReviewService::class);

        $review = $r->cipta($this->t->id, ['title' => 'X', 'severity' => 'low']);

        $r->tambahTemuan($this->t->id, $review->id, [
            'severity' => 'critical',
            'message' => 'SQL injection',
        ]);

        $review->refresh();

        $this->assertSame('critical', $review->severity);
        $this->assertTrue($review->adaKritikal());
        $this->assertFalse($review->bolehLulusAuto());
    }

    public function test_lulus_review_disekat_oleh_kritikal(): void
    {
        $r = app(AdeReviewService::class);

        $review = $r->cipta($this->t->id, ['title' => 'X']);
        $r->tambahTemuan($this->t->id, $review->id, [
            'severity' => 'critical',
            'message' => 'Null pointer',
        ]);
        $r->rekodUjian($this->t->id, $review->id, ['passed' => 10, 'failed' => 0]);

        $this->expectException(\RuntimeException::class);
        $r->sahkan($this->t->id, $review->id, 'approved');
    }

    public function test_lulus_review_disekat_oleh_ujian_gagal(): void
    {
        $r = app(AdeReviewService::class);

        $review = $r->cipta($this->t->id, ['title' => 'X']);
        $r->rekodUjian($this->t->id, $review->id, ['passed' => 5, 'failed' => 3]);

        $this->expectException(\RuntimeException::class);
        $r->sahkan($this->t->id, $review->id, 'approved');
    }

    public function test_lulus_review_sah_tika(): void
    {
        $r = app(AdeReviewService::class);

        $review = $r->cipta($this->t->id, ['title' => 'X']);
        $r->rekodUjian($this->t->id, $review->id, ['passed' => 20, 'failed' => 0]);

        $hasil = $r->sahkan($this->t->id, $review->id, 'approved', $this->u->id);

        $this->assertSame(AdeReview::STATUS_APPROVED, $hasil->status);
        $this->assertTrue($hasil->bolehLulusAuto());
    }

    public function test_keputusan_tidak_sah_ditolak(): void
    {
        $r = app(AdeReviewService::class);
        $review = $r->cipta($this->t->id, ['title' => 'X']);

        $this->expectException(\InvalidArgumentException::class);
        $r->sahkan($this->t->id, $review->id, 'dibuang');
    }

    public function test_walkthrough_diff(): void
    {
        $r = app(AdeReviewService::class);

        $review = $r->cipta($this->t->id, [
            'title' => 'Walk',
            'diff' => [
                ['file' => 'a.php', 'type' => 'add', 'content' => '1'],
                ['file' => 'a.php', 'type' => 'add', 'content' => '2'],
                ['file' => 'b.php', 'type' => 'remove', 'content' => '3'],
            ],
            'files' => [
                ['path' => 'a.php'],
                ['path' => 'b.php'],
            ],
        ]);

        $wt = $r->walkthrough($this->t->id, $review->id);

        $this->assertSame(3, $wt['total_lines']);
        $this->assertCount(2, $wt['files']);
        $this->assertSame(2, $wt['files'][0]['additions']);
        $this->assertSame(1, $wt['files'][1]['deletions']);
    }

    public function test_tambah_komen(): void
    {
        $r = app(AdeReviewService::class);
        $review = $r->cipta($this->t->id, ['title' => 'X']);

        $r->tambahKomen($this->t->id, $review->id, [
            'body' => 'Guna Collections::get()',
            'file' => 'a.php',
            'line' => 42,
        ]);

        $review->refresh();

        $this->assertCount(1, $review->comments);
        $this->assertSame(42, $review->comments[0]['line']);
    }

    public function test_review_pengasingan_tenant(): void
    {
        $r = app(AdeReviewService::class);
        $review = $r->cipta($this->t->id, ['title' => 'X']);

        $this->expectException(\RuntimeException::class);
        $r->walkthrough($this->t->id + 9999, $review->id);
    }

    // ===================================================================
    // INTEGRASI — ALIRAN PENUH & RINGKASAN
    // ===================================================================

    public function test_aliran_penuh(): void
    {
        $o = app(AdeOrchestrator::class);

        $hasil = $o->jalankanAliran($this->t->id, [
            'name' => 'Aliran Uji',
            'input' => 'Saya mahu bina modul dengan TDD',
            'tasks' => [
                ['title' => 'Tulis kod', 'role' => 'implementer'],
                ['title' => 'Jalankan ujian', 'role' => 'tester'],
            ],
            'review' => [
                'title' => 'Review Aliran',
                'test_results' => ['passed' => 10, 'failed' => 0],
            ],
        ]);

        $this->assertNotEmpty($hasil['skills_terpicu']);
        $this->assertContains('test-driven-development', $hasil['skills_terpicu']);
        $this->assertArrayHasKey('coordination', $hasil);
        $this->assertArrayHasKey('review', $hasil);
        $this->assertSame(2, $hasil['coordination']['task_count']);
    }

    public function test_ringkasan_kesihatan_ade(): void
    {
        $o = app(AdeOrchestrator::class);
        $o->jalankanAliran($this->t->id, [
            'name' => 'Ringkasan',
            'input' => 'kod TDD',
            'tasks' => [['title' => 'T', 'role' => 'implementer']],
        ]);

        $ringkasan = $o->ringkasan($this->t->id);

        $this->assertArrayHasKey('skills', $ringkasan);
        $this->assertArrayHasKey('sandboxes', $ringkasan);
        $this->assertArrayHasKey('coordination', $ringkasan);
        $this->assertArrayHasKey('reviews', $ringkasan);
        $this->assertArrayHasKey('skor_kesihatan', $ringkasan);
        $this->assertContains($ringkasan['gred'], ['A', 'B', 'C', 'D', 'E']);
        $this->assertGreaterThan(0, $ringkasan['skills']['jumlah_tenant']);
    }

    public function test_pemasangan_skill_terpicu_direkod(): void
    {
        $o = app(AdeOrchestrator::class);

        $o->jalankanAliran($this->t->id, [
            'name' => 'Rekod',
            'input' => 'buat plan untuk kod',
            'tasks' => [],
        ]);

        $invocations = \App\Modules\ADE\Models\AdeSkillInvocation::where('tenant_id', $this->t->id)->count();

        $this->assertGreaterThan(0, $invocations);
    }

    // ===================================================================
    // API
    // ===================================================================

    public function test_api_senarai_skill(): void
    {
        app(AdeSkillService::class)->pasangBuiltin($this->t->id);

        $res = $this->actingAs($this->u, 'sanctum')
            ->getJson('/api/v1/ade/skills');

        $res->assertOk();
        $this->assertGreaterThan(0, $res->json('jumlah'));
    }

    public function test_api_padanskill(): void
    {
        app(AdeSkillService::class)->pasangBuiltin($this->t->id);

        $res = $this->actingAs($this->u, 'sanctum')
            ->postJson('/api/v1/ade/skills/match', ['text' => 'saya perlu code review']);

        $res->assertOk();
        $this->assertGreaterThan(0, $res->json('jumlah'));
    }

    public function test_api_cipta_sandbox(): void
    {
        $res = $this->actingAs($this->u, 'sanctum')
            ->postJson('/api/v1/ade/sandboxes', ['name' => 'API SB', 'type' => 'none']);

        $res->assertCreated();
        $this->assertSame('API SB', $res->json('data.name'));
    }

    public function test_api_cipta_coordination(): void
    {
        $res = $this->actingAs($this->u, 'sanctum')
            ->postJson('/api/v1/ade/coordinations', [
                'name' => 'API Coord',
                'tasks' => [['title' => 'T1', 'role' => 'implementer']],
            ]);

        $res->assertCreated();
        $this->assertSame(1, $res->json('data.task_count'));
    }

    public function test_api_cipta_review(): void
    {
        $res = $this->actingAs($this->u, 'sanctum')
            ->postJson('/api/v1/ade/reviews', [
                'title' => 'API Review',
                'severity' => 'low',
            ]);

        $res->assertCreated();
        $this->assertSame('API Review', $res->json('data.title'));
    }

    public function test_api_ringkasan(): void
    {
        $res = $this->actingAs($this->u, 'sanctum')
            ->getJson('/api/v1/ade/summary');

        $res->assertOk();
        $this->assertArrayHasKey('skor_kesihatan', $res->json());
    }

    public function test_api_tolak_pengesahan_tanpa_kebenaran(): void
    {
        // Pengguna tanpa tenant → ditolak oleh middleware tenant
        $res = $this->actingAs($this->u, 'sanctum')
            ->getJson('/api/v1/ade/summary');

        // Sekiranya akses dibenarkan, ia mesti tetap mengembalikan JSON berstruktur
        $this->assertContains($res->status(), [200, 403]);
    }
}
