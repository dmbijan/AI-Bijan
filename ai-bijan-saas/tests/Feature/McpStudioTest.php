<?php

namespace Tests\Feature;

use App\Modules\McpStudio\Models\McpAuditLog;
use App\Modules\McpStudio\Models\McpExecution;
use App\Modules\McpStudio\Models\McpPrompt;
use App\Modules\McpStudio\Models\McpResource;
use App\Modules\McpStudio\Models\McpSession;
use App\Modules\McpStudio\Models\McpTool;
use App\Modules\McpStudio\Services\McpServerService;
use App\Modules\McpStudio\Services\McpToolRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * MCP Studio (BAB 84 / Idea #1).
 *
 * AI MCP Studio — sambungkan mana-mana AI luar (Claude Desktop, Cursor,
 * n8n, Zapier) kepada AI BIJAN melalui protokol MCP.
 *
 * Fokus keselamatan: allowlist, kellunixuman manusia, jejak audit.
 */
class McpStudioTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@mcp.my', 'MCP Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    private function alat(array $ganti = []): McpTool
    {
        return McpTool::create(array_merge([
            'name' => 'list_prospects',
            'title' => 'Senarai Prospek',
            'description' => 'Dapatkan senarai prospek',
            'source_module' => 'prospecting',
            'action' => 'list',
            'status' => 'active',
            'requires_approval' => false,
            'is_destructive' => false,
        ], $ganti));
    }

    // -------------------------------------------------------------------
    // Pendaftaran alat
    // -------------------------------------------------------------------

    public function test_daftar_alat(): void
    {
        $s = app(McpToolRegistry::class);

        $tool = $s->daftar(
            $this->t->id,
            'get_orders',
            'Dapatkan Pesanan',
            'Baca senarai pesanan',
            'marketplace',
            'list'
        );

        $this->assertSame('get_orders', $tool->name);
        $this->assertSame('active', $tool->status);
        $this->assertTrue($tool->isActive());
    }

    public function test_daftar_alat_idempotent(): void
    {
        $s = app(McpToolRegistry::class);

        $a = $s->daftar($this->t->id, 'dup_tool', 'T dup', null, 'crm', 'list');
        $b = $s->daftar($this->t->id, 'dup_tool', 'T dup', null, 'crm', 'list');

        $this->assertSame($a->id, $b->id);
        $this->assertSame(1, McpTool::where('tenant_id', $this->t->id)->where('name', 'dup_tool')->count());
    }

    public function test_senarai_alat(): void
    {
        $s = app(McpToolRegistry::class);
        $this->alat();

        $senarai = $s->senaraiAlat($this->t->id);

        $this->assertCount(1, $senarai);
        $this->assertSame('list_prospects', $senarai[0]['name']);
    }

    // -------------------------------------------------------------------
    // Allowlist keselamatan
    // -------------------------------------------------------------------

    public function test_tindakan_dibenarkan(): void
    {
        $s = app(McpToolRegistry::class);

        $this->assertTrue($s->dibenarkan('prospecting', 'list'));
        $this->assertTrue($s->dibenarkan('crm', 'create'));
    }

    public function test_tindakan_tidak_dibenarkan(): void
    {
        $s = app(McpToolRegistry::class);

        $this->assertFalse($s->dibenarkan('prospecting', 'delete'));
        $this->assertFalse($s->dibenarkan('unknown_module', 'list'));
        $this->assertFalse($s->dibenarkan(null, 'list'));
    }

    public function test_panggil_alat_tak_dibenarkan_ditolak(): void
    {
        $s = app(McpToolRegistry::class);
        $this->alat(['source_module' => 'prospecting', 'action' => 'delete']);

        $this->expectException(\RuntimeException::class);
        $s->panggil($this->t->id, 'list_prospects', []);
    }

    public function test_panggil_alat_tiada_ditolak(): void
    {
        $s = app(McpToolRegistry::class);

        $this->expectException(\InvalidArgumentException::class);
        $s->panggil($this->t->id, 'tidak_ada', []);
    }

    // -------------------------------------------------------------------
    // Validasi parameter
    // -------------------------------------------------------------------

    public function test_validasi_parameter_wajib(): void
    {
        $tool = $this->alat([
            'parameters_schema' => [
                'type' => 'object',
                'properties' => ['id' => ['type' => 'integer']],
                'required' => ['id'],
            ],
        ]);

        $hasil = $tool->validasiArgumen([]);

        $this->assertFalse($hasil['valid']);
        $this->assertNotEmpty($hasil['errors']);
    }

    public function test_validasi_parameter_jenis(): void
    {
        $tool = $this->alat([
            'parameters_schema' => [
                'type' => 'object',
                'properties' => ['id' => ['type' => 'integer']],
                'required' => ['id'],
            ],
        ]);

        $hasil = $tool->validasiArgumen(['id' => 'bukan nombor']);

        $this->assertFalse($hasil['valid']);
    }

    public function test_validasi_parameter_lulus(): void
    {
        $tool = $this->alat([
            'parameters_schema' => [
                'type' => 'object',
                'properties' => ['id' => ['type' => 'integer']],
                'required' => ['id'],
            ],
        ]);

        $hasil = $tool->validasiArgumen(['id' => 5]);

        $this->assertTrue($hasil['valid']);
        $this->assertEmpty($hasil['errors']);
    }

    public function test_panggil_dengan_argumen_tak_sah_ditolak(): void
    {
        $s = app(McpToolRegistry::class);
        $this->alat([
            'parameters_schema' => [
                'type' => 'object',
                'properties' => ['id' => ['type' => 'integer']],
                'required' => ['id'],
            ],
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $s->panggil($this->t->id, 'list_prospects', []);
    }

    // -------------------------------------------------------------------
    // Human-in-the-loop
    // -------------------------------------------------------------------

    public function test_alat_destructive_menunggu_kelulusan(): void
    {
        $s = app(McpToolRegistry::class);
        $this->alat([
            'requires_approval' => true,
            'is_destructive' => true,
            'action' => 'update',
            'source_module' => 'crm',
        ]);

        $execution = $s->panggil($this->t->id, 'list_prospects', []);

        $this->assertSame(McpExecution::STATUS_AWAITING_APPROVAL, $execution->status);
        $this->assertSame(McpExecution::APPROVAL_PENDING, $execution->approval_status);
        $this->assertTrue($execution->menungguKelulusan());
    }

    public function test_alat_biasa_langsung_laksana(): void
    {
        $s = app(McpToolRegistry::class);
        $this->alat(['requires_approval' => false, 'is_destructive' => false]);

        $execution = $s->panggil($this->t->id, 'list_prospects', []);

        $this->assertSame(McpExecution::STATUS_EXECUTED, $execution->status);
        $this->assertTrue($execution->berjaya());
    }

    public function test_luluskan_pelaksanaan(): void
    {
        $s = app(McpToolRegistry::class);
        $this->alat([
            'requires_approval' => true,
            'is_destructive' => true,
            'action' => 'update',
            'source_module' => 'crm',
        ]);

        $execution = $s->panggil($this->t->id, 'list_prospects', []);
        $selesai = $s->luluskan($this->t->id, $execution->id, $this->u->id);

        $this->assertSame(McpExecution::APPROVAL_APPROVED, $selesai->approval_status);
        $this->assertNotNull($selesai->approved_at);
    }

    public function test_tolak_pelaksanaan(): void
    {
        $s = app(McpToolRegistry::class);
        $this->alat([
            'requires_approval' => true,
            'is_destructive' => true,
            'action' => 'update',
            'source_module' => 'crm',
        ]);

        $execution = $s->panggil($this->t->id, 'list_prospects', []);
        $ditolak = $s->tolak($this->t->id, $execution->id, 'Tidak dibenarkan');

        $this->assertSame(McpExecution::APPROVAL_REJECTED, $ditolak->approval_status);
        $this->assertSame(McpExecution::STATUS_REJECTED, $ditolak->status);
        $this->assertSame('Tidak dibenarkan', $ditolak->rejection_reason);
    }

    public function test_luluskan_pelaksanaan_yang_tidak_menunggu_gagal(): void
    {
        $s = app(McpToolRegistry::class);
        $this->alat(['requires_approval' => false, 'is_destructive' => false]);

        $execution = $s->panggil($this->t->id, 'list_prospects', []);

        $this->expectException(\RuntimeException::class);
        $s->luluskan($this->t->id, $execution->id);
    }

    public function test_senarai_menunggu_kelulusan(): void
    {
        $s = app(McpToolRegistry::class);
        $this->alat([
            'requires_approval' => true,
            'is_destructive' => true,
            'action' => 'update',
            'source_module' => 'crm',
        ]);

        $s->panggil($this->t->id, 'list_prospects', []);

        $this->assertCount(1, $s->senaraiMenungguKelulusan($this->t->id));
    }

    // -------------------------------------------------------------------
    // Audit
    // -------------------------------------------------------------------

    public function test_audit_direkod_semasa_panggilan(): void
    {
        $s = app(McpToolRegistry::class);
        $this->alat(['requires_approval' => false, 'is_destructive' => false]);

        $s->panggil($this->t->id, 'list_prospects', []);

        $this->assertGreaterThan(0, McpAuditLog::where('tenant_id', $this->t->id)->count());
    }

    public function test_audit_tenant_lain_tidak_nampak(): void
    {
        $s = app(McpToolRegistry::class);
        $this->alat(['requires_approval' => false, 'is_destructive' => false]);
        $s->panggil($this->t->id, 'list_prospects', []);

        [$u2, $t2] = $this->daftar('lawan@mcp.my', 'Lawan Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertSame(0, McpAuditLog::where('tenant_id', $t2->id)->count());
    }

    // -------------------------------------------------------------------
    // Sumber & prompt
    // -------------------------------------------------------------------

    public function test_baca_resource(): void
    {
        $s = app(McpToolRegistry::class);

        McpResource::create([
            'tenant_id' => $this->t->id,
            'uri' => 'crm://customers',
            'name' => 'Pelanggan',
            'mime_type' => 'application/json',
            'source_table' => 'customers',
            'status' => 'active',
        ]);

        $data = $s->bacaResource($this->t->id, 'crm://customers');

        $this->assertSame('crm://customers', $data['uri']);
        $this->assertSame('application/json', $data['mimeType']);
    }

    public function test_baca_resource_tiada_ditolak(): void
    {
        $s = app(McpToolRegistry::class);

        $this->expectException(\InvalidArgumentException::class);
        $s->bacaResource($this->t->id, 'tidak://ada');
    }

    public function test_render_prompt_ganti_argumen(): void
    {
        $prompt = McpPrompt::create([
            'tenant_id' => $this->t->id,
            'name' => 'ringkasan_pesanan',
            'title' => 'Ringkasan Pesanan',
            'template' => 'Ringkaskan pesanan {{order_no}} untuk pelanggan {{customer}}.',
            'arguments_schema' => [
                'type' => 'object',
                'properties' => [
                    'order_no' => ['type' => 'string'],
                    'customer' => ['type' => 'string'],
                ],
                'required' => ['order_no'],
            ],
            'status' => 'active',
        ]);

        $hasil = $prompt->render(['order_no' => 'ORD-1', 'customer' => 'Ahmad']);

        $this->assertStringContainsString('ORD-1', $hasil);
        $this->assertStringContainsString('Ahmad', $hasil);
        $this->assertStringNotContainsString('{{order_no}}', $hasil);
    }

    public function test_prompt_argument_wajib(): void
    {
        $prompt = McpPrompt::create([
            'tenant_id' => $this->t->id,
            'name' => 'p2',
            'title' => 'P2',
            'template' => 'x',
            'arguments_schema' => ['required' => ['a']],
            'status' => 'active',
        ]);

        $this->assertSame(['a'], $prompt->argumentWajib());
    }

    // -------------------------------------------------------------------
    // Pelayan MCP
    // -------------------------------------------------------------------

    public function test_initialize_returns_protocol(): void
    {
        $s = app(McpServerService::class);

        $hasil = $s->initialize($this->t->id, 'Claude Desktop', '1.0.0');

        $this->assertSame(McpServerService::PROTOCOL_VERSION, $hasil['protocolVersion']);
        $this->assertArrayHasKey('serverInfo', $hasil);
        $this->assertArrayHasKey('sessionId', $hasil);
    }

    public function test_initialize_cipta_sesi(): void
    {
        $s = app(McpServerService::class);
        $s->initialize($this->t->id, 'Cursor');

        $this->assertSame(1, McpSession::where('tenant_id', $this->t->id)->count());
    }

    public function test_list_tools(): void
    {
        $s = app(McpServerService::class);
        $this->alat();

        $hasil = $s->listTools($this->t->id);

        $this->assertArrayHasKey('tools', $hasil);
        $this->assertCount(1, $hasil['tools']);
    }

    public function test_list_prompts(): void
    {
        $s = app(McpServerService::class);

        McpPrompt::create([
            'tenant_id' => $this->t->id,
            'name' => 'p1',
            'title' => 'P1',
            'template' => 'x',
            'status' => 'active',
        ]);

        $hasil = $s->listPrompts($this->t->id);

        $this->assertCount(1, $hasil['prompts']);
    }

    public function test_get_prompt(): void
    {
        $s = app(McpServerService::class);

        McpPrompt::create([
            'tenant_id' => $this->t->id,
            'name' => 'p3',
            'title' => 'P3',
            'template' => 'Hello {{nama}}',
            'status' => 'active',
        ]);

        $hasil = $s->getPrompt($this->t->id, 'p3', ['nama' => 'Ali']);

        $this->assertStringContainsString('Hello Ali', $hasil['messages'][0]['content']['text']);
    }

    public function test_statistik(): void
    {
        $s = app(McpServerService::class);
        $this->alat(['requires_approval' => false, 'is_destructive' => false]);
        app(McpToolRegistry::class)->panggil($this->t->id, 'list_prospects', []);

        $stat = $s->statistik($this->t->id);

        $this->assertSame(1, $stat['jumlah_alat']);
        $this->assertSame(1, $stat['jumlah_panggilan']);
        $this->assertSame(100.0, $stat['kadar_keputusan']);
    }

    public function test_konfigurasi_klien(): void
    {
        $s = app(McpServerService::class);

        $config = $s->konfigurasiKlien($this->t->id, 'https://api.aibijan.com', 'token123');

        $this->assertArrayHasKey('mcpServers', $config);
        $this->assertArrayHasKey('ai-bijan', $config['mcpServers']);
    }

    // -------------------------------------------------------------------
    // API
    // -------------------------------------------------------------------

    public function test_api_initialize(): void
    {
        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/mcp/initialize', ['client_name' => 'Claude Desktop'])
            ->assertOk();

        $this->assertSame(McpServerService::PROTOCOL_VERSION, $res->json('protocolVersion'));
    }

    public function test_api_rpc_tools_list(): void
    {
        $this->alat();

        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/mcp/rpc', [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'tools/list',
                'params' => [],
            ])
            ->assertOk();

        $this->assertArrayHasKey('tools', $res->json('result'));
    }

    public function test_api_rpc_kaedah_tidak_disenaraikan(): void
    {
        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/mcp/rpc', [
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'tidak/diserahkan',
                'params' => [],
            ])
            ->assertStatus(400);

        $this->assertArrayHasKey('error', $res->json());
    }

    public function test_api_daftar_alat(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/mcp/tools', [
                'name' => 'api_tool',
                'title' => 'Alat API',
                'source_module' => 'crm',
                'action' => 'list',
            ])
            ->assertCreated();

        $this->dalamTenant($this->t);
        $this->assertSame(1, McpTool::where('tenant_id', $this->t->id)->count());
    }

    public function test_api_call_tool(): void
    {
        $this->alat(['requires_approval' => false, 'is_destructive' => false]);

        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/mcp/tools/call', [
                'name' => 'list_prospects',
                'arguments' => [],
            ])
            ->assertOk();

        $this->assertSame('executed', $res->json('data.status'));
    }

    public function test_api_call_tool_gagal_bila_modul_tiada_model(): void
    {
        // 'analytics' ada dalam ALLOWLIST tetapi tiada kelas Eloquent
        $this->alat([
            'name' => 'analytics_tool',
            'source_module' => 'analytics',
            'action' => 'list',
            'requires_approval' => false,
            'is_destructive' => false,
        ]);

        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/mcp/tools/call', [
                'name' => 'analytics_tool',
                'arguments' => [],
            ])
            ->assertOk();

        $this->assertSame('failed', $res->json('data.status'));
    }

    public function test_api_call_tool_menunggu_kelulusan(): void
    {
        $this->alat([
            'requires_approval' => true,
            'is_destructive' => true,
            'action' => 'update',
            'source_module' => 'crm',
        ]);

        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/mcp/tools/call', [
                'name' => 'list_prospects',
                'arguments' => [],
            ])
            ->assertOk();

        $this->assertSame('awaiting_approval', $res->json('data.status'));
    }

    public function test_api_approvals_lulus(): void
    {
        $s = app(McpToolRegistry::class);
        $this->alat([
            'requires_approval' => true,
            'is_destructive' => true,
            'action' => 'update',
            'source_module' => 'crm',
        ]);

        $execution = $s->panggil($this->t->id, 'list_prospects', []);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/mcp/approvals/{$execution->id}/approve")
            ->assertOk();

        $this->assertSame(
            McpExecution::APPROVAL_APPROVED,
            $execution->fresh()->approval_status
        );
    }

    public function test_api_daftar_prompt(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/mcp/prompts', [
                'name' => 'api_prompt',
                'title' => 'Prompt API',
                'template' => 'Hello {{nama}}',
            ])
            ->assertCreated();

        $this->dalamTenant($this->t);
        $this->assertSame(1, McpPrompt::where('tenant_id', $this->t->id)->count());
    }

    public function test_api_statistik(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/mcp/statistics')
            ->assertOk();
    }

    public function test_api_konfigurasi(): void
    {
        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/mcp/config')
            ->assertOk();

        $this->assertArrayHasKey('mcpServers', $res->json('data'));
    }
}
