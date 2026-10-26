<?php

namespace Tests\Feature;

use App\Modules\WorkflowStudio\Models\Workflow;
use App\Modules\WorkflowStudio\Models\WorkflowRun;
use App\Modules\WorkflowStudio\Models\WorkflowTemplate;
use App\Modules\WorkflowStudio\Services\WorkflowEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Workflow Studio (BAB 86 / Idea #26).
 * Visual workflow builder + template marketplace; dry-run lalai.
 */
class WorkflowStudioTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@wf.my', 'Workflow Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    private function definisi(array $tambah = []): array
    {
        return [
            'nodes' => [
                ['id' => 'n1', 'type' => 'trigger.manual'],
                ['id' => 'n2', 'type' => 'data.set', 'config' => ['key' => 'x', 'value' => 1]],
                ['id' => 'n3', 'type' => 'action.notify', 'config' => ['title' => 'Hello']],
            ],
            'edges' => [
                ['from' => 'n1', 'to' => 'n2'],
                ['from' => 'n2', 'to' => 'n3'],
            ],
        ] + $tambah;
    }

    private function workflow(array $ganti = []): Workflow
    {
        return Workflow::create(array_merge([
            'tenant_id' => $this->t->id,
            'name' => 'Aliran Uji',
            'definition' => $this->definisi(),
            'status' => Workflow::STATUS_ACTIVE,
            'is_enabled' => true,
            'dry_run_mode' => true,
        ], $ganti));
    }

    // -------------------------------------------------------------------
    // Katalog node
    // -------------------------------------------------------------------

    public function test_katalog_node_ada_jenis_asas(): void
    {
        $engine = app(WorkflowEngine::class);
        $katalog = $engine->katalogNode();

        $this->assertNotEmpty($katalog);

        $jenis = array_column($katalog, 'type');
        $this->assertContains('trigger.manual', $jenis);
        $this->assertContains('logic.condition', $jenis);
        $this->assertContains('ai.generate', $jenis);
        $this->assertContains('action.send_email', $jenis);
    }

    public function test_node_action_ditanda_side_effect(): void
    {
        $engine = app(WorkflowEngine::class);

        foreach ($engine->katalogNode() as $n) {
            if (str_starts_with($n['type'], 'action.')) {
                $this->assertTrue($n['side_effect'], "Node {$n['type']} mesti side_effect");
            }
        }
    }

    // -------------------------------------------------------------------
    // Pengesahan definisi
    // -------------------------------------------------------------------

    public function test_definisi_sah(): void
    {
        $engine = app(WorkflowEngine::class);

        $hasil = $engine->sahkanDefinisi($this->definisi());

        $this->assertTrue($hasil['valid']);
        $this->assertEmpty($hasil['errors']);
    }

    public function test_definisi_kosong_tidak_sah(): void
    {
        $engine = app(WorkflowEngine::class);

        $hasil = $engine->sahkanDefinisi(['nodes' => [], 'edges' => []]);

        $this->assertFalse($hasil['valid']);
        $this->assertNotEmpty($hasil['errors']);
    }

    public function test_definisi_jenis_node_tidak_sah(): void
    {
        $engine = app(WorkflowEngine::class);

        $hasil = $engine->sahkanDefinisi([
            'nodes' => [['id' => 'a', 'type' => 'jenis.tidak.wujud']],
            'edges' => [],
        ]);

        $this->assertFalse($hasil['valid']);
    }

    public function test_definisi_edge_merujuk_node_tiada(): void
    {
        $engine = app(WorkflowEngine::class);

        $hasil = $engine->sahkanDefinisi([
            'nodes' => [['id' => 'a', 'type' => 'trigger.manual']],
            'edges' => [['from' => 'a', 'to' => 'tidak-ada']],
        ]);

        $this->assertFalse($hasil['valid']);
    }

    public function test_definisi_id_pendua(): void
    {
        $engine = app(WorkflowEngine::class);

        $hasil = $engine->sahkanDefinisi([
            'nodes' => [
                ['id' => 'a', 'type' => 'trigger.manual'],
                ['id' => 'a', 'type' => 'data.set'],
            ],
            'edges' => [],
        ]);

        $this->assertFalse($hasil['valid']);
    }

    // -------------------------------------------------------------------
    // Traversal graf
    // -------------------------------------------------------------------

    public function test_node_permulaan(): void
    {
        $wf = $this->workflow();

        $this->assertSame('n1', $wf->nodePermulaan());
    }

    public function test_node_seterusnya(): void
    {
        $wf = $this->workflow();

        $this->assertSame(['n2'], $wf->nodeSeterusnya('n1'));
        $this->assertSame(['n3'], $wf->nodeSeterusnya('n2'));
        $this->assertSame([], $wf->nodeSeterusnya('n3'));
    }

    // -------------------------------------------------------------------
    // Laksana
    // -------------------------------------------------------------------

    public function test_laksana_dry_run_tiada_kesan_sampingan(): void
    {
        $engine = app(WorkflowEngine::class);
        $wf = $this->workflow(['dry_run_mode' => true]);

        $run = $engine->laksana($this->t->id, $wf->id);

        $this->assertSame(WorkflowRun::STATUS_COMPLETED, $run->status);
        $this->assertSame(3, $run->nodes_executed);
        $this->assertSame(0, $run->nodes_failed);
    }

    public function test_laksana_merekod_hasil_node(): void
    {
        $engine = app(WorkflowEngine::class);
        $wf = $this->workflow();

        $run = $engine->laksana($this->t->id, $wf->id);

        $this->assertIsArray($run->node_results);
        $this->assertCount(3, $run->node_results);
        $this->assertSame('ok', $run->node_results[0]['status']);
        $this->assertTrue($run->node_results[0]['dry_run']);
    }

    public function test_laksana_naik_kiraan_run(): void
    {
        $engine = app(WorkflowEngine::class);
        $wf = $this->workflow();

        $engine->laksana($this->t->id, $wf->id);

        $this->assertSame(1, $wf->fresh()->run_count);
        $this->assertSame(1, $wf->fresh()->success_count);
        $this->assertNotNull($wf->fresh()->last_run_at);
    }

    public function test_laksana_gagal_bila_tiada_node_permulaan(): void
    {
        $engine = app(WorkflowEngine::class);

        $wf = Workflow::create([
            'tenant_id' => $this->t->id,
            'name' => 'Kosong',
            'definition' => ['nodes' => [], 'edges' => []],
            'status' => Workflow::STATUS_ACTIVE,
            'is_enabled' => true,
        ]);

        $run = $engine->laksana($this->t->id, $wf->id);

        $this->assertSame(WorkflowRun::STATUS_FAILED, $run->status);
        $this->assertNotNull($run->error_message);
    }

    public function test_laksana_guna_konteks_payload(): void
    {
        $engine = app(WorkflowEngine::class);
        $wf = $this->workflow();

        $run = $engine->laksana($this->t->id, $wf->id, ['foo' => 'bar']);

        $this->assertSame(['foo' => 'bar'], $run->trigger_payload);
    }

    // -------------------------------------------------------------------
    // Template
    // -------------------------------------------------------------------

    public function test_pasang_template(): void
    {
        $engine = app(WorkflowEngine::class);

        $tmpl = WorkflowTemplate::create([
            'key' => 'tpl_ujian',
            'name' => 'Template Ujian',
            'definition' => $this->definisi(),
            'is_official' => true,
            'is_active' => true,
        ]);

        $wf = $engine->pasangTemplate($this->t->id, $tmpl->id);

        $this->assertSame($tmpl->id, $wf->workflow_template_id);
        $this->assertSame(Workflow::STATUS_DRAFT, $wf->status);
        $this->assertFalse($wf->is_enabled);
        $this->assertTrue($wf->dry_run_mode);
        $this->assertSame(1, $tmpl->fresh()->install_count);
    }

    public function test_template_tenant_lain_tidak_boleh_dipasang(): void
    {
        $engine = app(WorkflowEngine::class);

        [$u2, $t2] = $this->daftar('lawan@wf.my', 'Lawan Sdn Bhd');

        $tmpl = WorkflowTemplate::create([
            'tenant_id' => $t2->id,
            'key' => 'tpl_lawan',
            'name' => 'Template Lawan',
            'definition' => $this->definisi(),
            'is_active' => true,
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $engine->pasangTemplate($this->t->id, $tmpl->id);
    }

    // -------------------------------------------------------------------
    // Ringkasan
    // -------------------------------------------------------------------

    public function test_ringkasan(): void
    {
        $engine = app(WorkflowEngine::class);
        $wf = $this->workflow();
        $engine->laksana($this->t->id, $wf->id);

        $r = $engine->ringkasan($this->t->id);

        $this->assertSame(1, $r['jumlah_workflow']);
        $this->assertSame(1, $r['aktif']);
        $this->assertSame(1, $r['run_30_hari']);
        $this->assertSame(1, $r['berjaya']);
        $this->assertSame(100.0, $r['kadar_berjaya']);
    }

    // -------------------------------------------------------------------
    // Tenant isolation
    // -------------------------------------------------------------------

    public function test_workflow_tenant_lain_tidak_nampak(): void
    {
        $wf = $this->workflow();

        [$u2, $t2] = $this->daftar('lawan2@wf.my', 'Lawan Dua Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertNull(Workflow::find($wf->id));
    }

    // -------------------------------------------------------------------
    // API
    // -------------------------------------------------------------------

    public function test_api_node_types(): void
    {
        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/workflows/node-types')
            ->assertOk();

        $this->assertNotEmpty($res->json('data'));
    }

    public function test_api_cipta_workflow(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/workflows', [
                'name' => 'Aliran API',
                'definition' => $this->definisi(),
            ])
            ->assertCreated();

        $this->dalamTenant($this->t);
        $this->assertSame(1, Workflow::where('tenant_id', $this->t->id)->count());
    }

    public function test_api_cipta_workflow_definisi_tidak_sah(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/workflows', [
                'name' => 'Rosak',
                'definition' => ['nodes' => [], 'edges' => []],
            ])
            ->assertStatus(422);
    }

    public function test_api_run_workflow(): void
    {
        $wf = $this->workflow();

        $res = $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/workflows/{$wf->id}/run")
            ->assertOk();

        $this->assertSame('completed', $res->json('data.status'));
    }

    public function test_api_sejarah_run(): void
    {
        $wf = $this->workflow();
        app(WorkflowEngine::class)->laksana($this->t->id, $wf->id);

        $res = $this->sebagai($this->u, $this->t)
            ->getJson("/api/v1/workflows/{$wf->id}/runs")
            ->assertOk();

        $this->assertCount(1, $res->json('data'));
    }

    public function test_api_templates(): void
    {
        WorkflowTemplate::create([
            'key' => 'x',
            'name' => 'X',
            'definition' => $this->definisi(),
            'is_active' => true,
        ]);

        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/workflows/templates')
            ->assertOk();

        $this->assertCount(1, $res->json('data'));
    }

    public function test_api_summary(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/workflows/summary')
            ->assertOk();
    }
}
