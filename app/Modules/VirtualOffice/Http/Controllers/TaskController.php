<?php

namespace App\Modules\VirtualOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\VirtualOffice\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * List tasks for a board.
     */
    public function index(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();
        $boardId = $request->query('board_id');
        $status = $request->query('status');
        $priority = $request->query('priority');
        $assignedTo = $request->query('assigned_to');

        $query = \App\Modules\VirtualOffice\Models\Card::with(['board', 'column', 'assignee'])
            ->whereHas('board', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });

        if ($boardId) {
            $query->where('board_id', $boardId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($priority) {
            $query->where('priority', $priority);
        }

        if ($assignedTo) {
            $query->where('assigned_to', $assignedTo);
        }

        $tasks = $query->orderBy('position')->get();

        return response()->json($tasks);
    }

    /**
     * Get a single task.
     */
    public function show(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $task = \App\Modules\VirtualOffice\Models\Card::where('board_id')
            ->whereIn('board_id', function ($q) use ($tenantId) {
                $q->select('id')->from('vo_boards')->where('tenant_id', $tenantId);
            })
            ->with(['board', 'column', 'assignee', 'comments.user', 'history'])
            ->findOrFail($id);

        return response()->json($task);
    }

    /**
     * Create a new task.
     */
    public function store(Request $request)
    {
        $request->validate([
            'board_id' => 'required|exists:vo_boards,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'column_id' => 'nullable|exists:vo_columns,id',
            'assigned_to' => 'nullable|exists:vo_agents,id',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:todo,in_progress,review,done,cancelled',
            'due_date' => 'nullable|date',
            'labels' => 'nullable|array',
            'checklist' => 'nullable|array',
        ]);

        $tenantId = app(TenantContext::class)->id();

        // Verify board belongs to tenant
        $boardExists = \App\Modules\VirtualOffice\Models\Board::where('id', $request->board_id)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (!$boardExists) {
            return response()->json(['message' => 'Board not found'], 404);
        }

        $task = $this->taskService->create(array_merge($request->all(), [
            'created_by' => $request->user()->id,
        ]), $tenantId);

        return response()->json($task, 201);
    }

    /**
     * Update a task.
     */
    public function update(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $task = \App\Modules\VirtualOffice\Models\Card::where('board_id')
            ->whereIn('board_id', function ($q) use ($tenantId) {
                $q->select('id')->from('vo_boards')->where('tenant_id', $tenantId);
            })
            ->findOrFail($id);

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'column_id' => 'nullable|exists:vo_columns,id',
            'assigned_to' => 'nullable|exists:vo_agents,id',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:todo,in_progress,review,done,cancelled',
            'due_date' => 'nullable|date',
            'labels' => 'nullable|array',
            'checklist' => 'nullable|array',
        ]);

        $task = $this->taskService->update($task, $request->all());

        return response()->json($task);
    }

    /**
     * Move task to different column (Kanban drag & drop).
     */
    public function move(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $task = \App\Modules\VirtualOffice\Models\Card::where('board_id')
            ->whereIn('board_id', function ($q) use ($tenantId) {
                $q->select('id')->from('vo_boards')->where('tenant_id', $tenantId);
            })
            ->findOrFail($id);

        $request->validate([
            'column_id' => 'required|exists:vo_columns,id',
        ]);

        $task = $this->taskService->moveCard($task, $request->column_id);

        return response()->json($task);
    }

    /**
     * Reorder tasks within a column/board.
     */
    public function reorder(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();
        $boardId = $request->input('board_id');

        $request->validate([
            'board_id' => 'required|exists:vo_boards,id',
            'cards' => 'required|array',
            'cards.*.id' => 'required|exists:vo_cards,id',
            'cards.*.position' => 'required|integer',
        ]);

        // Verify all cards belong to the board
        $cardCount = \App\Modules\VirtualOffice\Models\Card::where('board_id', $boardId)
            ->whereIn('id', collect($request->cards)->pluck('id')->toArray())
            ->count();

        if ($cardCount !== count($request->cards)) {
            return response()->json(['message' => 'Some cards do not belong to this board'], 400);
        }

        $this->taskService->reorderCards($boardId, $request->cards);

        return response()->json(['message' => 'Tasks reordered successfully']);
    }

    /**
     * Complete a task.
     */
    public function complete(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $task = \App\Modules\VirtualOffice\Models\Card::where('board_id')
            ->whereIn('board_id', function ($q) use ($tenantId) {
                $q->select('id')->from('vo_boards')->where('tenant_id', $tenantId);
            })
            ->findOrFail($id);

        $task = $this->taskService->completeTask($task);

        return response()->json($task);
    }

    /**
     * Add comment to task.
     */
    public function addComment(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $task = \App\Modules\VirtualOffice\Models\Card::where('board_id')
            ->whereIn('board_id', function ($q) use ($tenantId) {
                $q->select('id')->from('vo_boards')->where('tenant_id', $tenantId);
            })
            ->findOrFail($id);

        $request->validate([
            'body' => 'required|string',
        ]);

        $comment = $this->taskService->addComment($task, $request->body, $request->user()->id);

        return response()->json($comment, 201);
    }

    /**
     * Assign task to agent.
     */
    public function assign(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $task = \App\Modules\VirtualOffice\Models\Card::where('board_id')
            ->whereIn('board_id', function ($q) use ($tenantId) {
                $q->select('id')->from('vo_boards')->where('tenant_id', $tenantId);
            })
            ->findOrFail($id);

        $request->validate([
            'assigned_to' => 'required|exists:vo_agents,id',
        ]);

        $task = $this->taskService->assignTask($task, $request->assigned_to);

        return response()->json($task);
    }

    /**
     * Delete a task.
     */
    public function destroy(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $task = \App\Modules\VirtualOffice\Models\Card::where('board_id')
            ->whereIn('board_id', function ($q) use ($tenantId) {
                $q->select('id')->from('vo_boards')->where('tenant_id', $tenantId);
            })
            ->findOrFail($id);

        $this->taskService->deleteTask($task);

        return response()->json(['message' => 'Task deleted successfully']);
    }

    /**
     * Get overdue tasks.
     */
    public function overdue(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();
        $boardId = $request->query('board_id');

        $query = \App\Modules\VirtualOffice\Models\Card::with(['board', 'column', 'assignee'])
            ->whereHas('board', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });

        if ($boardId) {
            $query->where('board_id', $boardId);
        }

        $tasks = $this->taskService->getOverdueTasks($boardId ?? 0);

        return response()->json($tasks);
    }

    /**
     * Get task statistics for a board.
     */
    public function statistics(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();
        $boardId = $request->query('board_id');

        if (!$boardId) {
            return response()->json(['message' => 'board_id is required'], 400);
        }

        // Verify board belongs to tenant
        $boardExists = \App\Modules\VirtualOffice\Models\Board::where('id', $boardId)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (!$boardExists) {
            return response()->json(['message' => 'Board not found'], 404);
        }

        $stats = $this->taskService->getBoardStatistics($boardId);

        return response()->json($stats);
    }
}
