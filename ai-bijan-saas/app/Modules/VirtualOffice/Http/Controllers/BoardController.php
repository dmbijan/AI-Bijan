<?php

namespace App\Modules\VirtualOffice\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use App\Http\Controllers\Controller;
use App\Modules\VirtualOffice\Services\BoardService;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    protected $boardService;

    public function __construct(BoardService $boardService)
    {
        $this->boardService = $boardService;
    }

    /**
     * List all boards for the tenant.
     */
    public function index(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();
        $userId = $request->user()->id;

        $boards = $this->boardService->getUserBoards($tenantId, $userId);

        return response()->json($boards);
    }

    /**
     * Get a single board with full structure.
     */
    public function show(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $boardExists = \App\Modules\VirtualOffice\Models\Board::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->exists();

        if (!$boardExists) {
            return response()->json(['message' => 'Board not found'], 404);
        }

        $board = $this->boardService->getBoardDetail($id);

        return response()->json($board);
    }

    /**
     * Create a new board.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'visibility' => 'sometimes|in:private,team,public',
            'color' => 'sometimes|string|max:7',
            'settings' => 'nullable|array',
            'columns' => 'nullable|array',
        ]);

        $tenantId = app(TenantContext::class)->id();

        $board = $this->boardService->create(array_merge($request->all(), [
            'created_by' => $request->user()->id,
        ]), $tenantId);

        return response()->json($board, 201);
    }

    /**
     * Update a board.
     */
    public function update(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $board = \App\Modules\VirtualOffice\Models\Board::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'visibility' => 'sometimes|in:private,team,public',
            'color' => 'sometimes|string|max:7',
            'settings' => 'nullable|array',
        ]);

        $board = $this->boardService->update($board, $request->all());

        return response()->json($board);
    }

    /**
     * Add member to board.
     */
    public function addMember(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $board = \App\Modules\VirtualOffice\Models\Board::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'sometimes|in:owner,editor,viewer',
        ]);

        $this->boardService->addMember($board, $request->user_id, $request->role ?? 'editor');

        return response()->json(['message' => 'Member added successfully']);
    }

    /**
     * Remove member from board.
     */
    public function removeMember(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $board = \App\Modules\VirtualOffice\Models\Board::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $this->boardService->removeMember($board, $request->user_id);

        return response()->json(['message' => 'Member removed successfully']);
    }

    /**
     * Archive a board.
     */
    public function archive(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $board = \App\Modules\VirtualOffice\Models\Board::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $board = $this->boardService->archiveBoard($board);

        return response()->json($board);
    }

    /**
     * Restore an archived board.
     */
    public function restore(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $board = \App\Modules\VirtualOffice\Models\Board::where('id', $id)
            ->whereNotNull('archived_at')
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $board = $this->boardService->restoreBoard($board);

        return response()->json($board);
    }

    /**
     * Duplicate a board.
     */
    public function duplicate(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $originalBoard = \App\Modules\VirtualOffice\Models\Board::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $board = $this->boardService->duplicateBoard($originalBoard, $tenantId);

        return response()->json($board, 201);
    }

    /**
     * Delete a board.
     */
    public function destroy(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $board = \App\Modules\VirtualOffice\Models\Board::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $board->delete();

        return response()->json(['message' => 'Board deleted successfully']);
    }

    /**
     * Add a column to board.
     */
    public function addColumn(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $board = \App\Modules\VirtualOffice\Models\Board::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'position' => 'nullable|integer',
        ]);

        $column = $this->boardService->addColumn($board, $request->name, $request->position);

        return response()->json($column, 201);
    }

    /**
     * Delete a column.
     */
    public function deleteColumn(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $board = \App\Modules\VirtualOffice\Models\Board::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->findOrFail($id);

        $columnId = $request->input('column_id');

        $column = \App\Modules\VirtualOffice\Models\Column::where('board_id', $id)
            ->findOrFail($columnId);

        $this->boardService->deleteColumn($column, $request->target_column_id);

        return response()->json(['message' => 'Column deleted successfully']);
    }
}
