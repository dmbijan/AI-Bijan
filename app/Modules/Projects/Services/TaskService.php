<?php

namespace App\Modules\Projects\Services;

use App\Modules\Projects\Models\Task;
use App\Modules\Projects\Models\Milestone;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TaskService
{
    /**
     * Get all tasks for a project.
     */
    public function getProjectTasks(int $projectId): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('project_id', $projectId)
            ->with(['assignee', 'parent', 'subtasks', 'dependencies', 'dependents'])
            ->orderBy('order')
            ->get();
    }

    /**
     * Get task by ID with relationships.
     */
    public function getTask(int $taskId, int $tenantId): ?Task
    {
        return Task::where('id', $taskId)
            ->where('tenant_id', $tenantId)
            ->with(['assignee', 'parent', 'subtasks', 'dependencies', 'dependents', 'project'])
            ->first();
    }

    /**
     * Create a new task.
     */
    public function createTask(array $data, int $tenantId): Task
    {
        return DB::transaction(function () use ($data, $tenantId) {
            // Check for circular dependencies before creating
            if (isset($data['depends_on_ids'])) {
                foreach ($data['depends_on_ids'] as $depId) {
                    if ($this->wouldCreateCycle($data['project_id'], $depId, null)) {
                        throw ValidationException::withMessages([
                            'depends_on_ids' => ['Cannot add dependency: would create circular reference.']
                        ]);
                    }
                }
            }

            $task = Task::create([
                'tenant_id' => $tenantId,
                'project_id' => $data['project_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'todo',
                'priority' => $data['priority'] ?? 'medium',
                'assigned_to' => $data['assigned_to'] ?? null,
                'parent_task_id' => $data['parent_task_id'] ?? null,
                'order' => $data['order'] ?? 0,
                'due_date' => $data['due_date'] ?? null,
                'estimated_hours' => $data['estimated_hours'] ?? null,
                'story_points' => $data['story_points'] ?? null,
                'labels' => $data['labels'] ?? null,
            ]);

            // Add dependencies if provided
            if (isset($data['depends_on_ids'])) {
                foreach ($data['depends_on_ids'] as $depId) {
                    $task->dependencies()->attach($depId);
                }
            }

            return $task->fresh(['dependencies', 'dependents']);
        });
    }

    /**
     * Update a task.
     */
    public function updateTask(Task $task, array $data, int $tenantId): Task
    {
        return DB::transaction(function () use ($task, $data, $tenantId) {
            // Verify ownership
            if ($task->tenant_id !== $tenantId) {
                throw new \Exception('Task not found.');
            }

            // Check for circular dependencies if updating depends_on_ids
            if (isset($data['depends_on_ids'])) {
                foreach ($data['depends_on_ids'] as $depId) {
                    if ($this->wouldCreateCycle($task->project_id, $depId, $task->id)) {
                        throw ValidationException::withMessages([
                            'depends_on_ids' => ['Cannot add dependency: would create circular reference.']
                        ]);
                    }
                }
            }

            $updatable = [
                'title', 'description', 'status', 'priority', 'assigned_to',
                'parent_task_id', 'order', 'due_date', 'estimated_hours',
                'actual_hours', 'story_points', 'labels'
            ];

            foreach ($updatable as $field) {
                if (isset($data[$field])) {
                    $task->{$field} = $data[$field];
                }
            }

            // Handle status change timestamps
            if (isset($data['status'])) {
                if ($data['status'] === 'in_progress' && !$task->actual_start_date) {
                    $task->actual_start_date = now();
                }
                if ($data['status'] === 'done' && !$task->actual_end_date) {
                    $task->actual_end_date = now();
                    $task->completed_at = now();
                }
            }

            $task->save();

            // Update dependencies
            if (isset($data['depends_on_ids'])) {
                $task->dependencies()->sync($data['depends_on_ids']);
            }

            return $task->fresh(['dependencies', 'dependents', 'assignee']);
        });
    }

    /**
     * Delete a task.
     */
    public function deleteTask(Task $task): bool
    {
        return DB::transaction(function () use ($task) {
            // Delete subtasks first
            $task->subtasks()->each(fn($subtask) => $this->deleteTask($subtask));
            
            // Remove from dependencies
            $task->dependencies()->detach();
            $task->dependents()->detach();
            
            return $task->delete();
        });
    }

    /**
     * Change task status.
     */
    public function setStatus(Task $task, string $status): Task
    {
        // Validate status transition
        $validTransitions = [
            'todo' => ['in_progress', 'cancelled'],
            'in_progress' => ['review', 'todo', 'cancelled'],
            'review' => ['in_progress', 'done', 'cancelled'],
            'done' => [],
            'cancelled' => ['todo'],
        ];

        if (!empty($validTransitions[$task->status]) && 
            !in_array($status, $validTransitions[$task->status])) {
            throw ValidationException::withMessages([
                'status' => ["Cannot transition from {$task->status} to {$status}."]
            ]);
        }

        // Check if all dependencies are done before moving to done
        if ($status === 'done') {
            $pendingDeps = $task->dependencies()
                ->wherePivot('status', '!=', 'done')
                ->count();
            
            if ($pendingDeps > 0) {
                throw ValidationException::withMessages([
                    'status' => ['All dependencies must be completed before marking task as done.']
                ]);
            }
        }

        return $this->updateTask($task, ['status' => $status]);
    }

    /**
     * Assign task to user.
     */
    public function assignTask(Task $task, int $userId): Task
    {
        return $this->updateTask($task, ['assigned_to' => $userId]);
    }

    /**
     * Check if adding a dependency would create a cycle.
     */
    private function wouldCreateCycle(int $projectId, int $newDepId, ?int $excludeTaskId): bool
    {
        if ($newDepId === $excludeTaskId) {
            return true;
        }

        // Check if the new dependency is in the same project
        $depExists = Task::where('id', $newDepId)
            ->where('project_id', $projectId)
            ->exists();

        if (!$depExists) {
            return false;
        }

        // Traverse up the dependency chain
        $visited = [];
        $currentId = $excludeTaskId;

        while ($currentId) {
            if ($currentId === $newDepId) {
                return true;
            }

            if (in_array($currentId, $visited)) {
                break; // Prevent infinite loop
            }

            $visited[] = $currentId;

            // Get parent task
            $parentTask = Task::where('id', $currentId)
                ->whereNotNull('parent_task_id')
                ->first();

            if ($parentTask) {
                $currentId = $parentTask->parent_task_id;
            } else {
                // Check dependencies
                $dep = DB::table('task_dependencies')
                    ->where('task_id', $currentId)
                    ->where('tenant_id', function($query) use ($projectId) {
                        $query->select('tenant_id')
                            ->from('tasks')
                            ->where('id', $currentId)
                            ->limit(1);
                    })
                    ->first();

                if ($dep && $dep->depends_on_id === $newDepId) {
                    return true;
                }

                $currentId = $dep ? $dep->depends_on_id : null;
            }
        }

        return false;
    }

    /**
     * Get overdue tasks for a project.
     */
    public function getOverdueTasks(int $projectId): \Illuminate\Database\Eloquent\Collection
    {
        return Task::where('project_id', $projectId)
            ->overdue()
            ->with(['assignee', 'project'])
            ->get();
    }

    /**
     * Get tasks assigned to a user.
     */
    public function getUserTasks(int $userId, ?int $projectId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Task::where('assigned_to', $userId)
            ->with(['project', 'dependencies', 'dependents']);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        return $query->orderBy('due_date')->get();
    }

    /**
     * Bulk update task statuses.
     */
    public function bulkUpdateStatus(array $taskIds, string $status, int $tenantId): int
    {
        return Task::where('tenant_id', $tenantId)
            ->whereIn('id', $taskIds)
            ->update(['status' => $status, 'updated_at' => now()]);
    }
}
