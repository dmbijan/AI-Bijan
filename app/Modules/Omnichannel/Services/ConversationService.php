<?php

namespace App\Modules\Omnichannel\Services;

use Illuminate\Support\Facades\DB;

class ConversationService
{
    /**
     * Create conversation from inbound message
     */
    public function createFromMessage(array $messageData)
    {
        return DB::transaction(function () use ($messageData) {
            // Find or create contact
            $contact = \App\Modules\Omnichannel\Models\Contact::upsertFromMessage($messageData);
            
            // Check if there's an existing open conversation for this contact+channel
            $conversation = \App\Modules\Omnichannel\Models\Conversation::where('tenant_id', $messageData['tenant_id'])
                ->where('channel_id', $messageData['channel_id'])
                ->where(function($q) use ($contact) {
                    $q->where('source_contact_identifier', $contact->identifier)
                      ->orWhere('source_contact_phone', $contact->phone_number)
                      ->orWhere('source_contact_email', $contact->email);
                })
                ->open()
                ->first();

            if (!$conversation) {
                // Create new conversation
                $conversation = \App\Modules\Omnichannel\Models\Conversation::create([
                    'tenant_id' => $messageData['tenant_id'],
                    'channel_id' => $messageData['channel_id'],
                    'contact_id' => $contact->id,
                    'subject' => $messageData['subject'] ?? 'New inquiry',
                    'status' => 'open',
                    'priority' => $messageData['priority'] ?? 'medium',
                    'source_contact_identifier' => $contact->identifier,
                    'source_contact_name' => $contact->name,
                    'source_contact_email' => $contact->email,
                    'source_contact_phone' => $contact->phone_number,
                    'message_count' => 0,
                    'tags' => [],
                    'custom_fields' => [],
                    'metadata' => ['source' => 'inbound_message'],
                ]);

                // Assign agent based on strategy
                $this->assignAgent($conversation);
            }

            return $conversation;
        });
    }

    /**
     * List conversations
     */
    public function getAll($tenantId, $filters = [])
    {
        $query = \App\Modules\Omnichannel\Models\Conversation::where('tenant_id', $tenantId)
            ->with(['channel', 'contact', 'agent', 'lastMessage']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['channel_id'])) {
            $query->byChannel($filters['channel_id']);
        }

        if (!empty($filters['agent_id'])) {
            $query->byAgent($filters['agent_id']);
        }

        if (!empty($filters['priority'])) {
            $query->byPriority($filters['priority']);
        }

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('subject', 'like', "%{$filters['search']}%")
                  ->orWhere('source_contact_name', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('last_message_at', 'desc')->paginate(20);
    }

    /**
     * Get conversation by ID
     */
    public function getById($conversationId)
    {
        return \App\Modules\Omnichannel\Models\Conversation::with([
            'channel',
            'contact',
            'agent',
            'messages' => function($q) {
                $q->orderBy('created_at');
            },
        ])->findOrFail($conversationId);
    }

    /**
     * Assign agent to conversation
     */
    protected function assignAgent($conversation)
    {
        $strategy = config('omnichannel.conversations.agent_assignment_strategy', 'round-robin');
        
        switch ($strategy) {
            case 'round-robin':
                $agentId = $this->getRoundRobinAgent($conversation->tenant_id);
                break;
            case 'least-busy':
                $agentId = $this->getLeastBusyAgent($conversation->tenant_id);
                break;
            default:
                $agentId = null;
        }

        if ($agentId) {
            $conversation->assignAgent($agentId);
        }

        return $conversation;
    }

    /**
     * Get round-robin agent
     */
    protected function getRoundRobinAgent($tenantId)
    {
        // TODO: Implement round-robin logic with session/cache
        return \App\Models\User::where('tenant_id', $tenantId)
            ->whereIn('role', ['tenant_admin', 'staff'])
            ->inRandomOrder()
            ->value('id');
    }

    /**
     * Get least busy agent
     */
    protected function getLeastBusyAgent($tenantId)
    {
        return \App\Models\User::where('tenant_id', $tenantId)
            ->whereHas('conversations', function($q) {
                $q->open();
            })
            ->withCount(['conversations as open_conversation_count' => function($q) {
                $q->open();
            }])
            ->orderBy('open_conversation_count')
            ->first();
    }

    /**
     * Update conversation status
     */
    public function updateStatus($conversationId, $status)
    {
        $conversation = $this->getById($conversationId);
        
        switch ($status) {
            case 'open':
                $conversation->open();
                break;
            case 'archived':
                $conversation->close();
                break;
            case 'pending':
                $conversation->pending();
                break;
        }

        return $conversation;
    }

    /**
     * Get statistics
     */
    public function getStatistics($tenantId)
    {
        $stats = [
            'total_open' => \App\Modules\Omnichannel\Models\Conversation::where('tenant_id', $tenantId)->open()->count(),
            'total_pending' => \App\Modules\Omnichannel\Models\Conversation::where('tenant_id', $tenantId)->pending()->count(),
            'total_archived_today' => \App\Modules\Omnichannel\Models\Conversation::where('tenant_id', $tenantId)
                ->whereDate('resolved_at', today())
                ->count(),
            'avg_response_time' => \App\Modules\Omnichannel\Models\Conversation::where('tenant_id', $tenantId)
                ->whereNotNull('first_response_at')
                ->avg(function($q) {
                    // This would need a raw SQL expression
                }) ?? 0,
        ];

        return $stats;
    }
}
