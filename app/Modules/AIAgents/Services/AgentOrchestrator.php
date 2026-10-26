<?php

namespace App\Modules\AIAgents\Services;

use App\Modules\AIAgents\Models\Agent;
use Illuminate\Support\Facades\DB;

class AgentOrchestrator
{
    /**
     * Orchestrate multi-agent workflow
     */
    public function orchestrateWorkflow($tenantId, $task, $agents = [])
    {
        // Determine which agents are needed for this task
        if (empty($agents)) {
            $agents = $this->determineAgentsForTask($task);
        }

        // Create orchestration session
        $sessionId = $this->createSession($tenantId, $task, $agents);

        // Execute agents in sequence or parallel based on dependencies
        $results = [];
        foreach ($agents as $agentKey) {
            $result = $this->executeAgent($tenantId, $agentKey, $task, $sessionId);
            $results[$agentKey] = $result;

            // Check if result needs human approval
            if ($result['requires_approval']) {
                $this->requestApproval($tenantId, $result);
            }
        }

        // Aggregate results
        return $this->aggregateResults($sessionId, $results);
    }

    /**
     * Determine which agents are needed for a task
     */
    protected function determineAgentsForTask($task)
    {
        $taskType = $task['type'] ?? 'general';
        $taskCategory = $task['category'] ?? null;

        $agentMap = [
            'financial' => ['cfo', 'ceo'],
            'marketing' => ['cmo', 'ads_specialist'],
            'sales' => ['ciso', 'cmo'],
            'operations' => ['coo', 'cfo'],
            'hr' => ['chro', 'ceo'],
            'support' => ['support', 'ciso'],
            'analytics' => ['analyst', 'researcher'],
            'executive' => ['ceo', 'exec_assistant'],
        ];

        $agents = $agentMap[$taskCategory] ?? ['ceo'];

        return $agents;
    }

    /**
     * Execute a single agent
     */
    protected function executeAgent($tenantId, $agentKey, $task, $sessionId)
    {
        $agent = Agent::where('tenant_id', $tenantId)
            ->where('agent_key', $agentKey)
            ->first();

        // Auto-konfigurasi daripada config jika tenant belum ada rekod ejen ini
        if (!$agent) {
            $baseConfig = config("ai_agents.agents.{$agentKey}");

            if (!$baseConfig) {
                return [
                    'success' => false,
                    'error' => "Agent '{$agentKey}' tidak wujud dalam konfigurasi.",
                ];
            }

            $agent = app(\App\Modules\AIAgents\Services\AgentService::class)
                ->configureAgent($tenantId, $agentKey);
        }

        if ($agent->status !== 'active') {
            return [
                'success' => false,
                'error' => "Agent '{$agentKey}' tidak aktif.",
            ];
        }

        // Check budget limits
        $this->checkBudgetLimits($agent);

        // Check if approval is required
        $requiresApproval = false;
        foreach ($task['actions'] ?? [] as $action) {
            if ($agent->requiresApproval($action)) {
                $requiresApproval = true;
                break;
            }
        }

        // Create execution record
        $executionId = DB::table('ai_agent_executions')->insertGetId([
            'tenant_id' => $tenantId,
            'agent_key' => $agentKey,
            'session_id' => $sessionId,
            'task_type' => $task['type'] ?? 'general',
            'objective' => $task['objective'] ?? '',
            'input_data' => json_encode($task['input'] ?? []),
            'allowed_tools' => json_encode($agent->tools_json ?? []),
            'risk_level' => $this->calculateRiskLevel($task),
            'approval_required' => $requiresApproval,
            'status' => $requiresApproval ? 'awaiting_approval' : 'queued',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'success' => true,
            'execution_id' => $executionId,
            'agent_key' => $agentKey,
            'requires_approval' => $requiresApproval,
            'status' => $requiresApproval ? 'awaiting_approval' : 'running',
        ];
    }

    /**
     * Jana jawapan ejen melalui AI Gateway (BAB 16 + 21)
     *
     * Ini adalah titik integrasi utama: ejen → Gateway → model.
     */
    public function invokeAgent(
        int $tenantId,
        string $agentKey,
        string $objective,
        array $context = [],
        array $options = []
    ): array {
        $agent = Agent::where('tenant_id', $tenantId)
            ->where('agent_key', $agentKey)
            ->first();

        // Auto-konfigurasi jika belum ada
        if (!$agent) {
            $agent = app(AgentService::class)->configureAgent($tenantId, $agentKey);
        }

        if ($agent->status !== 'active') {
            return [
                'success' => false,
                'agent_key' => $agentKey,
                'error' => "Ejen '{$agentKey}' tidak aktif.",
            ];
        }

        // Governance: ejen mesti aktif dan milik tenant ini (bukan semakan tool)
        $governance = app(AgentGovernance::class);

        $auditBase = [
            'objective' => $objective,
            'agent_status' => $agent->status,
        ];

        if ($agent->status !== 'active' || (int) $agent->tenant_id !== (int) $tenantId) {
            $governance->logAudit($tenantId, $agentKey, 'ai_invoke', 'violation', $auditBase);

            return [
                'success' => false,
                'agent_key' => $agentKey,
                'error' => 'Governance validation gagal.',
                'violations' => ['Ejen tidak aktif atau bukan milik tenant ini.'],
            ];
        }

        // Bina messages dari system prompt + konteks
        $messages = [
            ['role' => 'system', 'content' => $agent->system_prompt],
        ];

        if (!empty($context)) {
            $messages[] = [
                'role' => 'system',
                'content' => "Konteks perniagaan:\n" . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            ];
        }

        $messages[] = ['role' => 'user', 'content' => $objective];

        // Panggil AI Gateway
        $taskType = $this->mapAgentToTaskType($agentKey);

        try {
            $response = app(\App\Modules\AIGateway\Services\GatewayService::class)->call(
                $tenantId,
                $messages,
                $taskType,
                [
                    'user_id' => auth()->id(),
                    'agent_key' => $agentKey,
                    'source_module' => 'ai_agents',
                    'model' => $agent->current_model,
                ]
            );
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'agent_key' => $agentKey,
                'error' => $e->getMessage(),
            ];
        }

        // Log audit
        $governance->logAudit(
            $tenantId,
            $agentKey,
            'ai_invoke',
            ($response['success'] ?? false) ? 'allowed' : 'blocked',
            [
                'objective' => $objective,
                'model' => $response['model'] ?? null,
                'usage' => $response['usage'] ?? null,
            ]
        );

        return array_merge($response, ['agent_key' => $agentKey]);
    }

    /**
     * Peta ejen kepada jenis tugas untuk routing model
     */
    protected function mapAgentToTaskType(string $agentKey): string
    {
        return match ($agentKey) {
            'ceo', 'cfo', 'coo', 'ciso' => 'reasoning',
            'analyst', 'researcher' => 'reasoning',
            'cmo', 'ads_specialist' => 'creative',
            'chro', 'support', 'exec_assistant' => 'quick',
            default => 'default',
        };
    }

    /**
     * Check budget limits for agent
     */
    protected function checkBudgetLimits($agent)
    {
        $limits = $agent->budget_limits_json ?? [];

        // Check cost limit
        if (isset($limits['max_cost_per_run'])) {
            $todayCost = DB::table('ai_agent_executions')
                ->where('tenant_id', $agent->tenant_id)
                ->where('agent_key', $agent->agent_key)
                ->whereDate('created_at', today())
                ->sum('cost_usd');

            if ($todayCost + 0.1 > $limits['max_cost_per_run']) {
                throw new \RuntimeException("Agent '{$agent->agent_key}' has exceeded daily budget.");
            }
        }

        // Check token limit
        if (isset($limits['max_tokens_per_run'])) {
            // Token tracking would be implemented here
        }

        // Check action limit
        if (isset($limits['max_actions_per_run'])) {
            // Action tracking would be implemented here
        }
    }

    /**
     * Calculate risk level for task
     */
    protected function calculateRiskLevel($task)
    {
        $highRiskActions = ['payment.approve', 'contract_signing', 'hire_decision', 'termination'];

        foreach ($task['actions'] ?? [] as $action) {
            if (in_array($action, $highRiskActions)) {
                return 'high';
            }
        }

        return isset($task['risk_level']) ? $task['risk_level'] : 'medium';
    }

    /**
     * Request human approval for action
     */
    protected function requestApproval($tenantId, $executionResult)
    {
        // Create approval request in B-Virtual Office
        DB::table('approval_requests')->insert([
            'tenant_id' => $tenantId,
            'type' => 'ai_agent_action',
            'payload' => json_encode([
                'agent_key' => $executionResult['agent_key'],
                'execution_id' => $executionResult['execution_id'],
                'description' => 'AI Agent memerlukan kelulusan untuk tindakan ini.',
                'risk_level' => $executionResult['risk_level'] ?? null,
            ]),
            'requested_by' => auth()->id(),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Create orchestration session
     */
    protected function createSession($tenantId, $task, $agents)
    {
        $sessionId = 'sess_' . uniqid();

        DB::table('ai_agent_sessions')->insert([
            'tenant_id' => $tenantId,
            'session_id' => $sessionId,
            'task_type' => $task['type'] ?? 'general',
            'objective' => $task['objective'] ?? '',
            'agents_involved' => json_encode($agents),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $sessionId;
    }

    /**
     * Aggregate results from multiple agents
     */
    protected function aggregateResults($sessionId, $results)
    {
        // Combine all agent outputs into a unified response
        return [
            'session_id' => $sessionId,
            'results' => $results,
            'summary' => $this->generateSummary($results),
            'next_steps' => $this->determineNextSteps($results),
        ];
    }

    /**
     * Generate summary of agent results
     */
    protected function generateSummary($results)
    {
        $summary = [];
        foreach ($results as $agentKey => $result) {
            $summary[] = [
                'agent' => $agentKey,
                'status' => $result['status'] ?? 'unknown',
                'output' => $result['output'] ?? null,
            ];
        }
        return $summary;
    }

    /**
     * Determine next steps based on results
     */
    protected function determineNextSteps($results)
    {
        $nextSteps = [];

        foreach ($results as $agentKey => $result) {
            if ($result['requires_approval']) {
                $nextSteps[] = "Wait for human approval for {$agentKey}";
            } elseif ($result['success']) {
                $nextSteps[] = "{$agentKey} completed successfully";
            } else {
                $nextSteps[] = "Review error for {$agentKey}: " . ($result['error'] ?? 'Unknown error');
            }
        }

        return $nextSteps;
    }
}
