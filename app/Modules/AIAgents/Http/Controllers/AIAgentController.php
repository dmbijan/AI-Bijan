<?php

namespace App\Modules\AIAgents\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\AIAgents\Services\AgentService;
use App\Modules\AIAgents\Services\AgentOrchestrator;
use App\Modules\AIAgents\Services\ToolExecutor;
use App\Modules\AIAgents\Services\AgentMemoryManager;
use App\Modules\AIAgents\Services\AgentGovernance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AIAgentController extends Controller
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
    protected $orchestrator;
    protected $toolExecutor;
    protected $memoryManager;
    protected $governance;

    public function __construct(
        AgentService $agentService,
        AgentOrchestrator $orchestrator,
        ToolExecutor $toolExecutor,
        AgentMemoryManager $memoryManager,
        AgentGovernance $governance
    ) {
        $this->agentService = $agentService;
        $this->orchestrator = $orchestrator;
        $this->toolExecutor = $toolExecutor;
        $this->memoryManager = $memoryManager;
        $this->governance = $governance;
    }

    /**
     * List all available AI agents
     */
    public function listAgents(): JsonResponse
    {
        $agents = $this->agentService->getAvailableAgents();
        $formatted = [];

        foreach ($agents as $key => $agent) {
            $formatted[] = [
                'key' => $key,
                'id' => $agent['id'],
                'name' => $agent['name'],
                'description' => $agent['description'],
                'role' => $agent['role'],
                'tools' => $agent['tools'],
                'budget_limits' => $agent['budget_limits'],
                'approval_required' => $agent['approval_required_for'],
                'data_access' => $agent['data_access'],
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Get agent details
     */
    public function getAgent(string $agentKey): JsonResponse
    {
        $agent = $this->agentService->getAgent($agentKey);

        if (!$agent) {
            return response()->json([
                'success' => false,
                'message' => "Agent '{$agentKey}' not found.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $agent,
        ]);
    }

    /**
     * List tenant's configured agents
     */
    public function listTenantAgents(): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();
        $agents = $this->agentService->listTenantAgents($tenantId);

        return response()->json([
            'success' => true,
            'data' => $agents,
        ]);
    }

    /**
     * Configure agent for tenant
     */
    public function configureAgent(Request $request, string $agentKey): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        $customConfig = [];
        if ($request->has('system_prompt')) {
            $customConfig['system_prompt'] = $request->system_prompt;
        }
        if ($request->has('tools')) {
            $customConfig['tools'] = $request->tools;
        }
        if ($request->has('budget_limits')) {
            $customConfig['budget_limits'] = $request->budget_limits;
        }

        try {
            $agent = $this->agentService->configureAgent($tenantId, $agentKey, $customConfig);

            return response()->json([
                'success' => true,
                'message' => "Agent '{$agentKey}' configured successfully.",
                'data' => $agent,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update agent configuration
     */
    public function updateAgent(Request $request, string $agentKey): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        try {
            $agent = $this->agentService->updateAgent($tenantId, $agentKey, $request->all());

            return response()->json([
                'success' => true,
                'message' => "Agent '{$agentKey}' updated successfully.",
                'data' => $agent,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Disable agent
     */
    public function disableAgent(string $agentKey): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        try {
            $agent = $this->agentService->disableAgent($tenantId, $agentKey);

            return response()->json([
                'success' => true,
                'message' => "Agent '{$agentKey}' disabled.",
                'data' => $agent,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Enable agent
     */
    public function enableAgent(string $agentKey): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        try {
            $agent = $this->agentService->enableAgent($tenantId, $agentKey);

            return response()->json([
                'success' => true,
                'message' => "Agent '{$agentKey}' enabled.",
                'data' => $agent,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reset agent to default configuration
     */
    public function resetAgent(string $agentKey): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        try {
            $agent = $this->agentService->resetToDefault($tenantId, $agentKey);

            return response()->json([
                'success' => true,
                'message' => "Agent '{$agentKey}' reset to defaults.",
                'data' => $agent,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get agent statistics
     */
    public function getAgentStats(string $agentKey): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        try {
            $stats = $this->agentService->getAgentStats($tenantId, $agentKey);

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Recommend agents based on industry
     */
    public function recommendAgents(Request $request): JsonResponse
    {
        $request->validate([
            'industry_key' => 'required|string',
        ]);

        $recommendations = $this->agentService->recommendByIndustry($request->industry_key);

        return response()->json([
            'success' => true,
            'data' => $recommendations,
        ]);
    }

    /**
     * Execute agent task
     */
    public function executeTask(Request $request): JsonResponse
    {
        $request->validate([
            'agent_key' => 'required|string',
            'task_type' => 'required|string',
            'objective' => 'required|string',
            'input' => 'nullable|array',
        ]);

        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        // Validate governance before execution
        $validation = $this->governance->validateAction(
            $tenantId,
            $request->agent_key,
            $request->task_type,
            $request->input ?? []
        );

        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Governance validation failed.',
                'violations' => $validation['violations'],
            ], 403);
        }

        try {
            $result = $this->orchestrator->orchestrateWorkflow($tenantId, [
                'type' => $request->task_type,
                'objective' => $request->objective,
                'input' => $request->input ?? [],
                'actions' => [$request->task_type],
            ], [$request->agent_key]);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Execute tool call
     */
    public function executeTool(Request $request): JsonResponse
    {
        $request->validate([
            'agent_key' => 'required|string',
            'tool_name' => 'required|string',
            'params' => 'nullable|array',
        ]);

        try {
            $result = $this->toolExecutor->execute(
                $request->agent_key,
                $request->tool_name,
                $request->params ?? []
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Jana jawapan ejen melalui AI Gateway (integrasi penuh)
     */
    public function invokeAgent(Request $request): JsonResponse
    {
        $request->validate([
            'agent_key' => 'required|string',
            'objective' => 'required|string',
            'context' => 'nullable|array',
        ]);

        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        $result = $this->orchestrator->invokeAgent(
            $tenantId,
            $request->agent_key,
            $request->objective,
            $request->input('context', [])
        );

        $status = ($result['success'] ?? false) ? 200 : 422;

        return response()->json($result, $status);
    }

    /**
     * Save conversation
     */
    public function saveConversation(Request $request): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();
        $userId = auth()->id();

        $request->validate([
            'agent_key' => 'required|string',
            'messages' => 'required|array',
        ]);

        $result = $this->memoryManager->saveConversation(
            $tenantId,
            $request->agent_key,
            $userId,
            $request->messages
        );

        return response()->json([
            'success' => $result,
        ]);
    }

    /**
     * Get conversation history
     */
    public function getConversationHistory(string $agentKey, Request $request): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();
        $limit = $request->get('limit', 50);

        $history = $this->memoryManager->getConversationHistory($tenantId, $agentKey, $limit);

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Save business profile
     */
    public function saveBusinessProfile(Request $request): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        $request->validate([
            'business_name' => 'required|string',
            'industry_code' => 'nullable|string',
            'business_model' => 'nullable|string',
        ]);

        $result = $this->memoryManager->saveBusinessProfile($tenantId, $request->all());

        return response()->json([
            'success' => $result,
            'message' => 'Business profile saved.',
        ]);
    }

    /**
     * Get business profile
     */
    public function getBusinessProfile(): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();
        $profile = $this->memoryManager->getBusinessProfile($tenantId);

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'No business profile found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
    }

    /**
     * Get governance violations report
     */
    public function getGovernanceReport(Request $request): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();
        $limit = $request->get('limit', 50);

        $violations = $this->governance->getViolationsReport($tenantId, $limit);

        return response()->json([
            'success' => true,
            'data' => $violations,
        ]);
    }

    /** Senarai alat ejen sebenar (nama, input, kebenaran, risiko). */
    public function tools(): JsonResponse
    {
        return response()->json(['data' => app(\App\Modules\AIAgents\Tools\AgentToolRegistry::class)->definitions()]);
    }

    /** Jalankan satu alat sebenar. Risiko tinggi → Peti Kelulusan (status menunggu_kelulusan). */
    public function runTool(Request $request): JsonResponse
    {
        $d = $request->validate(['tool' => 'required|string|max:80', 'agent' => 'nullable|string|max:60', 'params' => 'nullable|array']);
        $r = app(\App\Modules\AIAgents\Tools\AgentToolRunner::class)->jalan($d['agent'] ?? 'pengguna', $d['tool'], $d['params'] ?? [], $request->user()->id);
        $kod = match ($r['status']) { 'berjaya' => 200, 'menunggu_kelulusan' => 202, 'ditolak' => 403, 'tiada_alat' => 404, 'input_tidak_sah' => 422, default => 409 };

        return response()->json($r, $kod);
    }

    /** "Tanya AI Bijan": soalan BM → alat sebenar → jawapan ringkas. */
    public function tanya(Request $request): JsonResponse
    {
        $d = $request->validate(['soalan' => 'required|string|min:2|max:300']);

        return response()->json(app(\App\Modules\AIAgents\Tools\TanyaAiBijan::class)->jawab($d['soalan'], $request->user()->id));
    }

    /** Pautan wa.me untuk teks yang dijana ejen (laporan, peringatan). Tiada mesej dihantar oleh sistem. */
    public function pautanWhatsApp(Request $request): JsonResponse
    {
        $d = $request->validate(['teks' => 'required|string|max:4000', 'telefon' => 'nullable|string|max:30']);
        $n = \App\Support\WhatsApp\PautanWhatsApp::nombor($d['telefon'] ?? null);
        if (! empty($d['telefon']) && ! $n) {
            return response()->json(['message' => 'Nombor telefon tidak sah.'], 422);
        }

        return response()->json(['url' => \App\Support\WhatsApp\PautanWhatsApp::url($n, $d['teks']), 'nombor' => $n]);
    }
}
