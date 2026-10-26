<?php

namespace App\Modules\Marketing\Services;

use App\Modules\Marketing\Models\Workflow;
use App\Modules\Marketing\Models\WorkflowExecution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkflowService
{
    /**
     * Get all workflows for a tenant.
     */
    public function getWorkflows(int $tenantId, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Workflow::where('tenant_id', $tenantId);

        if (isset($filters['active_only'])) {
            $query->active();
        }

        if (isset($filters['trigger_type'])) {
            $query->byTrigger($filters['trigger_type']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get a workflow by ID.
     */
    public function getWorkflowById(int $workflowId, int $tenantId): ?Workflow
    {
        return Workflow::where('id', $workflowId)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Get a workflow by slug.
     */
    public function getWorkflowBySlug(string $slug, int $tenantId): ?Workflow
    {
        return Workflow::where('slug', $slug)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Create a new workflow.
     */
    public function createWorkflow(array $data): Workflow
    {
        if (empty($data['slug'])) {
            $data['slug'] = Workflow::generateSlug($data['name']);
        }

        return DB::transaction(function () use ($data) {
            return Workflow::create($data);
        });
    }

    /**
     * Update a workflow.
     */
    public function updateWorkflow(Workflow $workflow, array $data): Workflow
    {
        return DB::transaction(function () use ($workflow, $data) {
            $workflow->update($data);
            return $workflow->fresh();
        });
    }

    /**
     * Delete a workflow.
     */
    public function deleteWorkflow(Workflow $workflow): bool
    {
        return $workflow->delete();
    }

    /**
     * Toggle workflow active status.
     */
    public function toggleActive(Workflow $workflow): Workflow
    {
        return $workflow->update(['is_active' => !$workflow->is_active]) ? $workflow->fresh() : null;
    }

    /**
     * Trigger workflow execution.
     */
    public function triggerWorkflow(Workflow $workflow, int $contactId, array $context = []): ?WorkflowExecution
    {
        // Check if workflow is active
        if (!$workflow->is_active) {
            return null;
        }

        // Check if contact already has running execution for this workflow
        $existing = WorkflowExecution::where('workflow_id', $workflow->id)
            ->where('contact_id', $contactId)
            ->whereIn('status', ['running', 'pending', 'waiting'])
            ->first();

        if ($existing) {
            return null;
        }

        return DB::transaction(function () use ($workflow, $contactId, $context) {
            return WorkflowExecution::create([
                'tenant_id' => $workflow->tenant_id,
                'workflow_id' => $workflow->id,
                'contact_id' => $contactId,
                'current_step' => 0,
                'status' => 'running',
                'started_at' => now(),
                'context_data' => $context,
            ]);
        });
    }

    /**
     * Execute workflow steps.
     */
    public function executeWorkflow(WorkflowExecution $execution): array
    {
        $workflow = $execution->workflow;
        $steps = $workflow->steps ?? [];
        $success = true;

        foreach ($steps as $index => $step) {
            try {
                $result = $this->executeStep($step, $execution);
                
                if (!$result) {
                    $success = false;
                    $execution->update([
                        'status' => 'failed',
                        'error_message' => "Step {$index} failed.",
                    ]);
                    break;
                }

                $execution->update(['current_step' => $index + 1]);

            } catch (\Exception $e) {
                $success = false;
                $execution->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
                break;
            }
        }

        // Update workflow stats
        $workflow->recordExecution($success);

        // Mark execution complete
        $execution->update([
            'status' => $success ? 'completed' : 'failed',
            'completed_at' => now(),
        ]);

        return [
            'success' => $success,
            'steps_executed' => $execution->current_step,
            'total_steps' => count($steps),
        ];
    }

    /**
     * Execute a single workflow step.
     */
    protected function executeStep(array $step, WorkflowExecution $execution): bool
    {
        $type = $step['type'] ?? '';

        switch ($type) {
            case 'send_email':
                return $this->executeSendEmail($step, $execution);
            case 'send_sms':
                return $this->executeSendSms($step, $execution);
            case 'send_whatsapp':
                return $this->executeSendWhatsapp($step, $execution);
            case 'delay':
                return true; // Delay handled by queue
            case 'update_contact':
                return $this->executeUpdateContact($step, $execution);
            case 'create_deal':
                return $this->executeCreateDeal($step, $execution);
            case 'add_tag':
                return $this->executeAddTag($step, $execution);
            default:
                return true; // Unknown step types are skipped
        }
    }

    /**
     * Execute send email step.
     *
     * Guna Blast Engine (BAB 82 Fasa 2) — bukan placeholder.
     */
    protected function executeSendEmail(array $step, WorkflowExecution $execution): bool
    {
        $recipient = $step['properties']['to'] ?? $step['properties']['email'] ?? null;
        $body = $step['properties']['body'] ?? $step['properties']['message'] ?? '';
        $subject = $step['properties']['subject'] ?? '';

        if (! $recipient || ! $body) {
            return false;
        }

        $provider = app(\App\Modules\Blast\Services\Providers\EmailProvider::class);

        if (! $provider->tersedia()) {
            Log::warning('Workflow: provider e-mel tidak dikonfigurasi', ['step' => $step]);

            return false;
        }

        $hasil = $provider->hantar($recipient, $body, [
            'subject' => $subject,
            'from' => config('blast.from_address'),
            'from_name' => config('blast.from_name'),
            'reply_to' => config('blast.reply_to'),
        ]);

        if (! $hasil['ok']) {
            Log::error('Workflow: hantar e-mel gagal', [
                'to' => $recipient,
                'error' => $hasil['error'],
            ]);

            return false;
        }

        return true;
    }

    /**
     * Execute send SMS step.
     *
     * Guna Blast Engine (BAB 82 Fasa 2) — bukan placeholder.
     */
    protected function executeSendSms(array $step, WorkflowExecution $execution): bool
    {
        $recipient = $step['properties']['to'] ?? $step['properties']['phone'] ?? null;
        $body = $step['properties']['body'] ?? $step['properties']['message'] ?? '';

        if (! $recipient || ! $body) {
            return false;
        }

        $provider = app(\App\Modules\Blast\Services\Providers\SmsProvider::class);

        if (! $provider->tersedia()) {
            Log::warning('Workflow: provider SMS tidak dikonfigurasi', ['step' => $step]);

            return false;
        }

        $hasil = $provider->hantar($recipient, $body, [
            'from' => $step['properties']['from'] ?? null,
        ]);

        if (! $hasil['ok']) {
            Log::error('Workflow: hantar SMS gagal', [
                'to' => $recipient,
                'error' => $hasil['error'],
            ]);

            return false;
        }

        return true;
    }

    /**
     * Execute send WhatsApp step.
     *
     * Guna Blast Engine (BAB 82 Fasa 2) — bukan placeholder.
     */
    protected function executeSendWhatsapp(array $step, WorkflowExecution $execution): bool
    {
        $recipient = $step['properties']['to'] ?? $step['properties']['whatsapp'] ?? $step['properties']['phone'] ?? null;
        $body = $step['properties']['body'] ?? $step['properties']['message'] ?? '';

        if (! $recipient || ! $body) {
            return false;
        }

        $provider = app(\App\Modules\Blast\Services\Providers\WhatsAppProvider::class);

        if (! $provider->tersedia()) {
            Log::warning('Workflow: provider WhatsApp tidak dikonfigurasi', ['step' => $step]);

            return false;
        }

        $hasil = $provider->hantar($recipient, $body, [
            'template' => $step['properties']['template'] ?? null,
            'parameters' => $step['properties']['parameters'] ?? null,
        ]);

        if (! $hasil['ok']) {
            Log::error('Workflow: hantar WhatsApp gagal', [
                'to' => $recipient,
                'error' => $hasil['error'],
            ]);

            return false;
        }

        return true;
    }

    /**
     * Execute update contact step.
     */
    protected function executeUpdateContact(array $step, WorkflowExecution $execution): bool
    {
        $contactService = app(ContactService::class);
        $contact = Contact::find($execution->contact_id);

        if (!$contact) {
            return false;
        }

        if (isset($step['properties'])) {
            $contactService->updateContact($contact, ['properties' => $step['properties']]);
        }

        return true;
    }

    /**
     * Execute create deal step.
     */
    protected function executeCreateDeal(array $step, WorkflowExecution $execution): bool
    {
        $dealService = app(DealService::class);
        
        $dealData = array_merge([
            'tenant_id' => $execution->tenant_id,
            'contact_id' => $execution->contact_id,
            'title' => $step['title'] ?? 'Auto-created deal',
            'stage' => $step['stage'] ?? 'prospect',
        ], $step['properties'] ?? []);

        $dealService->createDeal($dealData);
        return true;
    }

    /**
     * Execute add tag step.
     */
    protected function executeAddTag(array $step, WorkflowExecution $execution): bool
    {
        $contactService = app(ContactService::class);
        $contact = Contact::find($execution->contact_id);

        if (!$contact) {
            return false;
        }

        if (isset($step['tags']) && is_array($step['tags'])) {
            $contactService->addTags($contact, $step['tags']);
        }

        return true;
    }

    /**
     * Get workflow statistics.
     */
    public function getWorkflowStats(int $tenantId): array
    {
        $totalWorkflows = Workflow::where('tenant_id', $tenantId)->count();
        $activeWorkflows = Workflow::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();
        $totalExecutions = WorkflowExecution::whereHas('workflow', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->count();
        $successfulExecutions = WorkflowExecution::whereHas('workflow', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })->where('status', 'completed')->count();

        return [
            'total_workflows' => $totalWorkflows,
            'active_workflows' => $activeWorkflows,
            'total_executions' => $totalExecutions,
            'successful_executions' => $successfulExecutions,
        ];
    }
}
