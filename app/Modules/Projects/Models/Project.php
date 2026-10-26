<?php

namespace App\Modules\Projects\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'status',
        'priority',
        'start_date',
        'end_date',
        'budget',
        'spent',
        'tags',
        'manager_id',
        'client_id',
        'owner_id',
        'hourly_rate',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'spent' => 'decimal:2',
        'tags' => 'array',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the project.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the project manager.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'manager_id');
    }

    /**
     * Get the client.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Models\Customer::class, 'client_id');
    }

    /**
     * Get all tasks for this project.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get active tasks only.
     */
    public function activeTasks(): HasMany
    {
        return $this->hasMany(Task::class)->whereIn('status', ['todo', 'in_progress', 'review']);
    }

    /**
     * Get all milestones.
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    /**
     * Get completed milestones.
     */
    public function completedMilestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->where('status', 'completed');
    }

    /**
     * Get timesheets for this project.
     */
    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }

    /**
     * Get billable hours total.
     */
    public function getBillableHoursAttribute(): float
    {
        return $this->timesheets()
            ->where('billable', true)
            ->where('status', 'diluluskan')
            ->sum('hours');
    }

    /**
     * Get total logged hours.
     */
    public function getTotalHoursAttribute(): float
    {
        return $this->timesheets()
            ->where('status', 'diluluskan')
            ->sum('hours');
    }

    /**
     * Get project members.
     */
    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    /**
     * Get member users.
     */
    public function memberUsers(): HasManyThrough
    {
        return $this->hasManyThrough(
            \App\Models\User::class,
            ProjectMember::class,
            'project_id',
            'id',
            'id',
            'user_id'
        );
    }

    /**
     * Get documents.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    /**
     * Get activity log.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ProjectActivity::class);
    }

    /**
     * Scope active projects.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
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
     * Check if project is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->end_date && 
               $this->end_date->isPast() && 
               $this->status !== 'completed' && 
               $this->status !== 'cancelled';
    }

    /**
     * Check if project is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Calculate completion percentage based on tasks.
     */
    public function getCompletionPercentageAttribute(): float
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            return 0;
        }

        $completedTasks = $this->tasks()->where('status', 'done')->count();
        return round(($completedTasks / $totalTasks) * 100, 2);
    }

    /**
     * Generate project code.
     */
    public static function generateCode(): string
    {
        $year = now()->format('Y');
        $lastCode = static::whereYear('created_at', $year)
            ->orderBy('code', 'desc')
            ->value('code');

        if ($lastCode) {
            $number = intval(substr($lastCode, -4)) + 1;
        } else {
            $number = 1;
        }

        return 'PRJ-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
