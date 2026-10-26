<?php

namespace App\Modules\Omnichannel\Services;

class AnalyticsService
{
    /**
     * Get omnichannel dashboard statistics
     */
    public function getDashboardStats($tenantId)
    {
        $conversations = \App\Modules\Omnichannel\Models\Conversation::where('tenant_id', $tenantId);
        $messages = \App\Modules\Omnichannel\Models\Message::whereHas('conversation', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        });

        return [
            'total_conversations' => (clone $conversations)->count(),
            'open_conversations' => (clone $conversations)->open()->count(),
            'pending_conversations' => (clone $conversations)->pending()->count(),
            'archived_today' => (clone $conversations)->whereDate('resolved_at', today())->count(),
            'total_messages_today' => (clone $messages)->whereDate('created_at', today())->count(),
            'avg_response_time_minutes' => $this->getAvgResponseTime($tenantId),
            'satisfaction_score' => $this->getSatisfactionScore($tenantId),
        ];
    }

    /**
     * Get channel-wise statistics
     */
    public function getChannelStats($tenantId)
    {
        $channels = \App\Modules\Omnichannel\Models\Channel::where('tenant_id', $tenantId)->get();

        $stats = [];
        foreach ($channels as $channel) {
            $conversations = \App\Modules\Omnichannel\Models\Conversation::where('tenant_id', $tenantId)
                ->where('channel_id', $channel->id);

            $stats[] = [
                'channel_id' => $channel->id,
                'channel_name' => $channel->name,
                'channel_type' => $channel->type,
                'status' => $channel->status,
                'total_conversations' => (clone $conversations)->count(),
                'open_conversations' => (clone $conversations)->open()->count(),
                'total_messages' => \App\Modules\Omnichannel\Models\Message::where('tenant_id', $tenantId)
                    ->where('channel_id', $channel->id)
                    ->count(),
            ];
        }

        return $stats;
    }

    /**
     * Get agent performance metrics
     */
    public function getAgentPerformance($tenantId)
    {
        $agents = \App\Models\User::where('tenant_id', $tenantId)
            ->whereIn('role', ['tenant_admin', 'staff'])
            ->withCount([
                'conversations as total_conversations' => function($q) {
                    $q->where('tenant_id', $tenantId);
                },
                'conversations as resolved_conversations' => function($q) use ($tenantId) {
                    $q->where('tenant_id', $tenantId)
                      ->where('status', 'archived');
                },
            ])
            ->get();

        $performance = [];
        foreach ($agents as $agent) {
            $performance[] = [
                'agent_id' => $agent->id,
                'agent_name' => $agent->name,
                'total_conversations' => $agent->total_conversations,
                'resolved_conversations' => $agent->resolved_conversations,
                'avg_response_time' => $this->getAgentAvgResponseTime($agent->id, $tenantId),
                'satisfaction_score' => $this->getAgentSatisfaction($agent->id, $tenantId),
            ];
        }

        return collect($performance)->sortByDesc('total_conversations')->values();
    }

    /**
     * Get conversation trends (daily/weekly/monthly)
     */
    public function getConversationTrends($tenantId, $period = 'daily', $days = 30)
    {
        $startDate = now()->subDays($days);
        
        $query = \App\Modules\Omnichannel\Models\Conversation::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDate, now()])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, status')
            ->groupBy('date', 'status')
            ->orderBy('date');

        $trends = $query->get();

        // Group by date
        $grouped = $trends->groupBy('date')->map(function($day) {
            return [
                'date' => $day->first()['date'],
                'open' => $day->where('status', 'open')->sum('count'),
                'pending' => $day->where('status', 'pending')->sum('count'),
                'archived' => $day->where('status', 'archived')->sum('count'),
            ];
        });

        return $grouped->values();
    }

    /**
     * Get message volume by type
     */
    public function getMessageVolumeByType($tenantId)
    {
        return \App\Modules\Omnichannel\Models\Message::whereHas('conversation', function($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->selectRaw('message_type, COUNT(*) as count')
        ->groupBy('message_type')
        ->get();
    }

    /**
     * Get top conversations (most messages)
     */
    public function getTopConversations($tenantId, $limit = 10)
    {
        return \App\Modules\Omnichannel\Models\Conversation::where('tenant_id', $tenantId)
            ->with(['channel', 'contact', 'agent'])
            ->orderBy('message_count', 'desc')
            ->limit($limit)
            ->get();
    }

    // Helper methods
    protected function getAvgResponseTime($tenantId)
    {
        $conversations = \App\Modules\Omnichannel\Models\Conversation::where('tenant_id', $tenantId)
            ->whereNotNull('first_response_at')
            ->whereNotNull('created_at')
            ->get();

        if ($conversations->isEmpty()) {
            return 0;
        }

        $totalMinutes = 0;
        foreach ($conversations as $conv) {
            $totalMinutes += $conv->created_at->diffInMinutes($conv->first_response_at);
        }

        return round($totalMinutes / $conversations->count(), 2);
    }

    protected function getAgentAvgResponseTime($agentId, $tenantId)
    {
        $conversations = \App\Modules\Omnichannel\Models\Conversation::where('tenant_id', $tenantId)
            ->where('agent_id', $agentId)
            ->whereNotNull('first_response_at')
            ->whereNotNull('created_at')
            ->get();

        if ($conversations->isEmpty()) {
            return 0;
        }

        $totalMinutes = 0;
        foreach ($conversations as $conv) {
            $totalMinutes += $conv->created_at->diffInMinutes($conv->first_response_at);
        }

        return round($totalMinutes / $conversations->count(), 2);
    }

    protected function getSatisfactionScore($tenantId)
    {
        // TODO: Implement satisfaction scoring based on ratings/reviews
        return 4.5; // Placeholder
    }

    protected function getAgentSatisfaction($agentId, $tenantId)
    {
        // TODO: Implement agent-specific satisfaction scoring
        return 4.5; // Placeholder
    }
}
