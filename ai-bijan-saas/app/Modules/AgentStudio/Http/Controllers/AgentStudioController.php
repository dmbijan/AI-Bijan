<?php

namespace App\Modules\AgentStudio\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\AgentStudio\Models\AgentBlueprint;
use App\Modules\AgentStudio\Models\AgentRun;
use App\Modules\AgentStudio\Models\AgentTestRun;
use App\Modules\AgentStudio\Services\AgentBlueprintService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentStudioController extends Controller
{
    public function __construct(private AgentBlueprintService $service) {}

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    /**
     * Senarai templat ejen (16).
     */
    public function templates(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->senaraiTemplat(),
        ]);
    }

    /**
     * Jana ejen daripada templat.
     */
    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'template' => ['required', 'string', 'max:60'],
            'name' => ['nullable', 'string', 'max:180'],
        ]);

        $agent = $this->service->janaDaripadaTemplat(
            $this->tenantId(),
            $validated['template'],
            $validated['name'] ?? null
        );

        return response()->json(['success' => true, 'data' => $agent], 201);
    }

    /**
     * Senarai ejen.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AgentBlueprint::query()
            ->where('tenant_id', $this->tenantId())
            ->orderBy('category')
            ->orderBy('name');

        if ($request->filled('category')) {
            $query->where('category', $request->string('category')->toString());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        return response()->json(['success' => true, 'data' => $query->get()]);
    }

    /**
     * Butiran ejen.
     */
    public function show(int $id): JsonResponse
    {
        $agent = AgentBlueprint::query()
            ->where('tenant_id', $this->tenantId())
            ->with(['tools', 'knowledge'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $agent]);
    }

    /**
     * Kemas kini ejen.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $agent = AgentBlueprint::query()
            ->where('tenant_id', $this->tenantId())
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'persona' => ['nullable', 'string', 'max:1000'],
            'system_prompt' => ['nullable', 'string', 'max:8000'],
            'capabilities' => ['nullable', 'array'],
            'autonomy' => ['nullable', 'array'],
            'escalation_rules' => ['nullable', 'array'],
            'model' => ['nullable', 'string', 'max:80'],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'max_tokens' => ['nullable', 'integer', 'min:64', 'max:32000'],
            'status' => ['nullable', 'in:draft,active,paused,archived'],
        ]);

        $agent->update($validated);

        return response()->json(['success' => true, 'data' => $agent->fresh()]);
    }

    /**
     * Ikat alat.
     */
    public function bindTool(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'tool_key' => ['required', 'string', 'max:120'],
            'tool_type' => ['nullable', 'in:internal,mcp,http'],
            'permission_level' => ['nullable', 'in:read,write,admin'],
            'tool_config' => ['nullable', 'array'],
        ]);

        $binding = $this->service->ikatAlat(
            $this->tenantId(),
            $id,
            $validated['tool_key'],
            $validated['tool_type'] ?? 'internal',
            $validated['permission_level'] ?? 'read',
            $validated['tool_config'] ?? []
        );

        return response()->json(['success' => true, 'data' => $binding], 201);
    }

    /**
     * Ikat pengetahuan.
     */
    public function bindKnowledge(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'source_type' => ['required', 'in:document,url,faq,text,vector_store'],
            'source_ref' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:180'],
            'weight' => ['nullable', 'numeric', 'min:0', 'max:10'],
        ]);

        $binding = $this->service->ikatPengetahuan(
            $this->tenantId(),
            $id,
            $validated['source_type'],
            $validated['source_ref'],
            $validated['title'] ?? null,
            (float) ($validated['weight'] ?? 1.0)
        );

        return response()->json(['success' => true, 'data' => $binding], 201);
    }

    /**
     * Jalankan ejen.
     */
    public function run(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'prompt' => ['nullable', 'string', 'max:8000'],
            'message' => ['nullable', 'string', 'max:8000'],
            'trigger' => ['nullable', 'string', 'max:80'],
            'requires_write' => ['nullable', 'boolean'],
        ]);

        $run = $this->service->jalankan(
            $this->tenantId(),
            $id,
            $validated,
            $validated['trigger'] ?? 'manual'
        );

        return response()->json(['success' => true, 'data' => $run]);
    }

    /**
     * Senarai ujian.
     */
    public function tests(int $id): JsonResponse
    {
        $tests = AgentTestRun::query()
            ->where('tenant_id', $this->tenantId())
            ->where('agent_blueprint_id', $id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $tests]);
    }

    /**
     * Jalankan ujian.
     */
    public function runTest(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'prompt' => ['nullable', 'string', 'max:8000'],
            'expected_output' => ['nullable', 'string', 'max:8000'],
        ]);

        $testRun = $this->service->jalankanUjian(
            $this->tenantId(),
            $id,
            $validated['name'],
            $validated['prompt'] ?? null,
            $validated['expected_output'] ?? null
        );

        return response()->json(['success' => true, 'data' => $testRun], 201);
    }

    /**
     * Publish ejen.
     */
    public function publish(int $id): JsonResponse
    {
        $agent = $this->service->publish($this->tenantId(), $id);

        return response()->json(['success' => true, 'data' => $agent]);
    }

    /**
     * Sejarah run.
     */
    public function runs(int $id): JsonResponse
    {
        $runs = AgentRun::query()
            ->where('tenant_id', $this->tenantId())
            ->where('agent_blueprint_id', $id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $runs]);
    }

    /**
     * Statistik.
     */
    public function statistik(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->statistik($this->tenantId()),
        ]);
    }
}
