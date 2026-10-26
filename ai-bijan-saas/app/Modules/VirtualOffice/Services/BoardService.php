<?php

namespace App\Modules\VirtualOffice\Services;

use App\Modules\VirtualOffice\Models\Board;
use App\Modules\VirtualOffice\Models\Column;
use App\Modules\VirtualOffice\Models\Card;
use Illuminate\Support\Facades\DB;

class BoardService
{
    /**
     * Create a new board with default columns.
     */
    public function create(array $data, int $tenantId): Board
    {
        return DB::transaction(function () use ($data, $tenantId) {
            $board = Board::create([
                'tenant_id' => $tenantId,
                'created_by' => $data['created_by'] ?? auth()->id(),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'visibility' => $data['visibility'] ?? 'team',
                'color' => $data['color'] ?? '#3B82F6',
                'settings' => $data['settings'] ?? [],
            ]);

            // Create default columns if not provided
            $defaultColumns = config('virtual_office.boards.default_column_names', ['To Do', 'In Progress', 'Review', 'Done']);
            if (!isset($data['columns'])) {
                foreach ($defaultColumns as $position => $columnName) {
                    $board->columns()->create([
                        'name' => $columnName,
                        'position' => $position,
                        'is_default' => true,
                    ]);
                }
            } else {
                // Use provided columns
                foreach ($data['columns'] as $position => $columnData) {
                    $board->columns()->create([
                        'name' => $columnData['name'],
                        'position' => $position,
                        'is_default' => false,
                    ]);
                }
            }

            return $board;
        });
    }

    /**
     * Update an existing board.
     */
    public function update(Board $board, array $data): Board
    {
        $board->update($data);
        return $board->fresh();
    }

    /**
     * Add member to board.
     */
    public function addMember(Board $board, int $userId, string $role = 'editor'): void
    {
        $board->members()->updateOrCreate(
            ['user_id' => $userId],
            ['role' => $role]
        );
    }

    /**
     * Remove member from board.
     */
    public function removeMember(Board $board, int $userId): void
    {
        $board->members()->where('user_id', $userId)->delete();
    }

    /**
     * Create a new column.
     */
    public function createColumn(Board $board, array $data): Column
    {
        return $board->columns()->create([
            'name' => $data['name'],
            'position' => $data['position'] ?? 0,
            'is_default' => $data['is_default'] ?? false,
        ]);
    }

    /**
     * Update column position/order.
     */
    public function reorderColumns(int $boardId, array $columnsOrder): void
    {
        // $columnsOrder = [['id' => 1, 'position' => 0], ['id' => 2, 'position' => 1], ...]
        DB::transaction(function () use ($boardId, $columnsOrder) {
            foreach ($columnsOrder as $columnData) {
                Column::where('id', $columnData['id'])
                    ->where('board_id', $boardId)
                    ->update(['position' => $columnData['position']]);
            }
        });
    }

    /**
     * Archive a board.
     */
    public function archiveBoard(Board $board): void
    {
        $board->archive();
    }

    /**
     * Restore an archived board.
     */
    public function restoreBoard(Board $board): void
    {
        $board->restore();
    }

    /**
     * Get all boards for a tenant.
     */
    public function getTenantBoards(int $tenantId, ?int $userId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Board::where('tenant_id', $tenantId)->whereNull('archived_at');

        // If user is not owner, filter by access
        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('visibility', 'public')
                  ->orWhere('visibility', 'team')
                  ->orWhereHas('members', function ($q) use ($userId) {
                      $q->where('user_id', $userId);
                  });
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get board with full structure (columns + cards).
     */
    public function getBoardWithStructure(int $boardId): ?Board
    {
        return Board::with(['columns.cards', 'members.user'])->find($boardId);
    }

    /**
     * Duplicate a board with all columns and cards.
     */
    public function duplicateBoard(Board $originalBoard, int $newTenantId, string $newName = null): Board
    {
        return DB::transaction(function () use ($originalBoard, $newTenantId, $newName) {
            // Create new board
            $newBoard = Board::create([
                'tenant_id' => $newTenantId,
                'created_by' => $originalBoard->created_by,
                'name' => $newName ?? $originalBoard->name . ' (Copy)',
                'description' => $originalBoard->description,
                'visibility' => $originalBoard->visibility,
                'color' => $originalBoard->color,
                'settings' => $originalBoard->settings,
            ]);

            // Copy columns
            foreach ($originalBoard->columns as $column) {
                $newColumn = $newBoard->columns()->create([
                    'name' => $column->name,
                    'position' => $column->position,
                    'is_default' => $column->is_default,
                ]);

                // Copy cards in this column
                foreach ($column->cards as $card) {
                    $newColumn->cards()->create([
                        'title' => $card->title,
                        'description' => $card->description,
                        'priority' => $card->priority,
                        'status' => $card->status,
                        'due_date' => $card->due_date,
                        'labels' => $card->labels,
                        'checklist' => $card->checklist,
                    ]);
                }
            }

            return $newBoard;
        });
    }
}
