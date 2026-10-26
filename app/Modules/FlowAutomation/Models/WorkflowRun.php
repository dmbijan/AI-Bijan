<?php

namespace App\Modules\FlowAutomation\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class WorkflowRun extends Model
{
    use BelongsToTenant;

    use HasUlids;

    protected $table = 'workflow_runs';

    protected $fillable = [
        'id',
        'tenant_id',
        'workflow_id',
        'workflow_version',
        'trigger_source',
        'trigger_payload',
        'idempotency_key',
        'status',
        'current_step',
        'total_steps',
        'steps_log',
        'error_message',
        'retry_count',
        'next_retry_at',
        'started_at',
        'completed_at',
        'duration_ms',
    ];

    protected $casts = [
        'trigger_payload' => 'array',
        'steps_log' => 'array',
        'retry_count' => 'integer',
        'current_step' => 'integer',
        'total_steps' => 'integer',
        'duration_ms' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_WAITING_APPROVAL = 'waiting_approval';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_DEAD_LETTER = 'dead_letter';

    public function workflow()
    {
        return $this->belongsTo(WorkflowDefinition::class, 'workflow_id');
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

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_RUNNING,
            self::STATUS_WAITING_APPROVAL,
        ]);
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', [self::STATUS_FAILED, self::STATUS_DEAD_LETTER]);
    }

    public function scopeDueForRetry($query)
    {
        return $query->where('status', self::STATUS_FAILED)
            ->whereNotNull('next_retry_at')
            ->where('next_retry_at', '<=', now());
    }

    // ===== Helpers =====

    /**
     * Log satu langkah
     */
    public function logStep(string $step, string $status, array $extra = []): void
    {
        $log = $this->steps_log ?? [];
        $log[] = array_merge([
            'step' => $step,
            'status' => $status,
            'at' => now()->toIso8601String(),
        ], $extra);

        $this->steps_log = $log;
        $this->save();
    }

    /**
     * Tanda sebagai selesai
     */
    public function markCompleted(): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        $this->duration_ms = $this->started_at
            ? (int) $this->started_at->diffInMilliseconds($this->completed_at)
            : null;
        $this->save();
    }

    /**
     * Tanda gagal dan jadualkan retry
     */
    public function markFailed(string $error): void
    {
        $limits = config('flow_automation.limits', []);
        $maxRetries = $limits['max_retry_attempts'] ?? 3;
        $backoff = $limits['retry_backoff_seconds'] ?? [30, 120, 600];

        $this->error_message = $error;
        $this->retry_count = ($this->retry_count ?? 0) + 1;

        if ($this->retry_count > $maxRetries) {
            $this->status = self::STATUS_DEAD_LETTER;
            $this->next_retry_at = null;
        } else {
            $this->status = self::STATUS_FAILED;
            $delay = $backoff[$this->retry_count - 1] ?? end($backoff);
            $this->next_retry_at = now()->addSeconds($delay);
        }

        $this->completed_at = now();
        $this->save();
    }

    /**
     * Boleh retry?
     */
    public function canRetry(): bool
    {
        $maxRetries = config('flow_automation.limits.max_retry_attempts', 3);
        return $this->status === self::STATUS_FAILED
            && ($this->retry_count ?? 0) < $maxRetries;
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_DEAD_LETTER,
        ], true);
    }

    /**
     * Kemajuan dalam peratus
     */
    public function progressPercent(): int
    {
        if (empty($this->total_steps)) {
            return 0;
        }

        return (int) round((($this->current_step ?? 0) / $this->total_steps) * 100);
    }
}
