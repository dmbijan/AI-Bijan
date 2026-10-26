<?php

namespace App\Modules\AIAgents\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use BelongsToTenant;

    protected $table = 'ai_agents';

    protected $fillable = [
        'tenant_id',
        'agent_key',
        'name',
        'description',
        'role',
        'system_prompt',
        'tools_json',
        'budget_limits_json',
        'approval_required_json',
        'data_access_json',
        'memory_enabled',
        'proactive_alerts',
        'status',
        'current_model',
        'total_runs',
        'total_cost_usd',
        'total_tokens',
    ];

    protected $casts = [
        'tools_json' => 'array',
        'budget_limits_json' => 'array',
        'approval_required_json' => 'array',
        'data_access_json' => 'array',
        'memory_enabled' => 'boolean',
        'proactive_alerts' => 'boolean',
    ];

    /**
     * Get all available agent configurations from config
     */
    public static function getAvailableAgents()
    {
        return config('ai_agents.agents', []);
    }

    /**
     * Get agent by key
     */
    public static function getByKey($key)
    {
        $agents = self::getAvailableAgents();
        return $agents[$key] ?? null;
    }

    /**
     * Scope by tenant
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Check if tool is allowed for this agent
     */
    public function hasTool($toolName)
    {
        $tools = $this->tools_json ?? [];
        return in_array($toolName, $tools);
    }

    /**
     * Check if action requires approval
     */
    public function requiresApproval($action)
    {
        $required = $this->approval_required_json ?? [];
        return in_array($action, $required);
    }

    /**
     * Get model routing for task type
     */
    public static function getModelForTask($taskType)
    {
        $routing = config('ai_agents.model_routing', []);
        $models = $routing[$taskType] ?? $routing['balanced'] ?? ['asai/minimax-m3'];
        return $models[0]; // Return primary model
    }

    /**
     * Get approval level required for action
     */
    public static function getApprovalLevel($action)
    {
        $matrix = config('ai_agents.approval_matrix', []);
        return $matrix[$action] ?? null;
    }
}
