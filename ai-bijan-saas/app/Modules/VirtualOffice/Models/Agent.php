<?php

namespace App\Modules\VirtualOffice\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'vo_agents';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'email',
        'type',
        'avatar_url',
        'role',
        'status',
        'metadata',
        'last_active_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_active_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the agent.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the user associated with the agent.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get tasks assigned to this agent.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to', 'id');
    }

    /**
     * Get schedules for this agent.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'agent_id', 'id');
    }

    /**
     * Check if agent is available (active or idle).
     */
    public function isAvailable(): bool
    {
        return in_array($this->status, ['active', 'idle']);
    }

    /**
     * Check if agent is AI type.
     */
    public function isAI(): bool
    {
        return $this->type === 'ai';
    }

    /**
     * Set status to busy temporarily.
     */
    public function setBusy(): void
    {
        $this->update(['status' => 'busy']);
    }

    /**
     * Set status to active/idle.
     */
    public function setActive(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Update last active timestamp.
     */
    public function touchLastActive(): void
    {
        $this->update(['last_active_at' => now()]);
    }
}
