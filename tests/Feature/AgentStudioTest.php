<?php

namespace Tests\Feature;

use App\Modules\AgentStudio\Models\AgentBlueprint;
use App\Modules\AgentStudio\Models\AgentKnowledgeBinding;
use App\Modules\AgentStudio\Models\AgentRun;
use App\Modules\AgentStudio\Models\AgentTestRun;
use App\Modules\AgentStudio\Models\AgentToolBinding;
use App\Modules\AgentStudio\Services\AgentBlueprintService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Agent Studio (BAB 84 / Idea #8).
 *
 * B-Ejen — bina ejen AI tanpa kod, ala Zoho Agent Store (16 templat).
 * Corak: templat -> sesuaikan -> ujian -> publish.
 */
class AgentStudioTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@agent.my', 'Agent Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    // -------------------------------------------------------------------
    // Templat
    // -------------------------------------------------------------------

    public function test_tiada_templat_bukan_16(): void
    {
        $this->assertCount(16, AgentBlueprintService::TEMPLAT_EJEN);
    }

    public function test_senarai_templat(): void
    {
        $s = app(AgentBlueprintService::class);

        $senarai = $s->senaraiTemplat();

        $this->assertCount(16, $senarai);
        $this->assertArrayHasKey('key', $senarai[0]);
        $this->assertArrayHasKey('capabilities', $senarai[0]);
    }

    public function test_templat_wajib_punya_kemahiran(): void
    {
        foreach (AgentBlueprintService::TEMPLAT_EJEN as $key => $t) {
            $this->assertNotEmpty($t['capabilities'], "Templat {$key} tiada kemahiran");
            $this->assertNotEmpty($t['persona'], "Templat {$key} tiada persona");
            $this->assertArrayHasKey('level', $t['autonomy'], "Templat {$key} tiada otonomi");
        }
    }

    public function test_jana_ejen_daripada_templat(): void
    {
        $s = app(AgentBlueprintService::class);

        $agent = $s->janaDaripadaTemplat($this->t->id, 'sales_agent');

        $this->assertSame('Ejen Jualan AI', $agent->name);
        $this->assertSame('sales', $agent->category);
        $this->assertSame(AgentBlueprint::STATUS_DRAFT, $agent->status);
        $this->assertFalse($agent->isPublished());
        $this->assertNotEmpty($agent->system_prompt);
    }

    public function test_jana_ejen_ikat_alat(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'sales_agent');

        $this->assertGreaterThan(0, $agent->tools()->count());
    }

    public function test_jana_ejen_ikat_pengetahuan(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'sales_agent');

        $this->assertGreaterThan(0, $agent->knowledge()->count());
    }

    public function test_jana_ejen_templat_tiada_gagal(): void
    {
        $s = app(AgentBlueprintService::class);

        $this->expectException(\InvalidArgumentException::class);
        $s->janaDaripadaTemplat($this->t->id, 'templat_tiada');
    }

    public function test_jana_ejen_untuk_semua_16_templat(): void
    {
        $s = app(AgentBlueprintService::class);

        foreach (array_keys(AgentBlueprintService::TEMPLAT_EJEN) as $key) {
            $agent = $s->janaDaripadaTemplat($this->t->id, $key);
            $this->assertNotNull($agent->id);
        }

        $this->assertSame(16, AgentBlueprint::where('tenant_id', $this->t->id)->count());
    }

    // -------------------------------------------------------------------
    // Otonomi
    // -------------------------------------------------------------------

    public function test_otonomi_manual_perlu_kelulusan(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'compliance_agent');

        $this->assertSame(AgentBlueprintService::AUTONOMI_MANUAL, $agent->tahapOtonomi());
        $this->assertTrue($agent->perluKelulusan());
        $this->assertFalse($agent->bolehLakuSendiri());
    }

    public function test_otonomi_penuh_boleh_laku_sendiri(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'content_agent');

        $this->assertSame(AgentBlueprintService::AUTONOMI_PENUH, $agent->tahapOtonomi());
        $this->assertTrue($agent->bolehLakuSendiri());
    }

    public function test_tahap_otonomi_kosong_lalai(): void
    {
        $agent = AgentBlueprint::create([
            'tenant_id' => $this->t->id,
            'key' => 'manual_ejen',
            'name' => 'Manual',
            'category' => 'general',
            'status' => AgentBlueprint::STATUS_DRAFT,
        ]);

        $this->assertSame(0, $agent->tahapOtonomi());
        $this->assertTrue($agent->perluKelulusan());
    }

    // -------------------------------------------------------------------
    // Ikatan
    // -------------------------------------------------------------------

    public function test_ikat_alat(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'support_agent');

        $binding = $s->ikatAlat($this->t->id, $agent->id, 'crm.show', 'internal', 'read');

        $this->assertSame('crm.show', $binding->tool_key);
        $this->assertFalse($binding->bolehTulis());
    }

    public function test_ikat_alat_boleh_tulis(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'support_agent');

        $binding = $s->ikatAlat($this->t->id, $agent->id, 'helpdesk.create', 'internal', 'write');

        $this->assertTrue($binding->bolehTulis());
        $this->assertFalse($binding->bolehUrus());
    }

    public function test_ikat_alat_idempotent(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'support_agent');

        $s->ikatAlat($this->t->id, $agent->id, 'crm.show');
        $s->ikatAlat($this->t->id, $agent->id, 'crm.show');

        $this->assertSame(1, $agent->tools()->where('tool_key', 'crm.show')->count());
    }

    public function test_ikat_pengetahuan(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'support_agent');

        $binding = $s->ikatPengetahuan($this->t->id, $agent->id, 'faq', 'faq_bank_v2', 'FAQ', 2.0);

        $this->assertSame('faq_bank_v2', $binding->source_ref);
        $this->assertSame(2.0, (float) $binding->weight);
    }

    // -------------------------------------------------------------------
    // Pelaksanaan
    // -------------------------------------------------------------------

    public function test_jalankan_ejen(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'sales_agent');

        $run = $s->jalankan($this->t->id, $agent->id, ['prompt' => 'Senarai prospek saya']);

        $this->assertNotNull($run->output);
        $this->assertContains($run->status, [AgentRun::STATUS_COMPLETED, AgentRun::STATUS_ESCALATED]);
    }

    public function test_jalankan_ejen_tanpa_prompt_gagal(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'sales_agent');

        $run = $s->jalankan($this->t->id, $agent->id, []);

        $this->assertSame(AgentRun::STATUS_FAILED, $run->status);
        $this->assertNotNull($run->error_message);
    }

    public function test_jalankan_ejen_naik_kiraan_run(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'sales_agent');

        $s->jalankan($this->t->id, $agent->id, ['prompt' => 'Hello']);

        $this->assertSame(1, $agent->fresh()->run_count);
    }

    public function test_eksekusi_untuk_ejen_tenant_lain_gagal(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'sales_agent');

        [$u2, $t2] = $this->daftar('lawan@agent.my', 'Lawan Sdn Bhd');
        $this->dalamTenant($t2);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $s->jalankan($t2->id, $agent->id, ['prompt' => 'Hello']);
    }

    // -------------------------------------------------------------------
    // Ujian
    // -------------------------------------------------------------------

    public function test_jalankan_ujian(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'sales_agent');

        $test = $s->jalankanUjian($this->t->id, $agent->id, 'Ujian 1', 'Hello', 'Hello');

        $this->assertNotNull($test->actual_output);
        // Skor >= 70 bermaksud ujian lulus (padanan substring = 85, tepat = 100)
        $this->assertGreaterThanOrEqual(70.0, (float) $test->score);
        $this->assertTrue($test->isPassed());
    }

    public function test_ujian_gagal_bila_jawapan_jauh_beza(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'sales_agent');

        $test = $s->jalankanUjian(
            $this->t->id,
            $agent->id,
            'Ujian 2',
            'Hello',
            'zebra kerbau gajah kura kata'
        );

        $this->assertFalse($test->isPassed());
    }

    public function test_ujian_tiada_jangkaan_lulus(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'sales_agent');

        $test = $s->jalankanUjian($this->t->id, $agent->id, 'Ujian 3', 'Hello');

        $this->assertTrue($test->isPassed());
    }

    // -------------------------------------------------------------------
    // Publish
    // -------------------------------------------------------------------

    public function test_publish_gagal_tanpa_alat(): void
    {
        $s = app(AgentBlueprintService::class);

        $agent = AgentBlueprint::create([
            'tenant_id' => $this->t->id,
            'key' => 'tiada_alat',
            'name' => 'Tiada Alat',
            'category' => 'general',
            'status' => AgentBlueprint::STATUS_DRAFT,
        ]);

        $this->expectException(\RuntimeException::class);
        $s->publish($this->t->id, $agent->id);
    }

    public function test_publish_gagal_tanpa_ujian_lulus(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'sales_agent');

        $this->expectException(\RuntimeException::class);
        $s->publish($this->t->id, $agent->id);
    }

    public function test_publish_berjaya(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'sales_agent');
        $s->jalankanUjian($this->t->id, $agent->id, 'Ujian', 'Hello', 'Hello');

        $diterbit = $s->publish($this->t->id, $agent->id);

        $this->assertSame(AgentBlueprint::STATUS_ACTIVE, $diterbit->status);
        $this->assertTrue($diterbit->isPublished());
    }

    // -------------------------------------------------------------------
    // Statistik
    // -------------------------------------------------------------------

    public function test_statistik(): void
    {
        $s = app(AgentBlueprintService::class);
        $s->janaDaripadaTemplat($this->t->id, 'sales_agent');
        $s->janaDaripadaTemplat($this->t->id, 'support_agent');

        $stat = $s->statistik($this->t->id);

        $this->assertSame(2, $stat['jumlah_ejen']);
        $this->assertSame(16, $stat['templat_tersedia']);
    }

    // -------------------------------------------------------------------
    // API
    // -------------------------------------------------------------------

    public function test_api_senarai_templat(): void
    {
        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/agent-studio/templates')
            ->assertOk();

        $this->assertCount(16, $res->json('data'));
    }

    public function test_api_jana_ejen(): void
    {
        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/agent-studio/generate', ['template' => 'support_agent'])
            ->assertCreated();

        $this->assertSame('Ejen Sokongan AI', $res->json('data.name'));
    }

    public function test_api_senarai_ejen(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/agent-studio/generate', ['template' => 'support_agent'])
            ->assertCreated();

        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/agent-studio/agents')
            ->assertOk();

        $this->assertCount(1, $res->json('data'));
    }

    public function test_api_butiran_ejen(): void
    {
        $agent = app(AgentBlueprintService::class)->janaDaripadaTemplat($this->t->id, 'support_agent');

        $res = $this->sebagai($this->u, $this->t)
            ->getJson("/api/v1/agent-studio/agents/{$agent->id}")
            ->assertOk();

        $this->assertSame('Ejen Sokongan AI', $res->json('data.name'));
    }

    public function test_api_kemas_kini_ejen(): void
    {
        $agent = app(AgentBlueprintService::class)->janaDaripadaTemplat($this->t->id, 'support_agent');

        $this->sebagai($this->u, $this->t)
            ->putJson("/api/v1/agent-studio/agents/{$agent->id}", [
                'name' => 'Ejen Sokongan V2',
                'temperature' => 0.3,
            ])
            ->assertOk();

        $this->dalamTenant($this->t);
        $this->assertSame('Ejen Sokongan V2', $agent->fresh()->name);
    }

    public function test_api_ikat_alat(): void
    {
        $agent = app(AgentBlueprintService::class)->janaDaripadaTemplat($this->t->id, 'support_agent');

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/agent-studio/agents/{$agent->id}/tools", [
                'tool_key' => 'api_tool',
                'permission_level' => 'write',
            ])
            ->assertCreated();

        $this->dalamTenant($this->t);
        $this->assertSame(1, AgentToolBinding::where('agent_blueprint_id', $agent->id)->where('tool_key', 'api_tool')->count());
    }

    public function test_api_ikat_pengetahuan(): void
    {
        $agent = app(AgentBlueprintService::class)->janaDaripadaTemplat($this->t->id, 'support_agent');

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/agent-studio/agents/{$agent->id}/knowledge", [
                'source_type' => 'faq',
                'source_ref' => 'faq_v3',
            ])
            ->assertCreated();

        $this->dalamTenant($this->t);
        $this->assertSame(1, AgentKnowledgeBinding::where('agent_blueprint_id', $agent->id)->where('source_ref', 'faq_v3')->count());
    }

    public function test_api_jalankan_ejen(): void
    {
        $agent = app(AgentBlueprintService::class)->janaDaripadaTemplat($this->t->id, 'sales_agent');

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/agent-studio/agents/{$agent->id}/run", [
                'prompt' => 'Hello',
            ])
            ->assertOk();

        $this->dalamTenant($this->t);
        $this->assertSame(1, AgentRun::where('agent_blueprint_id', $agent->id)->count());
    }

    public function test_api_jalankan_ujian(): void
    {
        $agent = app(AgentBlueprintService::class)->janaDaripadaTemplat($this->t->id, 'sales_agent');

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/agent-studio/agents/{$agent->id}/tests", [
                'name' => 'Ujian API',
                'prompt' => 'Hello',
            ])
            ->assertCreated();

        $this->dalamTenant($this->t);
        $this->assertSame(1, AgentTestRun::where('agent_blueprint_id', $agent->id)->count());
    }

    public function test_api_publish(): void
    {
        $s = app(AgentBlueprintService::class);
        $agent = $s->janaDaripadaTemplat($this->t->id, 'sales_agent');
        $s->jalankanUjian($this->t->id, $agent->id, 'Ujian', 'Hello', 'Hello');

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/agent-studio/agents/{$agent->id}/publish")
            ->assertOk();

        $this->dalamTenant($this->t);
        $this->assertSame(AgentBlueprint::STATUS_ACTIVE, $agent->fresh()->status);
    }

    public function test_api_statistik(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/agent-studio/statistics')
            ->assertOk();
    }
}
