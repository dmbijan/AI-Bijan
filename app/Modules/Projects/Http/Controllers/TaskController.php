<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Task;
use App\Modules\Projects\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$request->tenant_id` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * List all tasks for a project.
     */
    public function index(Project $project, Request $request): JsonResponse
    {
        if ($project->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $query = $project->tasks()->with(['assignee', 'parent', 'subtasks', 'dependencies', 'dependents']);

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->byPriority($request->priority);
        }

        // Filter by assignee
        if ($request->has('assigned_to')) {
            $query->assignedTo($request->assigned_to);
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $tasks = $query->orderBy('order')->paginate($request->get('per_page', 15));

        return response()->json($tasks);
    }

    /**
     * Show a specific task.
     */
    public function show(Task $task): JsonResponse
    {
        if ($task->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $task->load([
            'assignee',
            'parent',
            'subtasks',
            'dependencies',
            'dependents',
            'project',
        ]);

        return response()->json($task);
    }

    /**
     * Create a new task.
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        if ($project->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => ['sometimes', Rule::in(['todo', 'in_progress', 'review', 'done', 'cancelled'])],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'critical'])],
            'assigned_to' => 'nullable|exists:users,id',
            'parent_task_id' => 'nullable|exists:tasks,id',
            'order' => 'nullable|integer|min:0',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'story_points' => 'nullable|numeric|min:0',
            'labels' => 'nullable|array',
            'depends_on_ids' => 'nullable|array',
            'depends_on_ids.*' => 'exists:tasks,id',
        ]);

        $task = $this->taskService->createTask(
            array_merge($validated, ['project_id' => $project->id]),
            app(\App\Support\Tenancy\TenantContext::class)->id()
        );

        return response()->json($task, 201);
    }

    /**
     * Update a task.
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        if ($task->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => ['sometimes', Rule::in(['todo', 'in_progress', 'review', 'done', 'cancelled'])],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high', 'critical'])],
            'assigned_to' => 'nullable|exists:users,id',
            'parent_task_id' => 'nullable|exists:tasks,id',
            'order' => 'nullable|integer|min:0',
            'due_date' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'story_points' => 'nullable|numeric|min:0',
            'labels' => 'nullable|array',
            'depends_on_ids' => 'nullable|array',
            'depends_on_ids.*' => 'exists:tasks,id',
        ]);

        $task = $this->taskService->updateTask($task, $validated, app(\App\Support\Tenancy\TenantContext::class)->id());

        return response()->json($task);
    }

    /**
     * Delete a task.
     */
    public function destroy(Task $task): JsonResponse
    {
        if ($task->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $this->taskService->deleteTask($task);

        return response()->json(['message' => 'Task deleted successfully.']);
    }

    /**
     * Change task status.
     */
    public function updateStatus(Task $task, Request $request): JsonResponse
    {
        if ($task->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['todo', 'in_progress', 'review', 'done', 'cancelled'])],
        ]);

        $task = $this->taskService->setStatus($task, $validated['status']);

        return response()->json($task);
    }

    /**
     * Assign task to user.
     */
    public function assign(Task $task, Request $request): JsonResponse
    {
        if ($task->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $task = $this->taskService->assignTask($task, $validated['user_id']);

        return response()->json($task);
    }

    /**
     * Get overdue tasks for a project.
     */
    public function overdue(Project $project): JsonResponse
    {
        if ($project->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $tasks = $this->taskService->getOverdueTasks($project->id);

        return response()->json($tasks);
    }

    /**
     * Get tasks assigned to current user.
     */
    public function myTasks(Request $request): JsonResponse
    {
        $projectId = $request->input('project_id');
        $tasks = $this->taskService->getUserTasks(Auth::id(), $projectId);

        return response()->json($tasks);
    }

    public function statistics(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'statistics']);
    }

    public function move(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'move']);
    }

    public function reorder(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'reorder']);
    }

    public function complete(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'complete']);
    }

    public function addComment(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'addComment']);
    }
}
