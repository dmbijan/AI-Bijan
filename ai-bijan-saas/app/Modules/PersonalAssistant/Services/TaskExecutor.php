<?php

namespace App\Modules\PersonalAssistant\Services;

use App\Modules\PersonalAssistant\Models\PersonalTask;
use Illuminate\Support\Facades\DB;

class TaskExecutor
{
    /**
     * Cipta tugas baru dalam status Draft
     */
    public function createTask(int $tenantId, int $userId, array $data): PersonalTask
    {
        $riskLevel = $data['risk_level'] ?? PersonalTask::RISK_LOW;

        $task = PersonalTask::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'agent_id' => $data['agent_id'] ?? null,
            'assistant_type' => $data['assistant_type'] ?? 'owner',
            'objective' => $data['objective'],
            'input_reference' => $data['input_reference'] ?? null,
            'input_data' => $data['input_data'] ?? [],
            'allowed_tools' => $data['allowed_tools'] ?? [],
            'risk_level' => $riskLevel,
            'approval_required' => $data['approval_required'] ?? in_array($riskLevel, [
                PersonalTask::RISK_HIGH,
                PersonalTask::RISK_CRITICAL,
            ]),
            'budget_limit' => $data['budget_limit'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'status' => PersonalTask::STATUS_DRAFT,
        ]);

        return $task;
    }

    /**
     * Rancang tugas — Draft → Planned
     */
    public function plan(PersonalTask $task): PersonalTask
    {
        $this->transition($task, PersonalTask::STATUS_PLANNED);
        return $task;
    }

    /**
     * Hantar untuk kelulusan — Planned → Awaiting Approval
     */
    public function requestApproval(PersonalTask $task, ?int $approvalId = null): PersonalTask
    {
        $this->transition($task, PersonalTask::STATUS_AWAITING_APPROVAL);
        $task->approval_id = $approvalId;
        $task->save();

        return $task;
    }

    /**
     * Masukkan ke queue — Planned/Awaiting Approval → Queued
     */
    public function queue(PersonalTask $task): PersonalTask
    {
        // Jangan queue tugas berisiko tinggi tanpa kelulusan
        if ($task->requiresApproval() && $task->status !== PersonalTask::STATUS_AWAITING_APPROVAL) {
            throw new \RuntimeException('Tugas ini memerlukan kelulusan sebelum boleh di-queue.');
        }

        $this->transition($task, PersonalTask::STATUS_QUEUED);
        return $task;
    }

    /**
     * Mula jalankan — Queued → Running
     */
    public function start(PersonalTask $task): PersonalTask
    {
        $this->transition($task, PersonalTask::STATUS_RUNNING);
        return $task;
    }

    /**
     * Selesaikan tugas — Running → Completed / Partially Completed / Failed
     */
    public function complete(PersonalTask $task, array $result, string $status = PersonalTask::STATUS_COMPLETED): PersonalTask
    {
        if (!in_array($status, [
            PersonalTask::STATUS_COMPLETED,
            PersonalTask::STATUS_PARTIALLY_COMPLETED,
            PersonalTask::STATUS_FAILED,
        ], true)) {
            throw new \InvalidArgumentException("Status penyelesaian tidak sah: {$status}");
        }

        $this->transition($task, $status);

        $task->result_data = $result;
        $task->result_reference = $result['reference'] ?? null;
        $task->completed_at = now();

        if ($status === PersonalTask::STATUS_FAILED) {
            $task->error_details = $result['error'] ?? 'Tugas gagal tanpa butiran.';
        }

        $task->save();

        return $task;
    }

    /**
     * Batalkan tugas
     */
    public function cancel(PersonalTask $task, ?string $reason = null): PersonalTask
    {
        $this->transition($task, PersonalTask::STATUS_CANCELLED);
        $task->completed_at = now();
        if ($reason) {
            $task->error_details = "Dibatalkan: {$reason}";
        }
        $task->save();

        return $task;
    }

    /**
     * Peralihan status dengan validasi state machine
     */
    protected function transition(PersonalTask $task, string $newStatus): void
    {
        if (!$task->canTransitionTo($newStatus)) {
            throw new \RuntimeException(
                "Peralihan tidak sah: {$task->status} → {$newStatus}"
            );
        }

        $task->status = $newStatus;
        $task->save();
    }

    /**
     * Statistik tugas untuk pengguna
     */
    public function getStats(int $tenantId, int $userId): array
    {
        $base = PersonalTask::forTenant($tenantId)->forUser($userId);

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->active()->count(),
            'awaiting_approval' => (clone $base)->awaitingApproval()->count(),
            'completed' => (clone $base)->byStatus(PersonalTask::STATUS_COMPLETED)->count(),
            'failed' => (clone $base)->byStatus(PersonalTask::STATUS_FAILED)->count(),
            'by_status' => (clone $base)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'by_risk' => (clone $base)
                ->select('risk_level', DB::raw('count(*) as count'))
                ->groupBy('risk_level')
                ->pluck('count', 'risk_level')
                ->toArray(),
        ];
    }
}
