<?php

namespace App\Modules\AIAgents\Services;

use App\Modules\AIAgents\Models\Agent;
use Illuminate\Support\Facades\DB;

class AgentService
{
    /**
     * Get all available agents
     */
    public function getAvailableAgents()
    {
        return Agent::getAvailableAgents();
    }

    /**
     * Get agent by key
     */
    public function getAgent($key)
    {
        return Agent::getByKey($key);
    }

    /**
     * List tenant's configured agents
     */
    public function listTenantAgents($tenantId)
    {
        return Agent::where('tenant_id', $tenantId)
            ->orderBy('agent_key')
            ->get();
    }

    /**
     * Configure agent for tenant
     */
    public function configureAgent($tenantId, $agentKey, $customConfig = [])
    {
        $availableAgents = Agent::getAvailableAgents();

        if (!isset($availableAgents[$agentKey])) {
            throw new \InvalidArgumentException("Agent '{$agentKey}' is not a valid agent.");
        }

        $baseConfig = $availableAgents[$agentKey];

        // Merge custom config
        $config = array_merge($baseConfig, $customConfig);

        DB::beginTransaction();
        try {
            $agent = Agent::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'agent_key' => $agentKey,
                ],
                [
                    'name' => $config['name'],
                    'description' => $config['description'],
                    'role' => $config['role'],
                    'system_prompt' => $config['system_prompt'],
                    'tools_json' => $config['tools'],
                    'budget_limits_json' => $config['budget_limits'],
                    'approval_required_json' => $config['approval_required_for'],
                    'data_access_json' => $config['data_access'],
                    'memory_enabled' => $config['memory_enabled'],
                    'proactive_alerts' => $config['proactive_alerts'],
                    'status' => 'active',
                ]
            );

            DB::commit();

            return $agent;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update agent configuration
     */
    public function updateAgent($tenantId, $agentKey, $updates)
    {
        $agent = Agent::where('tenant_id', $tenantId)
            ->where('agent_key', $agentKey)
            ->first();

        if (!$agent) {
            throw new \InvalidArgumentException("Agent '{$agentKey}' not found for this tenant.");
        }

        // Validate tools against allowed tools
        if (isset($updates['tools'])) {
            $allowedTools = $agent->tools_json ?? [];
            foreach ($updates['tools'] as $tool) {
                if (!in_array($tool, $allowedTools)) {
                    throw new \InvalidArgumentException("Tool '{$tool}' is not allowed for this agent.");
                }
            }
        }

        // Peta nama input ke nama kolum
        $mapped = [];
        $fieldMap = [
            'tools' => 'tools_json',
            'budget_limits' => 'budget_limits_json',
            'approval_required_for' => 'approval_required_json',
            'data_access' => 'data_access_json',
        ];

        $allowed = [
            'name', 'description', 'role', 'system_prompt',
            'tools_json', 'budget_limits_json', 'approval_required_json',
            'data_access_json', 'memory_enabled', 'proactive_alerts',
            'status', 'current_model',
        ];

        foreach ($updates as $key => $value) {
            $column = $fieldMap[$key] ?? $key;
            if (in_array($column, $allowed, true)) {
                $mapped[$column] = $value;
            }
        }

        $agent->update($mapped);

        return $agent->fresh();
    }

    /**
     * Disable agent
     */
    public function disableAgent($tenantId, $agentKey)
    {
        $agent = Agent::where('tenant_id', $tenantId)
            ->where('agent_key', $agentKey)
            ->first();

        if (!$agent) {
            throw new \InvalidArgumentException("Agent '{$agentKey}' not found for this tenant.");
        }

        $agent->update(['status' => 'disabled']);

        return $agent;
    }

    /**
     * Enable agent
     */
    public function enableAgent($tenantId, $agentKey)
    {
        $agent = Agent::where('tenant_id', $tenantId)
            ->where('agent_key', $agentKey)
            ->first();

        if (!$agent) {
            throw new \InvalidArgumentException("Agent '{$agentKey}' not found for this tenant.");
        }

        $agent->update(['status' => 'active']);

        return $agent;
    }

    /**
     * Get agent statistics
     */
    public function getAgentStats($tenantId, $agentKey)
    {
        $agent = Agent::where('tenant_id', $tenantId)
            ->where('agent_key', $agentKey)
            ->first();

        if (!$agent) {
            throw new \InvalidArgumentException("Agent '{$agentKey}' not found for this tenant.");
        }

        // Get execution stats from ai_agent_executions table
        $executions = DB::table('ai_agent_executions')
            ->where('tenant_id', $tenantId)
            ->where('agent_key', $agentKey)
            ->selectRaw('
                COUNT(*) as total_runs,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_runs,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_runs,
                AVG(CASE WHEN status = "completed" THEN cost_usd END) as avg_cost,
                SUM(total_tokens) as total_tokens_used
            ')
            ->first();

        return [
            'agent' => $agent,
            'stats' => [
                'total_runs' => $executions->total_runs ?? 0,
                'completed_runs' => $executions->completed_runs ?? 0,
                'failed_runs' => $executions->failed_runs ?? 0,
                'avg_cost' => round($executions->avg_cost ?? 0, 4),
                'total_tokens' => $executions->total_tokens_used ?? 0,
            ],
        ];
    }

    /**
     * Reset agent to default configuration
     */
    public function resetToDefault($tenantId, $agentKey)
    {
        $availableAgents = Agent::getAvailableAgents();

        if (!isset($availableAgents[$agentKey])) {
            throw new \InvalidArgumentException("Agent '{$agentKey}' is not a valid agent.");
        }

        $defaultConfig = $availableAgents[$agentKey];

        $agent = Agent::where('tenant_id', $tenantId)
            ->where('agent_key', $agentKey)
            ->first();

        if (!$agent) {
            throw new \InvalidArgumentException("Agent '{$agentKey}' not found for this tenant.");
        }

        $agent->update([
            'system_prompt' => $defaultConfig['system_prompt'],
            'tools_json' => json_encode($defaultConfig['tools']),
            'budget_limits_json' => json_encode($defaultConfig['budget_limits']),
            'approval_required_json' => json_encode($defaultConfig['approval_required_for']),
            'data_access_json' => json_encode($defaultConfig['data_access']),
            'memory_enabled' => $defaultConfig['memory_enabled'],
            'proactive_alerts' => $defaultConfig['proactive_alerts'],
            'status' => 'active',
        ]);

        return $agent;
    }

    /**
     * Get recommended agents based on industry
     */
    public function recommendByIndustry($industryKey)
    {
        $agents = $this->getAvailableAgents();
        $recommendations = [];

        // Map industries to recommended agents
        $industryAgentMap = [
            'retail' => ['ceo', 'cfo', 'coo', 'ciso', 'support', 'analyst'],
            'manufacturing' => ['ceo', 'cfo', 'coo', 'ciso', 'researcher', 'analyst'],
            'services' => ['ceo', 'exec_assistant', 'ciso', 'chro', 'analyst'],
            'healthcare' => ['ceo', 'cfo', 'coo', 'support', 'chro'],
            'food_beverage' => ['ceo', 'cfo', 'coo', 'support', 'cmo'],
            'construction' => ['ceo', 'cfo', 'coo', 'ciso', 'researcher'],
            'logistics' => ['ceo', 'cfo', 'coo', 'ciso', 'analyst'],
            'education' => ['ceo', 'exec_assistant', 'ciso', 'chro', 'cmo'],
            'real_estate' => ['ceo', 'ciso', 'cmo', 'cfo', 'support'],
            'agriculture' => ['ceo', 'cfo', 'coo', 'researcher', 'analyst'],
        ];

        $recommendedKeys = $industryAgentMap[$industryKey] ?? array_keys($agents);

        foreach ($recommendedKeys as $key) {
            if (isset($agents[$key])) {
                $recommendations[$key] = [
                    'name' => $agents[$key]['name'],
                    'description' => $agents[$key]['description'],
                    'priority' => 'high',
                ];
            }
        }

        return $recommendations;
    }
}
