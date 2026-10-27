<?php

namespace App\Modules\Projects\Models;


use App\Models\Concerns\BelongsToTenant;use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectActivity extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'project_activities';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'user_id',
        'action',
        'changes',
        'description',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * Get the tenant that owns the activity.
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
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Scope by action type.
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope recent activities.
     */
    public function scopeRecent($query, int $limit = 50)
    {
        return $query->latest()->take($limit);
    }
}
