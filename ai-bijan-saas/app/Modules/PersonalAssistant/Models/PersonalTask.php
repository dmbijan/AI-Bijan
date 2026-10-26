<?php

namespace App\Modules\PersonalAssistant\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class PersonalTask extends Model
{
    use BelongsToTenant;

    use HasUlids;

    protected $table = 'ai_tasks';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'agent_id',
        'assistant_type',
        'objective',
        'input_reference',
        'input_data',
        'allowed_tools',
        'risk_level',
        'approval_required',
        'budget_limit',
        'deadline',
        'status',
        'result_reference',
        'result_data',
        'error_details',
        'approval_id',
        'completed_at',
    ];

    protected $casts = [
        'input_data' => 'array',
        'allowed_tools' => 'array',
        'result_data' => 'array',
        'approval_required' => 'boolean',
        'budget_limit' => 'decimal:2',
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'Draft';
    const STATUS_PLANNED = 'Planned';
    const STATUS_AWAITING_APPROVAL = 'Awaiting Approval';
    const STATUS_QUEUED = 'Queued';
    const STATUS_RUNNING = 'Running';
    const STATUS_COMPLETED = 'Completed';
    const STATUS_PARTIALLY_COMPLETED = 'Partially Completed';
    const STATUS_FAILED = 'Failed';
    const STATUS_CANCELLED = 'Cancelled';

    public const RISK_LOW = 'low';
    public const RISK_MODERATE = 'moderate';
    public const RISK_HIGH = 'high';
    public const RISK_CRITICAL = 'critical';

    // ===== Scopes =====

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PLANNED,
            self::STATUS_QUEUED,
            self::STATUS_RUNNING,
            self::STATUS_AWAITING_APPROVAL,
        ]);
    }

    public function scopeAwaitingApproval($query)
    {
        return $query->where('status', self::STATUS_AWAITING_APPROVAL);
    }

    // ===== Helpers =====

    public function getAvailableAssistants()
    {
        return config('personal_assistant.assistants', []);
    }

    public static function getRiskLevels()
    {
        return [
            self::RISK_LOW => 'Ringkas laporan, tiada kesan luaran',
            self::RISK_MODERATE => 'Cipta task atau draft',
            self::RISK_HIGH => 'Hantar komunikasi luaran, ubah rekod kritikal',
            self::RISK_CRITICAL => 'Refund, payout, komitmen kewangan',
        ];
    }

    public function requiresApproval()
    {
        return $this->approval_required
            || in_array($this->risk_level, [self::RISK_HIGH, self::RISK_CRITICAL]);
    }

    public function isTerminal()
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_PARTIALLY_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Sahkan peralihan status yang dibenarkan
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $transitions = [
            self::STATUS_DRAFT => [self::STATUS_PLANNED, self::STATUS_CANCELLED],
            self::STATUS_PLANNED => [self::STATUS_AWAITING_APPROVAL, self::STATUS_QUEUED, self::STATUS_CANCELLED],
            self::STATUS_AWAITING_APPROVAL => [self::STATUS_QUEUED, self::STATUS_CANCELLED],
            self::STATUS_QUEUED => [self::STATUS_RUNNING, self::STATUS_CANCELLED],
            self::STATUS_RUNNING => [
                self::STATUS_COMPLETED,
                self::STATUS_PARTIALLY_COMPLETED,
                self::STATUS_FAILED,
                self::STATUS_CANCELLED,
            ],
        ];

        return in_array($newStatus, $transitions[$this->status] ?? []);
    }
}
