<?php

namespace Tests\Feature;

use App\Modules\ExecutiveAI\Models\ExecutivePreference;
use App\Modules\ExecutiveAI\Services\AutonomyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * B-Executive AI (BAB 82 / Fasa 5).
 *
 * Fokus: hierarchy autonomy, kelulusan, briefing.
 */
class ExecutiveAITest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@exec.my', 'Exec Sdn Bhd');
    }

    // ---------------------------------------------------------------------
    // Autonomy hierarchy
    // ---------------------------------------------------------------------

    public function test_aras_0_tanya_hanya_baca(): void
    {
        $autonomy = app(AutonomyService::class);

        $this->assertTrue($autonomy->permitted(0, 'read_data'));
        $this->assertTrue($autonomy->permitted(0, 'ask_question'));
        $this->assertFalse($autonomy->permitted(0, 'send_email'));
        $this->assertFalse($autonomy->permitted(0, 'draft_email'));
    }

    public function test_aras_1_cadang_tidak_boleh_laksana(): void
    {
        $autonomy = app(AutonomyService::class);

        $this->assertTrue($autonomy->permitted(1, 'suggest_action'));
        $this->assertTrue($autonomy->permitted(1, 'analyse'));
        $this->assertFalse($autonomy->permitted(1, 'send_email'));
        $this->assertTrue($autonomy->requiresApproval(1, 'send_email'));
    }

    public function test_aras_2_draf_boleh_draf_tapi_boleh_hantar(): void
    {
        $autonomy = app(AutonomyService::class);

        $this->assertTrue($autonomy->permitted(2, 'draft_email'));
        $this->assertTrue($autonomy->permitted(2, 'draft_video'));
        $this->assertFalse($autonomy->permitted(2, 'send_email'));
        $this->assertTrue($autonomy->requiresApproval(2, 'send_email'));
    }

    public function test_aras_3_laksana_boleh_hantar(): void
    {
        $autonomy = app(AutonomyService::class);

        $this->assertTrue($autonomy->permitted(3, 'send_email'));
        $this->assertTrue($autonomy->permitted(3, 'blast_campaign'));
        $this->assertFalse($autonomy->permitted(3, 'send_payment'));
    }

    public function test_aras_4_otomasi_masih_perlu_kelulusan_bagi_duit(): void
    {
        $autonomy = app(AutonomyService::class);

        $this->assertTrue($autonomy->permitted(4, 'automated_followup'));
        $this->assertTrue($autonomy->permitted(4, 'send_payment'));
        // Tindakan sentiasa perlu kelulusan manusia eksplisit
        $this->assertTrue($autonomy->requiresApproval(4, 'send_payment'));
        $this->assertTrue($autonomy->requiresApproval(4, 'delete_data'));
        $this->assertTrue($autonomy->requiresApproval(4, 'send_video_blast'));
    }

    public function test_video_blast_minimum_aras(): void
    {
        $autonomy = app(AutonomyService::class);

        // Draf video = aras 2 (BAB 82.8.1)
        $this->assertSame(2, $autonomy->minimumLevelFor('draft_video'));

        // Menjalankan kempen blast = aras 3 (Laksana)
        $this->assertSame(3, $autonomy->minimumLevelFor('blast_campaign'));

        // Hantar video blast = aras 3 + sentiasa kelulusan
        $this->assertSame(3, $autonomy->minimumLevelFor('send_video_blast'));
        $this->assertTrue($autonomy->requiresApproval(3, 'send_video_blast'));
    }

    public function test_label_dan_penerangan_setiap_aras(): void
    {
        $autonomy = app(AutonomyService::class);

        $this->assertSame('Tanya', $autonomy->label(0));
        $this->assertSame('Cadang', $autonomy->label(1));
        $this->assertSame('Draf', $autonomy->label(2));
        $this->assertSame('Laksana', $autonomy->label(3));
        $this->assertSame('Automasi', $autonomy->label(4));

        $this->assertCount(5, $autonomy->semuaTahap());
        $this->assertNotEmpty($autonomy->description(2));
    }

    // ---------------------------------------------------------------------
    // API
    // ---------------------------------------------------------------------

    public function test_api_command_centre(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/executive-ai/command-centre')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['ringkasan', 'kpi', 'pipeline', 'prospect_panas', 'risiko']]);
    }

    public function test_api_tanya_cadangan_tindakan(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/executive-ai/ask', [
                'question' => 'Tolong hantar e-mel follow-up kepada prospek panas',
            ])
            ->assertOk()
            ->assertJsonPath('data.tindakan_dicadangkan.0.action', 'send_email')
            ->assertJsonPath('data.perlu_kelulusan', true);
    }

    public function test_api_aras_0_tanya_dikenalpasti(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/executive-ai/ask', [
                'question' => 'Berapa jualan bulan ini?',
            ])
            ->assertOk()
            ->assertJsonPath('autonomy_level', 0);
    }

    public function test_api_kemas_kinas_autonomi(): void
    {
        $this->sebagai($this->u, $this->t)
            ->putJson('/api/v1/executive-ai/autonomy', [
                'autonomy_level' => 2,
                'language' => 'ms',
            ])
            ->assertOk()
            ->assertJsonPath('data.autonomy_level', 2)
            ->assertJsonPath('label', 'Draf');
    }

    public function test_api_autonomi_ditolak_nilai_invalid(): void
    {
        $this->sebagai($this->u, $this->t)
            ->putJson('/api/v1/executive-ai/autonomy', ['autonomy_level' => 9])
            ->assertStatus(422);
    }

    public function test_api_senarai_aras_autonomi(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/executive-ai/autonomy')
            ->assertOk()
            ->assertJsonPath('data.level', 0)
            ->assertJsonCount(5, 'data.tahap');
    }

    public function test_api_jana_briefing(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/executive-ai/briefings/generate', ['type' => 'daily'])
            ->assertCreated()
            ->assertJsonPath('data.type', 'daily');
    }

    public function test_api_senarai_briefing(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/executive-ai/briefings')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_api_tenant_lain_tidak_nampak_preferensi(): void
    {
        $this->dalamTenant($this->t);

        ExecutivePreference::create([
            'user_id' => $this->u->id,
            'autonomy_level' => 3,
        ]);

        [$u2, $t2] = $this->daftar('pemilik2@exec.my', 'Exec Kedua');

        $this->sebagai($u2, $t2)
            ->getJson('/api/v1/executive-ai/autonomy')
            ->assertOk()
            ->assertJsonPath('data.level', 0);
    }
}
