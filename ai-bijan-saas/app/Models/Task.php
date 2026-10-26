<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use BelongsToTenant;

    protected $table = 'tasks';

    protected $fillable = ['project_id', 'parent_id', 'title', 'description', 'status', 'priority', 'assignee_id', 'start_date', 'due_date', 'estimate_hours', 'is_milestone', 'sort', 'completed_at'];

    protected $casts = ['start_date' => 'date:Y-m-d', 'due_date' => 'date:Y-m-d', 'is_milestone' => 'boolean', 'completed_at' => 'datetime'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function dependencies()
    {
        return $this->belongsToMany(Task::class, 'task_dependencies', 'task_id', 'depends_on_id')->withPivot('tenant_id');
    }

    public function comments()
    {
        return $this->hasMany(TaskComment::class);
    }
}
