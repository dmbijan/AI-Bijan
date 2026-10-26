<?php

namespace App\Modules\AgentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentToolBinding extends Model
{
    use BelongsToTenant;

    public const PERMISSION_READ = 'read';
    public const PERMISSION_WRITE = 'write';
    public const PERMISSION_ADMIN = 'admin';

    protected $fillable = [
        'tenant_id',
        'agent_blueprint_id', 'tool_key', 'tool_type', 'tool_config',
        'permission_level', 'is_enabled',
    ];

    protected $casts = [
        'tool_config' => 'array',
        'is_enabled' => 'boolean',
    ];

    protected $attributes = [
        'tool_type' => 'internal',
        'permission_level' => self::PERMISSION_READ,
        'is_enabled' => true,
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AgentBlueprint::class, 'agent_blueprint_id');
    }

    public function bolehTulis(): bool
    {
        return in_array($this->permission_level, [self::PERMISSION_WRITE, self::PERMISSION_ADMIN], true);
    }

    public function bolehUrus(): bool
    {
        return $this->permission_level === self::PERMISSION_ADMIN;
    }
}
