<?php

namespace App\Modules\VirtualOffice\Services;

use App\Modules\VirtualOffice\Models\Card;
use Illuminate\Support\Facades\DB;

class TaskService
{
    /**
     * Create a new task/card.
     */
    public function create(array $data, int $tenantId): Card
    {
        return DB::transaction(function () use ($data, $tenantId) {
            $card = Card::create([
                'board_id' => $data['board_id'],
                'column_id' => $data['column_id'] ?? null,
                'created_by' => $data['created_by'] ?? auth()->id(),
                'assigned_to' => $data['assigned_to'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'priority' => $data['priority'] ?? 'medium',
                'status' => $data['status'] ?? 'todo',
                'due_date' => $data['due_date'] ?? null,
                'labels' => $data['labels'] ?? [],
                'checklist' => $data['checklist'] ?? [],
            ]);

            // Record history
            $card->history()->create([
                'user_id' => $data['created_by'] ?? auth()->id(),
                'action' => 'created',
                'changes' => ['title' => $data['title']],
            ]);

            return $card;
        });
    }

    /**
     * Update an existing task.
     */
    public function update(Card $card, array $data): Card
    {
        $changes = [];

        foreach (['title', 'description', 'priority', 'status', 'due_date', 'labels', 'assigned_to'] as $field) {
            if (isset($data[$field]) && $data[$field] !== $card->$field) {
                $changes[$field] = ['old' => $card->$field, 'new' => $data[$field]];
            }
        }

        $card->update($data);

        if (!empty($changes)) {
            $card->history()->create([
                'user_id' => auth()->id(),
                'action' => 'updated',
                'changes' => $changes,
            ]);
        }

        return $card->fresh();
    }

    /**
     * Move card to different column (Kanban drag & drop).
     */
    public function moveCard(Card $card, int $columnId): Card
    {
        $oldColumnId = $card->column_id;

        if ($oldColumnId !== $columnId) {
            $card->update(['column_id' => $columnId]);

            $card->history()->create([
                'user_id' => auth()->id(),
                'action' => 'moved',
                'changes' => [
                    'from_column' => $oldColumnId,
                    'to_column' => $columnId,
                ],
            ]);
        }

        return $card->fresh();
    }

    /**
     * Reorder cards within a column.
     */
    public function reorderCards(int $boardId, array $cardsOrder): void
    {
        // $cardsOrder = [['id' => 1, 'position' => 0], ['id' => 2, 'position' => 1], ...]
        DB::transaction(function () use ($boardId, $cardsOrder) {
            foreach ($cardsOrder as $cardData) {
                Card::where('id', $cardData['id'])
                    ->where('board_id', $boardId)
                    ->update(['position' => $cardData['position']]);
            }
        });
    }

    /**
     * Complete a task.
     */
    public function completeTask(Card $card): Card
    {
        return $card->complete();
    }

    /**
     * Add comment to task.
     */
    public function addComment(Card $card, string $body, int $userId): \App\Modules\VirtualOffice\Models\CardComment
    {
        return $card->addComment($body, $userId);
    }

    /**
     * Assign task to agent/user.
     */
    public function assignTask(Card $card, ?int $agentId): Card
    {
        $oldAssignee = $card->assigned_to;

        if ($oldAssignee !== $agentId) {
            $card->update(['assigned_to' => $agentId]);

            $card->history()->create([
                'user_id' => auth()->id(),
                'action' => 'assigned',
                'changes' => [
                    'from' => $oldAssignee,
                    'to' => $agentId,
                ],
            ]);
        }

        return $card->fresh();
    }

    /**
     * Get overdue tasks for a board.
     */
    public function getOverdueTasks(int $boardId): \Illuminate\Database\Eloquent\Collection
    {
        return Card::where('board_id', $boardId)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->startOfDay())
            ->whereNotIn('status', ['done', 'cancelled'])
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get tasks due soon (within 24 hours).
     */
    public function getDueSoonTasks(int $boardId): \Illuminate\Database\Eloquent\Collection
    {
        return Card::where('board_id', $boardId)
            ->whereNotNull('due_date')
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addHours(24))
            ->whereNotIn('status', ['done', 'cancelled'])
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get task statistics for a board.
     */
    public function getBoardStatistics(int $boardId): array
    {
        $cards = Card::where('board_id', $boardId);

        return [
            'total' => (clone $cards)->count(),
            'todo' => (clone $cards)->where('status', 'todo')->count(),
            'in_progress' => (clone $cards)->where('status', 'in_progress')->count(),
            'review' => (clone $cards)->where('status', 'review')->count(),
            'done' => (clone $cards)->where('status', 'done')->count(),
            'overdue' => (clone $cards)->whereNotNull('due_date')
                ->where('due_date', '<', now()->startOfDay())
                ->whereNotIn('status', ['done', 'cancelled'])->count(),
            'completed_percentage' => 0,
        ];
    }

    /**
     * Delete a task.
     */
    public function deleteTask(Card $card): bool
    {
        return $card->delete();
    }
}
