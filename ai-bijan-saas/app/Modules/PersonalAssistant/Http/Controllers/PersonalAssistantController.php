<?php

namespace App\Modules\PersonalAssistant\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PersonalAssistant\Models\PersonalTask;
use App\Modules\PersonalAssistant\Models\MemoryItem;
use App\Modules\PersonalAssistant\Services\AssistantService;
use App\Modules\PersonalAssistant\Services\ContextBuilderService;
use App\Modules\PersonalAssistant\Services\MemoryService;
use App\Modules\PersonalAssistant\Services\ProactiveAlertService;
use App\Modules\PersonalAssistant\Services\TaskExecutor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonalAssistantController extends Controller
{
    public function __construct(
        protected AssistantService $assistants,
        protected TaskExecutor $tasks,
        protected ContextBuilderService $context,
        protected MemoryService $memory,
        protected ProactiveAlertService $alerts
    ) {
    }

    private function tenantId(): int
    {
        return app(\App\Support\Tenancy\TenantContext::class)->id();
    }

    // ===== Assistants =====

    public function listAssistants(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->assistants->listAssistants(),
        ]);
    }

    public function getAssistant(string $key): JsonResponse
    {
        $assistant = $this->assistants->getAssistant($key);

        if (!$assistant) {
            return response()->json([
                'success' => false,
                'message' => "Assistant '{$key}' tidak ditemui.",
            ], 404);
        }

        return response()->json(['success' => true, 'data' => $assistant]);
    }

    public function recommend(): JsonResponse
    {
        $role = auth()->user()->roles()->first()?->name ?? 'staff';

        return response()->json([
            'success' => true,
            'data' => $this->assistants->recommendForRole($role),
        ]);
    }

    // ===== Preferences =====

    public function getPreferences(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->assistants->getPreferences($this->tenantId(), auth()->id()),
        ]);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $prefs = $this->assistants->updatePreferences(
            $this->tenantId(),
            auth()->id(),
            $request->all()
        );

        return response()->json([
            'success' => true,
            'message' => 'Preferensi dikemas kini.',
            'data' => $prefs,
        ]);
    }

    public function switchModel(Request $request): JsonResponse
    {
        $request->validate(['model_id' => 'required|string']);

        try {
            $result = $this->assistants->switchModel(
                $this->tenantId(),
                auth()->id(),
                $request->model_id
            );

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    // ===== Context =====

    public function buildContext(Request $request): JsonResponse
    {
        $request->validate([
            'assistant_type' => 'required|string',
            'query' => 'required|string',
        ]);

        $context = $this->context->buildContext(
            $this->tenantId(),
            auth()->id(),
            $request->assistant_type,
            $request->query
        );

        return response()->json([
            'success' => true,
            'data' => $context,
            'exceeds_limit' => $this->context->exceedsLimit($context),
        ]);
    }

    // ===== Tasks =====

    public function listTasks(Request $request): JsonResponse
    {
        $query = PersonalTask::forTenant($this->tenantId())->forUser(auth()->id());

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        if ($request->filled('assistant_type')) {
            $query->where('assistant_type', $request->assistant_type);
        }
        if ($request->boolean('active_only')) {
            $query->active();
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('created_at')->paginate($request->integer('per_page', 20)),
        ]);
    }

    public function createTask(Request $request): JsonResponse
    {
        $request->validate([
            'objective' => 'required|string',
            'assistant_type' => 'nullable|string',
            'risk_level' => 'nullable|in:low,moderate,high,critical',
        ]);

        $task = $this->tasks->createTask($this->tenantId(), auth()->id(), $request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tugasan dicipta.',
            'data' => $task,
        ], 201);
    }

    public function showTask(string $id): JsonResponse
    {
        $task = PersonalTask::forTenant($this->tenantId())->find($id);

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Tugasan tidak ditemui.'], 404);
        }

        return response()->json(['success' => true, 'data' => $task]);
    }

    public function transitionTask(Request $request, string $id): JsonResponse
    {
        $request->validate(['action' => 'required|in:plan,queue,start,complete,cancel,request_approval']);

        $task = PersonalTask::forTenant($this->tenantId())->find($id);

        if (!$task) {
            return response()->json(['success' => false, 'message' => 'Tugasan tidak ditemui.'], 404);
        }

        try {
            $task = match ($request->action) {
                'plan' => $this->tasks->plan($task),
                'queue' => $this->tasks->queue($task),
                'start' => $this->tasks->start($task),
                'request_approval' => $this->tasks->requestApproval($task, $request->integer('approval_id') ?: null),
                'complete' => $this->tasks->complete(
                    $task,
                    $request->input('result', []),
                    $request->input('result_status', PersonalTask::STATUS_COMPLETED)
                ),
                'cancel' => $this->tasks->cancel($task, $request->input('reason')),
            };

            return response()->json(['success' => true, 'data' => $task]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function taskStats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->tasks->getStats($this->tenantId(), auth()->id()),
        ]);
    }

    // ===== Memory =====

    public function listMemory(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->memory->getForUser(
                $this->tenantId(),
                auth()->id(),
                $request->input('type'),
                $request->integer('limit', 50)
            ),
        ]);
    }

    public function storeMemory(Request $request): JsonResponse
    {
        $request->validate([
            'memory_type' => 'required|in:preference,context,insight,knowledge,history',
            'category' => 'required|string',
            'content_text' => 'required|string',
        ]);

        $item = $this->memory->store(
            $this->tenantId(),
            $request->boolean('shared') ? null : auth()->id(),
            $request->memory_type,
            $request->category,
            $request->content_text,
            $request->all()
        );

        return response()->json(['success' => true, 'data' => $item], 201);
    }

    public function updateMemory(Request $request, string $id): JsonResponse
    {
        $item = MemoryItem::forTenant($this->tenantId())->find($id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Memori tidak ditemui.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->memory->update($item, $request->all()),
        ]);
    }

    public function deleteMemory(string $id): JsonResponse
    {
        $item = MemoryItem::forTenant($this->tenantId())->find($id);

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Memori tidak ditemui.'], 404);
        }

        $this->memory->delete($item);

        return response()->json(['success' => true, 'message' => 'Memori dipadam.']);
    }

    public function memorySummary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->memory->summary($this->tenantId(), auth()->id()),
        ]);
    }

    /**
     * PDPA data subject request — padam memori peribadi pengguna
     */
    public function purgeMyMemory(): JsonResponse
    {
        $result = $this->memory->purgeUserMemory($this->tenantId(), auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Memori peribadi dipadam.',
            'data' => $result,
        ]);
    }

    // ===== Proactive Alerts =====

    public function listAlerts(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->alerts->generateForTenant($this->tenantId()),
            'available_types' => $this->alerts->getAlertTypes(),
        ]);
    }
}
