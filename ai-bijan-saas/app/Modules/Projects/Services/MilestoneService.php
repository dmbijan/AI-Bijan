<?php

namespace App\Modules\Projects\Services;

use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Models\Task;
use Illuminate\Support\Facades\DB;

class MilestoneService
{
    /**
     * Get all milestones for a project.
     */
    public function getProjectMilestones(int $projectId): \Illuminate\Database\Eloquent\Collection
    {
        return Milestone::where('project_id', $projectId)
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Get milestone by ID.
     */
    public function getMilestone(int $milestoneId, int $tenantId): ?Milestone
    {
        return Milestone::where('id', $milestoneId)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Create a new milestone.
     */
    public function createMilestone(array $data, int $tenantId): Milestone
    {
        return DB::transaction(function () use ($data, $tenantId) {
            return Milestone::create([
                'tenant_id' => $tenantId,
                'project_id' => $data['project_id'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'due_date' => $data['due_date'],
                'status' => $data['status'] ?? 'pending',
            ]);
        });
    }

    /**
     * Update a milestone.
     */
    public function updateMilestone(Milestone $milestone, array $data, int $tenantId): Milestone
    {
        return DB::transaction(function () use ($milestone, $data, $tenantId) {
            if ($milestone->tenant_id !== $tenantId) {
                throw new \Exception('Milestone not found.');
            }

            $updatable = ['name', 'description', 'due_date', 'status'];

            foreach ($updatable as $field) {
                if (isset($data[$field])) {
                    $milestone->{$field} = $data[$field];
                }
            }

            // Handle completion
            if (isset($data['status']) && $data['status'] === 'completed') {
                $milestone->completed_at = now();
                $milestone->completion_percentage = 100;
            } elseif (isset($data['status']) && $data['status'] === 'in_progress') {
                $milestone->completed_at = null;
            }

            $milestone->save();

            // Calculate completion percentage based on tasks
            $milestone->recalculateCompletion();

            return $milestone->fresh();
        });
    }

    /**
     * Delete a milestone.
     */
    public function deleteMilestone(Milestone $milestone): bool
    {
        return $milestone->delete();
    }

    /**
     * Recalculate milestone completion percentage based on associated tasks.
     */
    public function recalculateCompletion(Milestone $milestone): void
    {
        // This is a simplified calculation - in reality, you might want to 
        // associate tasks with milestones explicitly
        $projectTasks = Task::where('project_id', $milestone->project_id)->get();
        
        if ($projectTasks->isEmpty()) {
            $milestone->update(['completion_percentage' => 0]);
            return;
        }

        $completedTasks = $projectTasks->where('status', 'done')->count();
        $percentage = round(($completedTasks / $projectTasks->count()) * 100, 2);
        
        $milestone->update(['completion_percentage' => $percentage]);

        // Update status based on completion
        if ($percentage >= 100) {
            $milestone->update(['status' => 'completed']);
        } elseif ($percentage > 0) {
            $milestone->update(['status' => 'in_progress']);
        }
    }

    /**
     * Get overdue milestones for a project.
     */
    public function getOverdueMilestones(int $projectId): \Illuminate\Database\Eloquent\Collection
    {
        return Milestone::where('project_id', $projectId)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('status', '!=', 'completed')
            ->orderBy('due_date')
            ->get();
    }

    /**
     * Bulk update milestone statuses.
     */
    public function bulkUpdateStatus(array $milestoneIds, string $status, int $tenantId): int
    {
        return Milestone::where('tenant_id', $tenantId)
            ->whereIn('id', $milestoneIds)
            ->update([
                'status' => $status,
                'updated_at' => now(),
                'completed_at' => $status === 'completed' ? now() : null,
            ]);
    }
}
