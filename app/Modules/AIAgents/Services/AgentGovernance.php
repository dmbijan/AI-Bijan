<?php

namespace App\Modules\AIAgents\Services;

use Illuminate\Support\Facades\DB;

class AgentGovernance
{
    /**
     * Validate agent action against governance rules
     */
    public function validateAction($tenantId, $agentKey, $action, $params = [])
    {
        $violations = [];

        // Rule 1: Least privilege check
        if (!$this->checkLeastPrivilege($agentKey, $action)) {
            $violations[] = "Agent '{$agentKey}' does not have permission for action '{$action}'.";
        }

        // Rule 2: Tool allowlist validation
        if (!$this->validateToolAllowlist($agentKey, $action)) {
            $violations[] = "Action '{$action}' is not in the agent's tool allowlist.";
        }

        // Rule 3: Parameter validation
        if (!$this->validateParameters($action, $params)) {
            $violations[] = "Invalid parameters detected for action '{$action}'.";
        }

        // Rule 4: Budget limit check
        if (!$this->checkBudgetLimits($tenantId, $agentKey)) {
            $violations[] = "Agent '{$agentKey}' has exceeded budget limits.";
        }

        // Rule 5: Sensitive action requires human approval
        if ($this->isSensitiveAction($action) && !$this->hasHumanApproval($tenantId, $action)) {
            $violations[] = "Sensitive action '{$action}' requires human approval.";
        }

        // Rule 6: Data isolation check
        if (!$this->checkDataIsolation($tenantId, $agentKey)) {
            $violations[] = "Data isolation violation detected for agent '{$agentKey}'.";
        }

        return [
            'valid' => empty($violations),
            'violations' => $violations,
        ];
    }

    /**
     * Check least privilege - agent only has minimum required permissions
     */
    protected function checkLeastPrivilege($agentKey, $action)
    {
        $agents = config('ai_agents.agents', []);
        if (!isset($agents[$agentKey])) {
            return false;
        }

        $allowedTools = $agents[$agentKey]['tools'] ?? [];
        
        // Extract base action from tool name (e.g., 'finance.read' -> 'finance')
        $baseAction = explode('.', $action)[0] ?? $action;
        
        // Check if action is within allowed scope
        foreach ($allowedTools as $tool) {
            if (strpos($tool, $baseAction) === 0 || $tool === $action) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate tool allowlist
     */
    protected function validateToolAllowlist($agentKey, $action)
    {
        $agents = config('ai_agents.agents', []);
        if (!isset($agents[$agentKey])) {
            return false;
        }

        $allowedTools = $agents[$agentKey]['tools'] ?? [];
        return in_array($action, $allowedTools);
    }

    /**
     * Validate action parameters
     */
    protected function validateParameters($action, $params)
    {
        // Check for sensitive data exposure
        $sensitiveFields = ['password', 'ssn', 'credit_card', 'api_key'];
        foreach ($params as $key => $value) {
            foreach ($sensitiveFields as $field) {
                if (strpos($key, $field) !== false && !empty($value)) {
                    return false; // Potential sensitive data leak
                }
            }
        }

        return true;
    }

    /**
     * Check budget limits per agent per day
     */
    protected function checkBudgetLimits($tenantId, $agentKey)
    {
        $agents = config('ai_agents.agents', []);
        if (!isset($agents[$agentKey])) {
            return true; // No config, skip check
        }

        $limits = $agents[$agentKey]['budget_limits']['max_cost_per_run'] ?? null;
        if (!$limits) {
            return true; // No limit set
        }

        // Get today's total cost for this agent
        $todayCost = DB::table('ai_agent_executions')
            ->where('tenant_id', $tenantId)
            ->where('agent_key', $agentKey)
            ->whereDate('created_at', today())
            ->sum('cost_usd');

        return $todayCost <= $limits;
    }

    /**
     * Check if action is sensitive and requires approval
     */
    protected function isSensitiveAction($action)
    {
        $sensitiveActions = [
            'payment.approve',
            'contract_signing',
            'hire_decision',
            'termination',
            'salary_change',
            'campaign.publish',
            'journal.post',
            'purchase_order',
            'refund_over_50',
        ];

        return in_array($action, $sensitiveActions);
    }

    /**
     * Check if human approval exists for action
     */
    protected function hasHumanApproval($tenantId, $action)
    {
        $approval = DB::table('approval_requests')
            ->where('tenant_id', $tenantId)
            ->where('type', 'ai_agent_action')
            ->where('status', 'approved')
            ->where('payload', 'like', '%' . $action . '%')
            ->first();

        return $approval !== null;
    }

    /**
     * Check data isolation between tenants
     */
    protected function checkDataIsolation($tenantId, $agentKey)
    {
        // Verify agent belongs to this tenant
        $agent = DB::table('ai_agents')
            ->where('tenant_id', $tenantId)
            ->where('agent_key', $agentKey)
            ->first();

        return $agent !== null;
    }

    /**
     * Log governance audit trail
     */
    public function logAudit($tenantId, $agentKey, $action, $result, $details = [])
    {
        DB::table('ai_governance_audits')->insert([
            'tenant_id' => $tenantId,
            'agent_key' => $agentKey,
            'action' => $action,
            'result' => $result,
            'details_json' => json_encode($details),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    /**
     * Get governance violations report
     */
    public function getViolationsReport($tenantId, $limit = 50)
    {
        return DB::table('ai_governance_audits')
            ->where('tenant_id', $tenantId)
            ->where('result', 'violation')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
