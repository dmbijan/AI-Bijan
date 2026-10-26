<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Marketing\Services\SegmentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SegmentController extends Controller
{
    protected SegmentService $segmentService;

    public function __construct(SegmentService $segmentService)
    {
        $this->segmentService = $segmentService;
    }

    /**
     * List all segments.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $segments = $this->segmentService->getSegments($tenantId);

        return response()->json([
            'success' => true,
            'data' => $segments,
        ]);
    }

    /**
     * Get a single segment.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $segment = $this->segmentService->getSegmentById($id, $tenantId);

        if (!$segment) {
            return response()->json([
                'success' => false,
                'message' => 'Segment not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $segment,
        ]);
    }

    /**
     * Create a new segment.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:static,dynamic',
            'description' => 'nullable|string',
            'criteria' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $data = $request->all();
        $data['tenant_id'] = $tenantId;

        $segment = $this->segmentService->createSegment($data);

        return response()->json([
            'success' => true,
            'data' => $segment,
            'message' => 'Segment created successfully.',
        ], 201);
    }

    /**
     * Update a segment.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $segment = $this->segmentService->getSegmentById($id, $tenantId);

        if (!$segment) {
            return response()->json([
                'success' => false,
                'message' => 'Segment not found.',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:static,dynamic',
            'description' => 'nullable|string',
            'criteria' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $segment = $this->segmentService->updateSegment($segment, $request->all());

        return response()->json([
            'success' => true,
            'data' => $segment,
            'message' => 'Segment updated successfully.',
        ]);
    }

    /**
     * Delete a segment.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $segment = $this->segmentService->getSegmentById($id, $tenantId);

        if (!$segment) {
            return response()->json([
                'success' => false,
                'message' => 'Segment not found.',
            ], 404);
        }

        $this->segmentService->deleteSegment($segment);

        return response()->json([
            'success' => true,
            'message' => 'Segment deleted successfully.',
        ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $segment = $this->segmentService->getSegmentById($id, $tenantId);

        if (!$segment) {
            return response()->json([
                'success' => false,
                'message' => 'Segment not found.',
            ], 404);
        }

        $segment = $this->segmentService->toggleActive($segment);

        return response()->json([
            'success' => true,
            'data' => $segment,
            'message' => 'Segment status updated.',
        ]);
    }

    /**
     * Add contact to segment.
     */
    public function addContact(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'contact_id' => 'required|integer',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $success = $this->segmentService->addContactToSegment(
            $request->contact_id,
            $id,
            $tenantId
        );

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Contact not found or failed to add.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Contact added to segment.',
        ]);
    }

    /**
     * Remove contact from segment.
     */
    public function removeContact(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'contact_id' => 'required|integer',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $success = $this->segmentService->removeContactFromSegment(
            $request->contact_id,
            $tenantId
        );

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove contact from segment.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Contact removed from segment.',
        ]);
    }

    /**
     * Calculate segment count (for dynamic segments).
     */
    public function calculateCount(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $segment = $this->segmentService->getSegmentById($id, $tenantId);

        if (!$segment) {
            return response()->json([
                'success' => false,
                'message' => 'Segment not found.',
            ], 404);
        }

        $criteria = $segment->criteria ?? [];
        $count = $this->segmentService->calculateSegmentCount($tenantId, $criteria);

        return response()->json([
            'success' => true,
            'data' => ['count' => $count],
        ]);
    }
}
