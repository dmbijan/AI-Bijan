<?php

namespace App\Modules\FlowAutomation\Services;

use App\Modules\FlowAutomation\Models\WorkflowDefinition;
use App\Modules\FlowAutomation\Models\WorkflowRun;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * B-Flow Automation Engine (BAB 28)
 *
 * IDEMPOTENCY, RETRY, KILL SWITCH, dan AUDIT adalah WAJIB.
 */
class FlowEngine
{
    protected ?ActionDispatcher $dispatcher;

    public function __construct(?ActionDispatcher $dispatcher = null)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Cetus workflow dari domain event
     */
    public function triggerEvent(int $tenantId, string $eventName, array $payload = []): array
    {
        $workflows = WorkflowDefinition::findForEvent($tenantId, $eventName);

        $runs = [];
        foreach ($workflows as $workflow) {
            if (!$workflow->conditionsMatch($payload)) {
                continue;
            }

            $runs[] = $this->startRun(
                $workflow,
                'domain_event',
                $payload,
                $this->buildIdempotencyKey($workflow, $eventName, $payload)
            );
        }

        return $runs;
    }

    /**
     * Cetus workflow secara manual
     */
    public function triggerManual(WorkflowDefinition $workflow, array $payload = [], ?string $idempotencyKey = null): WorkflowRun
    {
        if (!$workflow->isRunnable()) {
            throw new \RuntimeException('Workflow tidak aktif atau kill switch dihidupkan.');
        }

        return $this->startRun(
            $workflow,
            'manual',
            $payload,
            $idempotencyKey ?? $this->buildIdempotencyKey($workflow, 'manual', $payload)
        );
    }

    /**
     * Mula satu run (dengan idempotency)
     */
    public function startRun(
        WorkflowDefinition $workflow,
        string $triggerSource,
        array $payload,
        string $idempotencyKey
    ): WorkflowRun {
        // KILL SWITCH
        if ($workflow->kill_switch) {
            throw new \RuntimeException('Kill switch workflow dihidupkan.');
        }

        if (!$workflow->is_active) {
            throw new \RuntimeException('Workflow tidak aktif.');
        }

        // IDEMPOTENCY — elak double execution
        $existing = WorkflowRun::forTenant($workflow->tenant_id)
            ->where('idempotency_key', $idempotencyKey)
            ->whereIn('status', [WorkflowRun::STATUS_COMPLETED, WorkflowRun::STATUS_RUNNING])
            ->first();

        if ($existing) {
            return $existing;
        }

        // RATE LIMIT per workflow
        if (!$this->checkRateLimit($workflow)) {
            throw new \RuntimeException('Had kadar workflow dilampaui.');
        }

        $run = WorkflowRun::create([
            'tenant_id' => $workflow->tenant_id,
            'workflow_id' => $workflow->id,
            'workflow_version' => $workflow->version ?? 1,
            'trigger_source' => $triggerSource,
            'trigger_payload' => $payload,
            'idempotency_key' => $idempotencyKey,
            'status' => WorkflowRun::STATUS_PENDING,
            'current_step' => 0,
            'total_steps' => $workflow->stepCount(),
            'steps_log' => [],
            'retry_count' => 0,
        ]);

        return $this->execute($run, $workflow);
    }

    /**
     * Jalankan langkah-langkah workflow
     */
    public function execute(WorkflowRun $run, ?WorkflowDefinition $workflow = null): WorkflowRun
    {
        $workflow = $workflow ?? $run->workflow;

        if (!$workflow) {
            $run->markFailed('Workflow definition tidak ditemui.');
            return $run;
        }
        $steps = $workflow->steps ?? [];
        $maxActions = config('flow_automation.limits.max_actions_per_run', 50);

        if (count($steps) > $maxActions) {
            $run->markFailed("Melebihi had {$maxActions} tindakan per run.");
            return $run;
        }

        $run->status = WorkflowRun::STATUS_RUNNING;
        $run->started_at = $run->started_at ?? now();
        $run->save();

        try {
            foreach ($steps as $index => $step) {
                $run->current_step = $index + 1;
                $run->save();

                $result = $this->executeStep($workflow, $step, $run->trigger_payload ?? []);

                // Jika langkah perlu kelulusan
                if (($result['status'] ?? null) === 'awaiting_approval') {
                    $run->status = WorkflowRun::STATUS_WAITING_APPROVAL;
                    $run->logStep($this->stepName($step), 'awaiting_approval', $result);
                    $run->save();
                    return $run;
                }

                // Jika langkah gagal
                if (($result['status'] ?? null) === 'failed') {
                    $run->logStep($this->stepName($step), 'failed', $result);
                    $run->markFailed($result['error'] ?? 'Langkah gagal tanpa butiran.');
                    $this->incrementFailure($workflow);
                    return $run;
                }

                $run->logStep($this->stepName($step), 'completed', $result);
            }

            $run->markCompleted();
            $this->incrementRunCounter($workflow);

            return $run;
        } catch (\Throwable $e) {
            Log::error('Workflow run failed', [
                'run_id' => $run->id,
                'workflow_id' => $workflow->id,
                'error' => $e->getMessage(),
            ]);

            $run->logStep('exception', 'failed', ['error' => $e->getMessage()]);
            $run->markFailed($e->getMessage());
            $this->incrementFailure($workflow);

            return $run;
        }
    }

    /**
     * Eksekusi satu langkah
     */
    protected function executeStep(WorkflowDefinition $workflow, $step, array $payload): array
    {
        $stepType = is_array($step) ? ($step['type'] ?? 'internal_api') : $step;
        $stepConfig = is_array($step) ? ($step['config'] ?? []) : [];

        $actionTypes = config('flow_automation.action_types', []);
        if (!isset($actionTypes[$stepType])) {
            return ['status' => 'failed', 'error' => "Jenis tindakan tidak sah: {$stepType}"];
        }

        // Langkah yang perlukan kelulusan
        if ($actionTypes[$stepType]['requires_approval'] ?? false) {
            return [
                'status' => 'awaiting_approval',
                'action_type' => $stepType,
                'config' => $stepConfig,
            ];
        }

        // Dispatch ke handler
        if ($this->dispatcher) {
            try {
                return $this->dispatcher->dispatch($stepType, $stepConfig, $payload);
            } catch (\Throwable $e) {
                return ['status' => 'failed', 'error' => $e->getMessage()];
            }
        }

        // Tiada dispatcher — placeholder
        return [
            'status' => 'completed',
            'action_type' => $stepType,
            'note' => 'Placeholder — dispatcher belum didaftarkan.',
        ];
    }

    /**
     * Retry run yang gagal
     */
    public function retry(WorkflowRun $run): WorkflowRun
    {
        if (!$run->canRetry()) {
            throw new \RuntimeException('Run ini tidak boleh di-retry.');
        }

        $run->status = WorkflowRun::STATUS_PENDING;
        $run->error_message = null;
        $run->next_retry_at = null;
        $run->current_step = 0;
        $run->save();

        return $this->execute($run);
    }

    /**
     * Proses semua run yang due untuk retry
     */
    public function processRetries(): array
    {
        $dueRuns = WorkflowRun::dueForRetry()->limit(50)->get();
        $results = [];

        foreach ($dueRuns as $run) {
            try {
                $results[] = ['run_id' => $run->id, 'status' => $this->retry($run)->status];
            } catch (\Throwable $e) {
                $results[] = ['run_id' => $run->id, 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Batalkan run
     */
    public function cancel(WorkflowRun $run, ?string $reason = null): WorkflowRun
    {
        if ($run->isTerminal()) {
            throw new \RuntimeException('Run sudah tamat.');
        }

        $run->status = WorkflowRun::STATUS_CANCELLED;
        $run->error_message = $reason ?? 'Dibatalkan oleh pengguna.';
        $run->completed_at = now();
        $run->save();

        return $run;
    }

    /**
     * Dry run — simulasi tanpa kesan sampingan
     */
    public function dryRun(WorkflowDefinition $workflow, array $payload = []): array
    {
        $steps = $workflow->steps ?? [];
        $preview = [];

        foreach ($steps as $index => $step) {
            $stepType = is_array($step) ? ($step['type'] ?? 'internal_api') : $step;
            $actionTypes = config('flow_automation.action_types', []);

            $preview[] = [
                'step' => $index + 1,
                'name' => $this->stepName($step),
                'type' => $stepType,
                'requires_approval' => $actionTypes[$stepType]['requires_approval'] ?? false,
                'would_execute' => true,
            ];
        }

        return [
            'workflow' => $workflow->name,
            'conditions_match' => $workflow->conditionsMatch($payload),
            'total_steps' => count($steps),
            'steps' => $preview,
            'note' => 'Ini adalah simulasi. Tiada tindakan sebenar dijalankan.',
        ];
    }

    // ===== Helpers =====

    protected function stepName($step): string
    {
        if (is_array($step)) {
            return $step['name'] ?? $step['type'] ?? 'step';
        }
        return (string) $step;
    }

    protected function buildIdempotencyKey(WorkflowDefinition $workflow, string $source, array $payload): string
    {
        $signature = json_encode([
            'workflow' => $workflow->id,
            'version' => $workflow->version ?? 1,
            'source' => $source,
            'payload_hash' => md5(json_encode($payload)),
        ]);

        return hash('sha256', $signature);
    }

    protected function checkRateLimit(WorkflowDefinition $workflow): bool
    {
        $limits = config('flow_automation.limits', []);
        $maxPerHour = $limits['max_runs_per_hour'] ?? 100;

        $countLastHour = WorkflowRun::where('workflow_id', $workflow->id)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        return $countLastHour < $maxPerHour;
    }

    protected function incrementRunCounter(WorkflowDefinition $workflow): void
    {
        $workflow->increment('run_count');
        $workflow->last_run_at = now();
        $workflow->save();
    }

    protected function incrementFailure(WorkflowDefinition $workflow): void
    {
        $workflow->increment('failure_count');
        $workflow->last_run_at = now();
        $workflow->save();
    }
}
