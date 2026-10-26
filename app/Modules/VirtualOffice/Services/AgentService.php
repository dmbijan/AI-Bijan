<?php

namespace App\Modules\VirtualOffice\Services;

use App\Modules\VirtualOffice\Models\Agent;
use Illuminate\Support\Facades\DB;

class AgentService
{
    /**
     * Create a new agent (human or AI).
     */
    public function create(array $data, int $tenantId): Agent
    {
        return DB::transaction(function () use ($data, $tenantId) {
            return Agent::create([
                'tenant_id' => $tenantId,
                'user_id' => $data['user_id'] ?? null,
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'type' => $data['type'] ?? 'human',
                'avatar_url' => $data['avatar_url'] ?? null,
                'role' => $data['role'] ?? null,
                'status' => $data['status'] ?? 'idle',
                'metadata' => $data['metadata'] ?? [],
            ]);
        });
    }

    /**
     * Update an existing agent.
     */
    public function update(Agent $agent, array $data): Agent
    {
        $agent->update($data);
        return $agent->fresh();
    }

    /**
     * Set agent status.
     */
    public function setStatus(Agent $agent, string $status): Agent
    {
        if (in_array($status, ['idle', 'active', 'busy', 'offline'])) {
            $agent->update(['status' => $status]);
        }
        return $agent->fresh();
    }

    /**
     * Get all agents for a tenant.
     */
    public function getTenantAgents(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Agent::where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get available agents only.
     */
    public function getAvailableAgents(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Agent::where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'idle'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get AI agents only.
     */
    public function getAIAgents(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Agent::where('tenant_id', $tenantId)
            ->where('type', 'ai')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get human agents only.
     */
    public function getHumanAgents(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Agent::where('tenant_id', $tenantId)
            ->where('type', 'human')
            ->orderBy('name')
            ->get();
    }

    /**
     * Delete an agent.
     */
    public function deleteAgent(Agent $agent): bool
    {
        return $agent->delete();
    }

    /**
     * Get agent statistics.
     */
    public function getAgentStatistics(int $tenantId): array
    {
        $agents = Agent::where('tenant_id', $tenantId);

        return [
            'total_agents' => (clone $agents)->count(),
            'human_agents' => (clone $agents)->where('type', 'human')->count(),
            'ai_agents' => (clone $agents)->where('type', 'ai')->count(),
            'active_agents' => (clone $agents)->whereIn('status', ['active', 'busy'])->count(),
            'available_agents' => (clone $agents)->whereIn('status', ['active', 'idle'])->count(),
            'offline_agents' => (clone $agents)->where('status', 'offline')->count(),
        ];
    }

    /**
     * Bulk update agent statuses.
     */
    public function bulkUpdateStatus(int $tenantId, array $agentIds, string $status): void
    {
        Agent::whereIn('id', $agentIds)
            ->where('tenant_id', $tenantId)
            ->update(['status' => $status]);
    }

    /**
     * Touch last active timestamp for all agents.
     */
    public function touchAllActive(int $tenantId): void
    {
        Agent::where('tenant_id', $tenantId)
            ->update(['last_active_at' => now()]);
    }
}
