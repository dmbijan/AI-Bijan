<?php

namespace App\Modules\McpStudio\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\McpStudio\Models\McpExecution;
use App\Modules\McpStudio\Models\McpPrompt;
use App\Modules\McpStudio\Models\McpResource;
use App\Modules\McpStudio\Models\McpTool;
use App\Modules\McpStudio\Services\McpServerService;
use App\Modules\McpStudio\Services\McpToolRegistry;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class McpStudioController extends Controller
{
    public function __construct(
        private McpToolRegistry $registry,
        private McpServerService $server
    ) {}

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    /**
     * Inisialisasi sesi MCP.
     */
    public function initialize(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_name' => ['required', 'string', 'max:120'],
            'client_version' => ['nullable', 'string', 'max:40'],
            'transport' => ['nullable', 'in:http,stdio'],
        ]);

        $hasil = $this->server->initialize(
            $this->tenantId(),
            $validated['client_name'],
            $validated['client_version'] ?? null,
            $validated['transport'] ?? 'http'
        );

        return response()->json($hasil);
    }

    /**
     * JSON-RPC endpoint tunggal (MCP).
     */
    public function rpc(Request $request): JsonResponse
    {
        $method = $request->input('method');
        $params = $request->input('params', []);
        $id = $request->input('id');

        try {
            $result = match ($method) {
                'initialize' => $this->server->initialize(
                    $this->tenantId(),
                    $params['client_name'] ?? 'unknown',
                    $params['client_version'] ?? null
                ),
                'tools/list' => $this->server->listTools($this->tenantId(), $params['module'] ?? null),
                'tools/call' => $this->server->callTool(
                    $this->tenantId(),
                    $params['name'] ?? '',
                    $params['arguments'] ?? [],
                    $params['session_id'] ?? null
                ),
                'resources/list' => $this->server->listResources($this->tenantId()),
                'resources/read' => $this->server->readResource($this->tenantId(), $params['uri'] ?? ''),
                'prompts/list' => $this->server->listPrompts($this->tenantId()),
                'prompts/get' => $this->server->getPrompt($this->tenantId(), $params['name'] ?? '', $params['arguments'] ?? []),
                'ping' => ['ok' => true],
                default => throw new \InvalidArgumentException("Kaedah MCP tidak disokong: {$method}"),
            };

            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'jsonrpc' => '2.0',
                'id' => $id,
                'error' => [
                    'code' => -32603,
                    'message' => $e->getMessage(),
                ],
            ], 400);
        }
    }

    /**
     * Senarai alat.
     */
    public function tools(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->registry->senaraiAlat($this->tenantId(), $request->input('module')),
        ]);
    }

    /**
     * Daftar alat baharu.
     */
    public function daftarTool(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9_\-]+$/i'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'source_module' => ['nullable', 'string', 'max:60'],
            'action' => ['nullable', 'string', 'max:60'],
            'parameters_schema' => ['nullable', 'array'],
            'requires_approval' => ['nullable', 'boolean'],
            'is_destructive' => ['nullable', 'boolean'],
        ]);

        $tool = $this->registry->daftar(
            $this->tenantId(),
            $validated['name'],
            $validated['title'],
            $validated['description'] ?? null,
            $validated['source_module'] ?? null,
            $validated['action'] ?? null,
            null,
            $validated['parameters_schema'] ?? [],
            $validated['requires_approval'] ?? true,
            $validated['is_destructive'] ?? false
        );

        return response()->json(['success' => true, 'data' => $tool], 201);
    }

    /**
     * Panggil alat.
     */
    public function callTool(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'arguments' => ['nullable', 'array'],
            'session_id' => ['nullable', 'integer'],
        ]);

        $hasil = $this->server->callTool(
            $this->tenantId(),
            $validated['name'],
            $validated['arguments'] ?? [],
            $validated['session_id'] ?? null
        );

        return response()->json(['success' => true, 'data' => $hasil]);
    }

    /**
     * Senarai pelaksanaan menunggu kelulusan.
     */
    public function pendingApprovals(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->registry->senaraiMenungguKelulusan($this->tenantId()),
        ]);
    }

    /**
     * Luluskan pelaksanaan.
     */
    public function approve(int $id): JsonResponse
    {
        $execution = $this->registry->luluskan($this->tenantId(), $id);

        return response()->json(['success' => true, 'data' => $execution]);
    }

    /**
     * Tolak pelaksanaan.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $execution = $this->registry->tolak($this->tenantId(), $id, $validated['reason'] ?? null);

        return response()->json(['success' => true, 'data' => $execution]);
    }

    /**
     * Senarai sumber.
     */
    public function resources(): JsonResponse
    {
        $resources = McpResource::query()
            ->where('tenant_id', $this->tenantId())
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $resources]);
    }

    /**
     * Daftar sumber.
     */
    public function storeResource(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uri' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:180'],
            'mime_type' => ['nullable', 'string', 'max:120'],
            'source_table' => ['nullable', 'string', 'max:120'],
            'columns' => ['nullable', 'array'],
        ]);

        $resource = McpResource::updateOrCreate(
            ['tenant_id' => $this->tenantId(), 'uri' => $validated['uri']],
            array_merge($validated, ['tenant_id' => $this->tenantId()])
        );

        return response()->json(['success' => true, 'data' => $resource], 201);
    }

    /**
     * Senarai prompt.
     */
    public function prompts(): JsonResponse
    {
        $prompts = McpPrompt::query()
            ->where('tenant_id', $this->tenantId())
            ->orderBy('name')
            ->get();

        return response()->json(['success' => true, 'data' => $prompts]);
    }

    /**
     * Daftar prompt.
     */
    public function storePrompt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'regex:/^[a-z0-9_\-]+$/i'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'template' => ['required', 'string'],
            'arguments_schema' => ['nullable', 'array'],
        ]);

        $prompt = McpPrompt::updateOrCreate(
            ['tenant_id' => $this->tenantId(), 'name' => $validated['name']],
            array_merge($validated, ['tenant_id' => $this->tenantId()])
        );

        return response()->json(['success' => true, 'data' => $prompt], 201);
    }

    /**
     * Statistik aktiviti.
     */
    public function statistik(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->server->statistik($this->tenantId()),
        ]);
    }

    /**
     * Konfigurasi untuk klien MCP luaran.
     */
    public function konfigurasi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'api_url' => ['nullable', 'url'],
        ]);

        $apiUrl = $validated['api_url'] ?? rtrim((string) config('app.url'), '/');

        return response()->json([
            'success' => true,
            'data' => $this->server->konfigurasiKlien($this->tenantId(), $apiUrl),
        ]);
    }
}
