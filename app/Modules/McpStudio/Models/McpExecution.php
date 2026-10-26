<?php

namespace App\Modules\McpStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class McpExecution extends Model
{
    use BelongsToTenant;

    public const STATUS_PENDING = 'pending';
    public const STATUS_AWAITING_APPROVAL = 'awaiting_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXECUTED = 'executed';
    public const STATUS_FAILED = 'failed';

    public const APPROVAL_NOT_REQUIRED = 'not_required';
    public const APPROVAL_PENDING = 'pending';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';

    protected $fillable = [
        'tenant_id',
        'mcp_tool_id', 'mcp_session_id', 'tool_name', 'arguments', 'result',
        'status', 'approval_status', 'approved_by', 'approved_at',
        'rejection_reason', 'duration_ms', 'error_message',
    ];

    protected $casts = [
        'arguments' => 'array',
        'result' => 'array',
        'approved_at' => 'datetime',
        'duration_ms' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'approval_status' => self::APPROVAL_NOT_REQUIRED,
    ];

    public function tool(): BelongsTo
    {
        return $this->belongsTo(McpTool::class, 'mcp_tool_id');
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(McpSession::class, 'mcp_session_id');
    }

    public function menungguKelulusan(): bool
    {
        return $this->approval_status === self::APPROVAL_PENDING;
    }

    public function telahDiluluskan(): bool
    {
        return $this->approval_status === self::APPROVAL_APPROVED;
    }

    public function telahDitolak(): bool
    {
        return $this->approval_status === self::APPROVAL_REJECTED;
    }

    public function berjaya(): bool
    {
        return $this->status === self::STATUS_EXECUTED;
    }
}
