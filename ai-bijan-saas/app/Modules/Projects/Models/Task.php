<?php

namespace App\Modules\Projects\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'assigned_to',
        'parent_task_id',
        'order',
        'due_date',
        'actual_start_date',
        'actual_end_date',
        'estimated_hours',
        'actual_hours',
        'story_points',
        'labels',
        'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'story_points' => 'decimal:2',
        'labels' => 'array',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the task.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the assignee.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    /**
     * Get parent task.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    /**
     * Get subtasks.
     */
    public function subtasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    /**
     * Get dependencies (other tasks this task depends on).
     */
    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_id')
            ->withTimestamps();
    }

    /**
     * Get dependents (tasks that depend on this task).
     */
    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'depends_on_id', 'task_id')
            ->withTimestamps();
    }

    /**
     * Get timesheets for this task.
     */
    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by priority.
     */
    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope assigned to user.
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope overdue tasks.
     */
    public function scopeOverdue($query)
    {
        return $query->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereIn('status', ['todo', 'in_progress']);
    }

    /**
     * Check if task is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'done';
    }

    /**
     * Check if task is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               !$this->isCompleted();
    }

    /**
     * Get all ancestor tasks (parent chain).
     */
    public function getAncestors(): \Illuminate\Support\Collection
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    /**
     * Get all descendant tasks (subtask tree).
     */
    public function getDescendants(): \Illuminate\Support\Collection
    {
        $descendants = collect();
        $subtasks = $this->subtasks;

        foreach ($subtasks as $subtask) {
            $descendants->push($subtask);
            $descendants = $descendants->merge($subtask->getDescendants());
        }

        return $descendants;
    }

    /**
     * Calculate total hours from subtasks.
     */
    public function getTotalSubtaskHoursAttribute(): float
    {
        return $this->subtasks()->sum('actual_hours') ?? 0;
    }
}
