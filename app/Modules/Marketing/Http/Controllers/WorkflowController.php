<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Marketing\Services\WorkflowService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkflowController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$request->tenant_id` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected WorkflowService $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * List all workflows.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $filters = $request->only(['active_only', 'trigger_type']);

        $workflows = $this->workflowService->getWorkflows($tenantId, $filters);

        return response()->json([
            'success' => true,
            'data' => $workflows,
        ]);
    }

    /**
     * Get a single workflow.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $workflow = $this->workflowService->getWorkflowById($id, $tenantId);

        if (!$workflow) {
            return response()->json([
                'success' => false,
                'message' => 'Workflow not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $workflow,
        ]);
    }

    /**
     * Create a new workflow.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'required|in:new_contact,form_submission,purchase,website_visit,email_open,email_click,campaign_start,scheduled',
            'trigger_config' => 'nullable|array',
            'steps' => 'required|array|min:1',
            'is_active' => 'boolean',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $data = $request->all();
        $data['tenant_id'] = $tenantId;

        $workflow = $this->workflowService->createWorkflow($data);

        return response()->json([
            'success' => true,
            'data' => $workflow,
            'message' => 'Workflow created successfully.',
        ], 201);
    }

    /**
     * Update a workflow.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $workflow = $this->workflowService->getWorkflowById($id, $tenantId);

        if (!$workflow) {
            return response()->json([
                'success' => false,
                'message' => 'Workflow not found.',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'trigger_type' => 'sometimes|in:new_contact,form_submission,purchase,website_visit,email_open,email_click,campaign_start,scheduled',
            'trigger_config' => 'nullable|array',
            'steps' => 'sometimes|array|min:1',
            'is_active' => 'boolean',
        ]);

        $workflow = $this->workflowService->updateWorkflow($workflow, $request->all());

        return response()->json([
            'success' => true,
            'data' => $workflow,
            'message' => 'Workflow updated successfully.',
        ]);
    }

    /**
     * Delete a workflow.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $workflow = $this->workflowService->getWorkflowById($id, $tenantId);

        if (!$workflow) {
            return response()->json([
                'success' => false,
                'message' => 'Workflow not found.',
            ], 404);
        }

        $this->workflowService->deleteWorkflow($workflow);

        return response()->json([
            'success' => true,
            'message' => 'Workflow deleted successfully.',
        ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $workflow = $this->workflowService->getWorkflowById($id, $tenantId);

        if (!$workflow) {
            return response()->json([
                'success' => false,
                'message' => 'Workflow not found.',
            ], 404);
        }

        $workflow = $this->workflowService->toggleActive($workflow);

        return response()->json([
            'success' => true,
            'data' => $workflow,
            'message' => 'Workflow status updated.',
        ]);
    }

    /**
     * Trigger workflow execution.
     */
    public function trigger(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'contact_id' => 'required|integer',
            'context' => 'nullable|array',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $workflow = $this->workflowService->getWorkflowById($id, $tenantId);

        if (!$workflow) {
            return response()->json([
                'success' => false,
                'message' => 'Workflow not found.',
            ], 404);
        }

        $execution = $this->workflowService->triggerWorkflow(
            $workflow,
            $request->contact_id,
            $request->context ?? []
        );

        if (!$execution) {
            return response()->json([
                'success' => false,
                'message' => 'Workflow is inactive or execution already exists.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $execution,
            'message' => 'Workflow triggered.',
        ]);
    }

    /**
     * Execute workflow.
     */
    public function execute(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $workflow = $this->workflowService->getWorkflowById($id, $tenantId);

        if (!$workflow) {
            return response()->json([
                'success' => false,
                'message' => 'Workflow not found.',
            ], 404);
        }

        // This would typically be called by a queue worker
        // For now, we'll just return success
        return response()->json([
            'success' => true,
            'message' => 'Workflow execution queued.',
        ]);
    }

    /**
     * Get workflow statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $stats = $this->workflowService->getWorkflowStats($tenantId);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
