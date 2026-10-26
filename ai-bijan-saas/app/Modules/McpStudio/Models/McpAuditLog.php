<?php

namespace App\Modules\McpStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McpAuditLog extends Model
{
    use BelongsToTenant;

    public const ACTION_TOOL_CALLED = 'tool_called';
    public const ACTION_TOOL_APPROVED = 'tool_approved';
    public const ACTION_TOOL_REJECTED = 'tool_rejected';
    public const ACTION_TOOL_EXECUTED = 'tool_executed';
    public const ACTION_RESOURCE_READ = 'resource_read';
    public const ACTION_PROMPT_USED = 'prompt_used';

    protected $fillable = [
        'tenant_id',
        'mcp_execution_id', 'action', 'actor_type', 'actor_id',
        'context', 'ip_address',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function execution(): BelongsTo
    {
        return $this->belongsTo(McpExecution::class, 'mcp_execution_id');
    }
}
