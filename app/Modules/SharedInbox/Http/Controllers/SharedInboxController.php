<?php

namespace App\Modules\SharedInbox\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SharedInbox\Models\CannedResponse;
use App\Modules\SharedInbox\Models\InboxConversation;
use App\Modules\SharedInbox\Models\InboxMacro;
use App\Modules\SharedInbox\Models\SharedInbox;
use App\Modules\SharedInbox\Services\SharedInboxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SharedInboxController extends Controller
{
    public function __construct(private SharedInboxService $inbox) {}

    /**
     * Senarai peti masuk.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SharedInbox::query()->orderBy('name');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
            'meta' => ['strategi' => SharedInbox::strategi()],
        ]);
    }

    /**
     * Cipta peti masuk.
     */
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'channel_type' => ['nullable', 'string', 'in:chat,email,whatsapp,messenger,voice,video'],
            'provider' => ['nullable', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:1000'],
            'assignment_strategy' => ['nullable', 'string', 'in:round_robin,least_loaded,manual,skill_match'],
            'sla_response_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'sla_resolution_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'business_hours' => ['nullable', 'array'],
            'ai_enabled' => ['nullable', 'boolean'],
            'ai_confidence_threshold' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'auto_assign_enabled' => ['nullable', 'boolean'],
            'settings' => ['nullable', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $data['slug'] = Str::slug($data['name']);
        $data['created_by'] = auth()->id();

        $inbox = SharedInbox::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Peti masuk berjaya dicipta.',
            'data' => $inbox,
        ], 201);
    }

    /**
     * Kemas kini peti masuk.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $inbox = SharedInbox::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'assignment_strategy' => ['nullable', 'string', 'in:round_robin,least_loaded,manual,skill_match'],
            'sla_response_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'sla_resolution_minutes' => ['nullable', 'integer', 'min:0', 'max:10080'],
            'business_hours' => ['nullable', 'array'],
            'ai_enabled' => ['nullable', 'boolean'],
            'ai_confidence_threshold' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'auto_assign_enabled' => ['nullable', 'boolean'],
            'welcome_message' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'settings' => ['nullable', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $inbox->update($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Peti masuk berjaya dikemas kini.',
            'data' => $inbox->fresh(),
        ]);
    }

    /**
     * Buang peti masuk.
     */
    public function destroy(int $id): JsonResponse
    {
        SharedInbox::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Peti masuk berjaya dibuang.',
        ]);
    }

    /**
     * Perbincangan dalam peti masuk.
     */
    public function conversations(Request $request, int $id): JsonResponse
    {
        $inbox = SharedInbox::findOrFail($id);

        $query = InboxConversation::query()->where('shared_inbox_id', $inbox->id);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->input('assigned_to'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('last_message_at')
                ->limit(min(500, (int) $request->input('limit', 50)))
                ->get(),
        ]);
    }

    /**
     * Cipta perbincangan.
     */
    public function storeConversation(Request $request, int $id): JsonResponse
    {
        $inbox = SharedInbox::findOrFail($id);

        $v = Validator::make($request->all(), [
            'contact_name' => ['nullable', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email', 'max:180'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'subject' => ['nullable', 'string', 'max:250'],
            'content' => ['nullable', 'string', 'max:10000'],
            'priority' => ['nullable', 'string', 'in:low,normal,high,urgent'],
            'tags' => ['nullable', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $conversation = $this->inbox->createConversation($inbox, $v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Perbincangan berjaya dicipta.',
            'data' => $conversation,
        ], 201);
    }

    /**
     * Butiran perbincangan dengan mesej.
     */
    public function showConversation(int $id): JsonResponse
    {
        $conversation = InboxConversation::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'conversation' => $conversation,
                'messages' => $conversation->messages()->orderBy('sent_at')->get(),
            ],
        ]);
    }

    /**
     * Hantar mesej.
     */
    public function sendMessage(Request $request, int $id): JsonResponse
    {
        $conversation = InboxConversation::findOrFail($id);

        $v = Validator::make($request->all(), [
            'content' => ['required', 'string', 'max:10000'],
            'type' => ['nullable', 'string', 'in:incoming,outgoing,note'],
            'attachments' => ['nullable', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $mesej = $this->inbox->addMessage($conversation, array_merge($v->validated(), [
            'sender_id' => auth()->id(),
            'sender_type' => 'agent',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Mesej berjaya dihantar.',
            'data' => $mesej,
        ], 201);
    }

    /**
     * Tugasan semula perbincangan.
     */
    public function assign(Request $request, int $id): JsonResponse
    {
        $conversation = InboxConversation::findOrFail($id);

        $conversation = $this->inbox->assignConversation(
            $conversation,
            $request->filled('assigned_to') ? (int) $request->input('assigned_to') : null
        );

        return response()->json([
            'success' => true,
            'message' => 'Perbincangan berjaya ditugaskan.',
            'data' => $conversation,
        ]);
    }

    /**
     * Kemas kini status / SLA perbincangan.
     */
    public function updateConversation(Request $request, int $id): JsonResponse
    {
        $conversation = InboxConversation::findOrFail($id);

        $v = Validator::make($request->all(), [
            'status' => ['nullable', 'string', 'in:open,pending,resolved,closed'],
            'priority' => ['nullable', 'string', 'in:low,normal,high,urgent'],
            'tags' => ['nullable', 'array'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();

        if (($data['status'] ?? null) === 'resolved' && $conversation->resolved_at === null) {
            $data['resolved_at'] = now();
        }

        if (($data['status'] ?? null) === 'closed' && $conversation->closed_at === null) {
            $data['closed_at'] = now();
        }

        $conversation->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Perbincangan berjaya dikemas kini.',
            'data' => $conversation->fresh(),
        ]);
    }

    /**
     * Senarai ulasan sedia ada.
     */
    public function cannedResponses(Request $request, int $id): JsonResponse
    {
        SharedInbox::findOrFail($id);

        $query = CannedResponse::query()
            ->where('tenant_id', $this->inbox->tenantId())
            ->where(function ($q) use ($id) {
                $q->where('shared_inbox_id', $id)->orWhereNull('shared_inbox_id');
            })
            ->orderBy('short_code');

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 200))->get(),
        ]);
    }

    /**
     * Tambah ulasan sedia ada.
     */
    public function storeCannedResponse(Request $request, int $id): JsonResponse
    {
        SharedInbox::findOrFail($id);

        $v = Validator::make($request->all(), [
            'short_code' => ['required', 'string', 'max:60', 'alpha_dash'],
            'title' => ['nullable', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:'.config('shared_inbox.canned_responses.max_content_length', 4000)],
            'category' => ['nullable', 'string', 'max:60'],
            'language' => ['nullable', 'string', 'max:5'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $item = CannedResponse::create(array_merge($v->validated(), [
            'shared_inbox_id' => $id,
            'created_by' => auth()->id(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Ulasan sedia ada berjaya ditambah.',
            'data' => $item,
        ], 201);
    }

    /**
     * Kemas kini ulasan sedia ada.
     */
    public function updateCannedResponse(Request $request, int $id, int $responseId): JsonResponse
    {
        SharedInbox::findOrFail($id);
        $item = CannedResponse::findOrFail($responseId);

        $v = Validator::make($request->all(), [
            'short_code' => ['sometimes', 'string', 'max:60', 'alpha_dash'],
            'title' => ['nullable', 'string', 'max:150'],
            'content' => ['sometimes', 'string', 'max:4000'],
            'category' => ['nullable', 'string', 'max:60'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $item->update($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Ulasan sedia ada berjaya dikemas kini.',
            'data' => $item->fresh(),
        ]);
    }

    /**
     * Buang ulasan sedia ada.
     */
    public function destroyCannedResponse(int $id, int $responseId): JsonResponse
    {
        SharedInbox::findOrFail($id);
        CannedResponse::findOrFail($responseId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ulasan sedia ada berjaya dibuang.',
        ]);
    }

    /**
     * Senarai makro.
     */
    public function macros(int $id): JsonResponse
    {
        SharedInbox::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => InboxMacro::query()
                ->where('shared_inbox_id', $id)
                ->orderBy('name')
                ->get(),
            'meta' => ['jenis_tindakan' => InboxMacro::jenisTindakan()],
        ]);
    }

    /**
     * Cipta makro.
     */
    public function storeMacro(Request $request, int $id): JsonResponse
    {
        SharedInbox::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'actions' => ['required', 'array', 'min:1', 'max:'.config('shared_inbox.macros.max_actions', 10)],
            'actions.*.type' => ['required', 'string', 'in:send_reply,set_status,set_priority,assign_agent,add_tag,remove_tag,close_conversation,resolve_sla'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $macro = InboxMacro::create(array_merge($v->validated(), [
            'shared_inbox_id' => $id,
            'created_by' => auth()->id(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Makro berjaya dicipta.',
            'data' => $macro,
        ], 201);
    }

    /**
     * Kemas kini makro.
     */
    public function updateMacro(Request $request, int $id, int $macroId): JsonResponse
    {
        SharedInbox::findOrFail($id);
        $macro = InboxMacro::findOrFail($macroId);

        $v = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'actions' => ['nullable', 'array', 'max:'.config('shared_inbox.macros.max_actions', 10)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $macro->update($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Makro berjaya dikemas kini.',
            'data' => $macro->fresh(),
        ]);
    }

    /**
     * Buang makro.
     */
    public function destroyMacro(int $id, int $macroId): JsonResponse
    {
        SharedInbox::findOrFail($id);
        InboxMacro::findOrFail($macroId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Makro berjaya dibuang.',
        ]);
    }

    /**
     * Jalankan makro pada perbincangan.
     */
    public function runMacro(Request $request, int $id, int $macroId): JsonResponse
    {
        SharedInbox::findOrFail($id);
        $macro = InboxMacro::findOrFail($macroId);
        $conversation = InboxConversation::findOrFail($request->input('conversation_id'));

        $hasil = $this->inbox->runMacro($macro, $conversation);

        return response()->json([
            'success' => $hasil['success'],
            'message' => 'Makro berjaya dijalankan.',
            'data' => [
                'dilakukan' => $hasil['executed'],
                'conversation' => $conversation->fresh(),
            ],
        ]);
    }

    /**
     * Semak SLA dan tandakan yang dilampaui.
     */
    public function checkSla(): JsonResponse
    {
        $hasil = $this->inbox->semakSla();

        return response()->json([
            'success' => true,
            'data' => $hasil,
        ]);
    }

    /**
     * Statistik peti masuk.
     */
    public function statistics(int $id): JsonResponse
    {
        $inbox = SharedInbox::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->inbox->statistics($inbox),
        ]);
    }

    /**
     * Jana balasan AI untuk mesej pelanggan (AI Gateway / OpenGenAI).
     */
    public function generateAiReply(Request $request, int $id): JsonResponse
    {
        $conversation = InboxConversation::findOrFail($id);

        $v = Validator::make($request->all(), [
            'message' => ['required', 'string', 'max:4000'],
            'send' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $hasil = $this->inbox->janaBalasanAI($conversation, $v->validated()['message']);

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'],
            ], 422);
        }

        // Hantar terus jika diminta.
        if ($v->boolean('send')) {
            $this->inbox->addMessage($conversation, [
                'content' => $hasil['text'],
                'type' => InboxMessage::TYPE_OUTGOING,
                'sender_type' => 'agent',
                'is_ai_generated' => true,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Balasan AI dijana.',
            'data' => [
                'reply' => $hasil['text'],
                'source' => $hasil['sumber'],
                'sent' => $v->boolean('send'),
            ],
        ]);
    }

    /**
     * @param  \Illuminate\Contracts\Validation\Validator  $v
     */
    protected function ralatValidasi($v): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Input tidak sah.',
            'errors' => $v->errors(),
        ], 422);
    }
}
