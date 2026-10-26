<?php

namespace App\Modules\Boardroom\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class BoardroomSession extends Model
{
    use BelongsToTenant;

    use HasUlids;

    protected $table = 'boardroom_sessions';

    protected $fillable = [
        'tenant_id',
        'requested_by',
        'question',
        'category',
        'context',
        'current_step',
        'agents_involved',
        'status',
        'conflict_detected',
        'conflict_details',
        'review_round',
        'summary',
        'options',
        'decision',
        'decision_by',
        'decision_reason',
        'decision_at',
        'closed_at',
    ];

    protected $casts = [
        'context' => 'array',
        'agents_involved' => 'array',
        'conflict_details' => 'array',
        'summary' => 'array',
        'options' => 'array',
        'conflict_detected' => 'boolean',
        'review_round' => 'integer',
        'current_step' => 'integer',
        'decision_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_AWAITING_REVIEW = 'awaiting_review';
    const STATUS_AWAITING_OWNER_DECISION = 'awaiting_owner_decision';
    const STATUS_DECIDED = 'decided';
    const STATUS_CLOSED = 'closed';
    const STATUS_CANCELLED = 'cancelled';

    public function findings()
    {
        return $this->hasMany(BoardroomFinding::class, 'session_id');
    }

    // ===== Scopes =====

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_CLOSED,
            self::STATUS_CANCELLED,
        ]);
    }

    public function scopeAwaitingOwner($query)
    {
        return $query->where('status', self::STATUS_AWAITING_OWNER_DECISION);
    }

    // ===== Helpers =====

    public function getStepInfo(): ?array
    {
        return config("boardroom.workflow_steps.{$this->current_step}");
    }

    public function advanceStep(): void
    {
        $maxStep = count(config('boardroom.workflow_steps', []));
        if ($this->current_step < $maxStep) {
            $this->current_step++;
        }
    }

    public function isOpen(): bool
    {
        return !in_array($this->status, [self::STATUS_CLOSED, self::STATUS_CANCELLED], true);
    }

    public function canRecordDecision(): bool
    {
        return $this->status === self::STATUS_AWAITING_OWNER_DECISION;
    }

    /**
     * Ringkasan dapatan mengikut jenis (fakta/anggaran/andaian)
     */
    public function findingsByType(): array
    {
        $grouped = [];

        foreach ($this->findings as $finding) {
            $grouped[$finding->finding_type][] = $finding->toArray();
        }

        return $grouped;
    }
    /**
     * Semak sama ada semua dapatan kritikal mempunyai sumber
     */
    public function hasUnsupportedFacts(): bool
    {
        return $this->findings()
            ->where('finding_type', 'fact')
            ->whereNull('source_reference')
            ->exists();
    }
}
