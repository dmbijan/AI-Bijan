<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use App\Http\Controllers\Controller;
use App\Modules\Marketing\Services\SequenceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SequenceController extends Controller
{
    protected SequenceService $sequenceService;

    public function __construct(SequenceService $sequenceService)
    {
        $this->sequenceService = $sequenceService;
    }

    /**
     * List all sequences.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $filters = $request->only(['active_only', 'trigger', 'campaign_id']);

        $sequences = $this->sequenceService->getSequences($tenantId, $filters);

        return response()->json([
            'success' => true,
            'data' => $sequences,
        ]);
    }

    /**
     * Get a single sequence.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $sequence = $this->sequenceService->getSequenceById($id, $tenantId);

        if (!$sequence) {
            return response()->json([
                'success' => false,
                'message' => 'Sequence not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $sequence,
        ]);
    }

    /**
     * Create a new sequence.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trigger' => 'required|in:new_contact,form_submission,purchase,website_visit,email_open,email_click',
            'trigger_conditions' => 'nullable|array',
            'steps' => 'required|array|min:1',
            'is_active' => 'boolean',
            'stop_on_response' => 'boolean',
            'max_repetitions' => 'nullable|integer|min:1',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $data = $request->all();
        $data['tenant_id'] = $tenantId;

        $sequence = $this->sequenceService->createSequence($data);

        return response()->json([
            'success' => true,
            'data' => $sequence,
            'message' => 'Sequence created successfully.',
        ], 201);
    }

    /**
     * Update a sequence.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $sequence = $this->sequenceService->getSequenceById($id, $tenantId);

        if (!$sequence) {
            return response()->json([
                'success' => false,
                'message' => 'Sequence not found.',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'trigger' => 'sometimes|in:new_contact,form_submission,purchase,website_visit,email_open,email_click',
            'trigger_conditions' => 'nullable|array',
            'steps' => 'sometimes|array|min:1',
            'is_active' => 'boolean',
            'stop_on_response' => 'boolean',
            'max_repetitions' => 'nullable|integer|min:1',
        ]);

        $sequence = $this->sequenceService->updateSequence($sequence, $request->all());

        return response()->json([
            'success' => true,
            'data' => $sequence,
            'message' => 'Sequence updated successfully.',
        ]);
    }

    /**
     * Delete a sequence.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $sequence = $this->sequenceService->getSequenceById($id, $tenantId);

        if (!$sequence) {
            return response()->json([
                'success' => false,
                'message' => 'Sequence not found.',
            ], 404);
        }

        $this->sequenceService->deleteSequence($sequence);

        return response()->json([
            'success' => true,
            'message' => 'Sequence deleted successfully.',
        ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $sequence = $this->sequenceService->getSequenceById($id, $tenantId);

        if (!$sequence) {
            return response()->json([
                'success' => false,
                'message' => 'Sequence not found.',
            ], 404);
        }

        $sequence = $this->sequenceService->toggleActive($sequence);

        return response()->json([
            'success' => true,
            'data' => $sequence,
            'message' => 'Sequence status updated.',
        ]);
    }

    /**
     * Start execution for a contact.
     */
    public function startExecution(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'contact_id' => 'required|integer',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $sequence = $this->sequenceService->getSequenceById($id, $tenantId);

        if (!$sequence) {
            return response()->json([
                'success' => false,
                'message' => 'Sequence not found.',
            ], 404);
        }

        $execution = $this->sequenceService->startExecution($sequence, $request->contact_id);

        if (!$execution) {
            return response()->json([
                'success' => false,
                'message' => 'Execution failed or already exists.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $execution,
            'message' => 'Sequence execution started.',
        ]);
    }

    /**
     * Execute sequence for a contact.
     */
    public function execute(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'contact_id' => 'required|integer',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $sequence = $this->sequenceService->getSequenceById($id, $tenantId);

        if (!$sequence) {
            return response()->json([
                'success' => false,
                'message' => 'Sequence not found.',
            ], 404);
        }

        $result = $this->sequenceService->executeSequence($sequence, $request->contact_id);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get sequence statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $stats = $this->sequenceService->getSequenceStats($tenantId);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
