<?php

namespace App\Modules\Projects\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends Model
{
    use BelongsToTenant;

    use HasFactory;

    public $timestamps = false;

    protected $table = 'project_members';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'user_id',
        'role',
    ];

    /**
     * Get the project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Scope by role.
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Check if user is a lead.
     */
    public function isLead(): bool
    {
        return $this->role === 'lead';
    }
}
