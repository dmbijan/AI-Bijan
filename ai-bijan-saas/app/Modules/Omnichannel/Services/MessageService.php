<?php

namespace App\Modules\Omnichannel\Services;

use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MessageService
{
    /**
     * Send outbound message
     */
    public function send(array $data)
    {
        return DB::transaction(function () use ($data) {
            $message = \App\Modules\Omnichannel\Models\Message::create([
                'tenant_id' => $data['tenant_id'],
                'conversation_id' => $data['conversation_id'],
                'channel_id' => $data['channel_id'],
                'sender_type' => 'agent',
                'sender_id' => $data['sender_id'] ?? auth()->id(),
                'direction' => 'outbound',
                'message_type' => $data['message_type'] ?? 'text',
                'content' => $data['content'] ?? ['text' => ''],
                'attachments' => $data['attachments'] ?? [],
                'metadata' => $data['metadata'] ?? [],
                'status' => 'sent',
            ]);

            // TODO: Implement actual sending per channel type
            // WhatsApp: Use Twilio/WABA API
            // Email: Use Laravel Mail
            // Social: Use respective APIs
            
            // Mark as delivered after a delay
            // For now, mark immediately for testing
            $message->markAsDelivered();

            // Update conversation
            $conversation = \App\Modules\Omnichannel\Models\Conversation::find($data['conversation_id']);
            if ($conversation) {
                $conversation->incrementMessageCount();
                $conversation->markFirstResponse();
            }

            return $message;
        });
    }

    /**
     * Handle inbound message from channel
     */
    public function handleInbound(array $messageData)
    {
        return DB::transaction(function () use ($messageData) {
            // Create or find conversation
            $conversation = app(ConversationService::class)->createFromMessage($messageData);

            // Create message record
            $message = \App\Modules\Omnichannel\Models\Message::create([
                'tenant_id' => $messageData['tenant_id'],
                'conversation_id' => $conversation->id,
                'channel_id' => $messageData['channel_id'],
                'sender_type' => 'contact',
                'sender_id' => $messageData['sender_id'] ?? $conversation->contact_id,
                'direction' => 'inbound',
                'message_type' => $messageData['message_type'] ?? 'text',
                'content' => $messageData['content'] ?? ['text' => ''],
                'attachments' => $messageData['attachments'] ?? [],
                'metadata' => $messageData['metadata'] ?? [],
                'status' => 'sent',
                'external_message_id' => $messageData['external_message_id'] ?? null,
            ]);

            // Update contact
            $conversation->contact->incrementConversationCount();

            // Check for auto-reply / bot response
            if (config('omnichannel.automation.chatbot_enabled')) {
                $this->handleAutoReply($conversation, $message);
            }

            return $message;
        });
    }

    /**
     * Upload media attachment
     */
    public function uploadMedia($file, $conversationId)
    {
        $validated = $file->validate([
            'media' => 'required|file|max:25600|mimes:' . implode(',', config('omnichannel.messages.allowed_types', [])),
        ]);

        $path = $file->store('omnichannel/media/' . date('Y/m'), 'public');

        return [
            'media_path' => $path,
            'media_url' => Storage::disk('public')->url($path),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Get conversation messages
     */
    public function getMessages($conversationId, $filters = [])
    {
        $query = \App\Modules\Omnichannel\Models\Message::where('conversation_id', $conversationId)
            ->orderBy('created_at');

        if (!empty($filters['type'])) {
            $query->where('message_type', $filters['type']);
        }

        if (!empty($filters['direction'])) {
            $query->where('direction', $filters['direction']);
        }

        return $query->paginate(50);
    }

    /**
     * Handle auto-reply / chatbot
     */
    protected function handleAutoReply($conversation, $message)
    {
        $userInput = is_array($message->content) ? ($message->content['text'] ?? '') : '';
        
        // Check escalation keywords
        $escalationKeywords = config('omnichannel.automation.keywords_for_escalation', []);
        foreach ($escalationKeywords as $keyword) {
            if (stripos($userInput, $keyword) !== false) {
                // Escalate to human agent
                $conversation->open();
                return;
            }
        }

        // Send greeting if first message
        if ($conversation->message_count <= 1) {
            $greeting = config('omnichannel.automation.greeting_message', 'Hi! How can we help?');
            
            $this->send([
                'tenant_id' => $conversation->tenant_id,
                'conversation_id' => $conversation->id,
                'channel_id' => $conversation->channel_id,
                'sender_id' => null, // Bot
                'message_type' => 'text',
                'content' => ['text' => $greeting],
                'metadata' => ['source' => 'auto_reply'],
            ]);
        }
    }

    /**
     * Send template message (WhatsApp business template)
     */
    public function sendTemplate($conversationId, $templateName, $variables = [])
    {
        return $this->send([
            'tenant_id' => app(TenantContext::class)->id(),
            'conversation_id' => $conversationId,
            'channel_id' => null, // Will be set from conversation
            'sender_id' => auth()->id(),
            'message_type' => 'template',
            'content' => [
                'template' => $templateName,
                'variables' => $variables,
            ],
            'metadata' => ['template_name' => $templateName],
        ]);
    }
}
