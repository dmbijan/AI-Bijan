<?php

namespace App\Modules\McpStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class McpSession extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'client_name', 'client_version', 'transport', 'status',
        'capabilities', 'meta', 'last_seen_at',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'meta' => 'array',
        'last_seen_at' => 'datetime',
    ];

    protected $attributes = [
        'transport' => 'http',
        'status' => 'active',
    ];

    public function executions(): HasMany
    {
        return $this->hasMany(McpExecution::class, 'mcp_session_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Tanda sesi masih hidup.
     */
    public function sentuh(): void
    {
        $this->forceFill(['last_seen_at' => now()])->save();
    }
}
