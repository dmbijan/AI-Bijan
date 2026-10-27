<?php

namespace App\Modules\VirtualOffice\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\VirtualOffice\Services\AgentService;
use Illuminate\Http\Request;

class AgentController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$request->tenant_id` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected $agentService;

    public function __construct(AgentService $agentService)
    {
        $this->agentService = $agentService;
    }

    /**
     * List all agents for the tenant.
     */
    public function index(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();
        $type = $request->query('type'); // human, ai, hybrid
        $status = $request->query('status'); // idle, active, busy, offline

        $query = \App\Modules\VirtualOffice\Models\Agent::where('tenant_id', $tenantId);

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $agents = $query->orderBy('name')->get();

        return response()->json($agents);
    }

    /**
     * Get a single agent.
     */
    public function show(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $agent = \App\Modules\VirtualOffice\Models\Agent::where('tenant_id', $tenantId)
            ->with(['user', 'tasks', 'schedules'])
            ->findOrFail($id);

        return response()->json($agent);
    }

    /**
     * Create a new agent.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:vo_agents,email',
            'type' => 'sometimes|in:human,ai,hybrid',
            'role' => 'nullable|string|max:100',
            'avatar_url' => 'nullable|url',
            'status' => 'sometimes|in:idle,active,busy,offline',
            'metadata' => 'nullable|array',
        ]);

        $tenantId = app(TenantContext::class)->id();

        $agent = $this->agentService->create(array_merge($request->all(), [
            'tenant_id' => $tenantId,
            'user_id' => $request->user()->id,
        ]), $tenantId);

        return response()->json($agent, 201);
    }

    /**
     * Update an agent.
     */
    public function update(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $agent = \App\Modules\VirtualOffice\Models\Agent::where('tenant_id', $tenantId)->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:vo_agents,email,' . $id,
            'type' => 'sometimes|in:human,ai,hybrid',
            'role' => 'nullable|string|max:100',
            'avatar_url' => 'nullable|url',
            'status' => 'sometimes|in:idle,active,busy,offline',
            'metadata' => 'nullable|array',
        ]);

        $agent = $this->agentService->update($agent, $request->all());

        return response()->json($agent);
    }

    /**
     * Delete an agent.
     */
    public function destroy(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $agent = \App\Modules\VirtualOffice\Models\Agent::where('tenant_id', $tenantId)->findOrFail($id);

        $this->agentService->deleteAgent($agent);

        return response()->json(['message' => 'Agent deleted successfully']);
    }

    /**
     * Update agent status.
     */
    public function updateStatus(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $agent = \App\Modules\VirtualOffice\Models\Agent::where('tenant_id', $tenantId)->findOrFail($id);

        $request->validate([
            'status' => 'required|in:idle,active,busy,offline',
        ]);

        $agent = $this->agentService->setStatus($agent, $request->status);

        return response()->json($agent);
    }

    /**
     * Get agent statistics.
     */
    public function statistics(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();
        $stats = $this->agentService->getAgentStatistics($tenantId);

        return response()->json($stats);
    }
}
