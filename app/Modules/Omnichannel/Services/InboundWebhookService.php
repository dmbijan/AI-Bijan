<?php

namespace App\Modules\Omnichannel\Services;

use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InboundWebhookService
{
    /**
     * Handle incoming webhook from WhatsApp
     */
    public function handleWhatsApp(Request $request)
    {
        try {
            $payload = $request->all();
            
            // Verify webhook signature
            if (!$this->verifyWebhookSignature($request)) {
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            // Process each message in the webhook
            foreach ($payload['messages'] ?? [] as $messageData) {
                $this->processWhatsAppMessage($messageData);
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle incoming webhook from Facebook Messenger
     */
    public function handleFacebook(Request $request)
    {
        try {
            $payload = $request->all();
            
            // Verify webhook
            if ($request->has('hub_challenge')) {
                return response($request->hub_challenge);
            }

            foreach ($payload['entry'] ?? [] as $entry) {
                foreach ($entry['messaging'] ?? [] as $messageData) {
                    $this->processFacebookMessage($messageData);
                }
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle incoming webhook from Telegram
     */
    public function handleTelegram(Request $request)
    {
        try {
            $update = $request->all();
            
            if (isset($update['message'])) {
                $this->processTelegramMessage($update['message']);
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Process WhatsApp message
     */
    protected function processWhatsAppMessage($messageData)
    {
        $channel = \App\Modules\Omnichannel\Models\Channel::where('type', 'whatsapp')
            ->active()
            ->first();

        if (!$channel) {
            return;
        }

        $contactIdentifier = $messageData['from'] ?? '';
        
        $messagePayload = [
            'tenant_id' => app(TenantContext::class)->id() ?? $this->getTenantFromChannel($channel),
            'channel_id' => $channel->id,
            'sender_id' => $contactIdentifier,
            'sender_name' => $messageData['sender_name'] ?? null,
            'phone' => $contactIdentifier,
            'message_type' => $messageData['type'] ?? 'text',
            'content' => $this->extractContent($messageData),
            'metadata' => ['source' => 'whatsapp', 'raw' => $messageData],
        ];

        app(MessageService::class)->handleInbound($messagePayload);
    }

    /**
     * Process Facebook message
     */
    protected function processFacebookMessage($messageData)
    {
        $channel = \App\Modules\Omnichannel\Models\Channel::where('type', 'facebook_messenger')
            ->active()
            ->first();

        if (!$channel) {
            return;
        }

        $senderId = $messageData['sender']['id'] ?? '';
        
        $messagePayload = [
            'tenant_id' => app(TenantContext::class)->id() ?? $this->getTenantFromChannel($channel),
            'channel_id' => $channel->id,
            'sender_id' => $senderId,
            'sender_name' => $messageData['sender']['name'] ?? null,
            'social_handle' => $senderId,
            'message_type' => $messageData['message']['text'] ? 'text' : 'attachment',
            'content' => ['text' => $messageData['message']['text'] ?? ''],
            'metadata' => ['source' => 'facebook', 'raw' => $messageData],
        ];

        app(MessageService::class)->handleInbound($messagePayload);
    }

    /**
     * Process Telegram message
     */
    protected function processTelegramMessage($message)
    {
        $channel = \App\Modules\Omnichannel\Models\Channel::where('type', 'telegram')
            ->active()
            ->first();

        if (!$channel) {
            return;
        }

        $senderId = $message['from']['id'] ?? '';
        
        $messagePayload = [
            'tenant_id' => app(TenantContext::class)->id() ?? $this->getTenantFromChannel($channel),
            'channel_id' => $channel->id,
            'sender_id' => $senderId,
            'sender_name' => ($message['from']['first_name'] ?? '') . ' ' . ($message['from']['last_name'] ?? ''),
            'social_handle' => '@' . ($message['from']['username'] ?? $senderId),
            'message_type' => $message['text'] ? 'text' : 'media',
            'content' => ['text' => $message['text'] ?? ''],
            'metadata' => ['source' => 'telegram', 'raw' => $message],
        ];

        app(MessageService::class)->handleInbound($messagePayload);
    }

    /**
     * Extract content based on message type
     */
    protected function extractContent($messageData)
    {
        $type = $messageData['type'] ?? 'text';
        
        switch ($type) {
            case 'text':
                return ['text' => $messageData['text'] ?? ''];
            case 'image':
                return [
                    'text' => $messageData['caption'] ?? '',
                    'media_url' => $messageData['media_url'] ?? null,
                ];
            case 'location':
                return [
                    'latitude' => $messageData['latitude'],
                    'longitude' => $messageData['longitude'],
                    'name' => $messageData['name'] ?? null,
                ];
            default:
                return ['text' => '[Media not supported]'];
        }
    }

    /**
     * Verify webhook signature
     */
    protected function verifyWebhookSignature(Request $request)
    {
        // TODO: Implement signature verification per channel
        return true;
    }

    /**
     * Get tenant from channel
     */
    protected function getTenantFromChannel($channel)
    {
        return $channel->tenant_id;
    }
}
