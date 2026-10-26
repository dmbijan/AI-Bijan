<?php

namespace App\Modules\Omnichannel\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Omnichannel\Services\ConversationService;
use App\Modules\Omnichannel\Services\MessageService;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    protected $conversationService;
    protected $messageService;

    public function __construct(
        ConversationService $conversationService,
        MessageService $messageService
    ) {
        $this->conversationService = $conversationService;
        $this->messageService = $messageService;
    }

    /**
     * List conversations
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'channel_id', 'agent_id', 'priority', 'search']);
        $conversations = $this->conversationService->getAll(app(TenantContext::class)->id(), $filters);
        return response()->json($conversations);
    }

    /**
     * Get conversation by ID with messages
     */
    public function show($id)
    {
        try {
            $conversation = $this->conversationService->getById($id);
            
            if ($conversation->tenant_id !== app(TenantContext::class)->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return response()->json($conversation);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Not found: ' . $e->getMessage()], 404);
        }
    }

    /**
     * Update conversation status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,pending,archived',
        ]);

        try {
            $conversation = $this->conversationService->updateStatus($id, $validated['status']);
            return response()->json($conversation);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Assign agent to conversation
     */
    public function assignAgent(Request $request, $id)
    {
        $validated = $request->validate([
            'agent_id' => 'required|integer',
        ]);

        try {
            $conversation = \App\Modules\Omnichannel\Models\Conversation::findOrFail($id);
            $conversation->assignAgent($validated['agent_id']);
            return response()->json($conversation);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Assign failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Add note to conversation
     */
    public function addNote(Request $request, $id)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        try {
            $conversation = \App\Modules\Omnichannel\Models\Conversation::findOrFail($id);
            $notes = $conversation->notes ?? [];
            $notes[] = [
                'text' => $validated['note'],
                'added_by' => auth()->id(),
                'added_at' => now()->toIso8601String(),
            ];
            $conversation->update(['notes' => $notes]);
            return response()->json($conversation);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Add note failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Send message in conversation
     */
    public function sendMessage(Request $request, $id)
    {
        $validated = $request->validate([
            'message_type' => 'nullable|in:text,image,video,audio,document,sticker,location,template,interactive',
            'content' => 'required|array',
            'attachments' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        try {
            $message = $this->messageService->send([
                'tenant_id' => app(TenantContext::class)->id(),
                'conversation_id' => $id,
                'channel_id' => null, // Will be fetched from conversation
                'sender_id' => auth()->id(),
                'message_type' => $validated['message_type'] ?? 'text',
                'content' => $validated['content'],
                'attachments' => $validated['attachments'] ?? [],
                'metadata' => $validated['metadata'] ?? [],
            ]);

            return response()->json($message, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Send failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Upload media attachment
     */
    public function uploadMedia(Request $request, $id)
    {
        $validated = $request->validate([
            'media' => 'required|file|max:25600',
        ]);

        try {
            $result = $this->messageService->uploadMedia($request->file('media'), $id);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get conversation messages
     */
    public function getMessages(Request $request, $id)
    {
        $filters = $request->only(['type', 'direction']);
        $messages = $this->messageService->getMessages($id, $filters);
        return response()->json($messages);
    }

    /**
     * Get statistics
     */
    public function getStatistics()
    {
        $stats = $this->conversationService->getStatistics(app(TenantContext::class)->id());
        return response()->json($stats);
    }
}
