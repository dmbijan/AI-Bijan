<?php

namespace App\Modules\Assets\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Assets\Services\MaintenanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MaintenanceController extends Controller
{
    protected MaintenanceService $maintenanceService;

    public function __construct(MaintenanceService $maintenanceService)
    {
        $this->maintenanceService = $maintenanceService;
    }

    /**
     * List all maintenance requests.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'type', 'priority', 'asset_id', 'assigned_to', 'search']);

        return response()->json($this->maintenanceService->listRequests(
            $request->tenant_id,
            $filters
        ));
    }

    /**
     * Show a single maintenance request.
     */
    public function show(Request $request, int $id)
    {
        $request = $this->maintenanceService->getRequest($id, $request->tenant_id);

        if (!$request) {
            return response()->json(['message' => 'Maintenance request not found.'], 404);
        }

        return response()->json($request);
    }

    /**
     * Create a new maintenance request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'type' => 'required|in:preventive,corrective,predictive,emergency',
            'priority' => 'required|in:low,medium,high,critical',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'scheduled_date' => 'nullable|date',
            'estimated_cost' => 'nullable|numeric|min:0',
            'vendor_id' => 'nullable|exists:suppliers,id',
        ]);

        try {
            $maintenanceRequest = $this->maintenanceService->createRequest(
                $validated,
                $request->tenant_id,
                $request->user()->id
            );

            return response()->json($maintenanceRequest, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update a maintenance request.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'type' => 'sometimes|in:preventive,corrective,predictive,emergency',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled',
            'scheduled_date' => 'nullable|date',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
            'downtime_hours' => 'nullable|numeric|min:0',
            'resolution' => 'nullable|string',
            'vendor_id' => 'nullable|exists:suppliers,id',
        ]);

        $result = $this->maintenanceService->updateRequest($id, $validated, $request->tenant_id);

        if (!$result) {
            return response()->json(['message' => 'Maintenance request not found.'], 404);
        }

        return response()->json($result);
    }

    /**
     * Delete a maintenance request.
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $success = $this->maintenanceService->deleteRequest($id, $request->tenant_id);

            if (!$success) {
                return response()->json(['message' => 'Maintenance request not found.'], 404);
            }

            return response()->json(['message' => 'Maintenance request deleted successfully.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Add parts/materials to a maintenance request.
     */
    public function addPart(Request $request, int $id)
    {
        $validated = $request->validate([
            'part_name' => 'required|string|max:255',
            'part_number' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            $part = $this->maintenanceService->addPart($id, $validated, $request->tenant_id);
            return response()->json($part, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove a part from a maintenance request.
     */
    public function removePart(Request $request, int $partId)
    {
        $success = $this->maintenanceService->removePart($partId, $request->tenant_id);

        if (!$success) {
            return response()->json(['message' => 'Part not found.'], 404);
        }

        return response()->json(['message' => 'Part removed successfully.']);
    }

    /**
     * Complete a maintenance request.
     */
    public function complete(Request $request, int $id)
    {
        $validated = $request->validate([
            'resolution' => 'nullable|string',
            'actual_cost' => 'nullable|numeric|min:0',
            'downtime_hours' => 'nullable|numeric|min:0',
        ]);

        $result = $this->maintenanceService->completeRequest($id, $validated, $request->tenant_id);

        if (!$result) {
            return response()->json(['message' => 'Maintenance request not found.'], 404);
        }

        return response()->json($result);
    }

    /**
     * Cancel a maintenance request.
     */
    public function cancel(Request $request, int $id)
    {
        $result = $this->maintenanceService->cancelRequest($id, $request->tenant_id);

        if (!$result) {
            return response()->json(['message' => 'Maintenance request not found.'], 404);
        }

        return response()->json($result);
    }

    /**
     * Assign a technician to a maintenance request.
     */
    public function assignTechnician(Request $request, int $id)
    {
        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
        ]);

        $result = $this->maintenanceService->assignTechnician(
            $id,
            $validated['technician_id'],
            $request->tenant_id
        );

        if (!$result) {
            return response()->json(['message' => 'Maintenance request not found.'], 404);
        }

        return response()->json($result);
    }

    /**
     * Get maintenance summary statistics.
     */
    public function summary(Request $request)
    {
        return response()->json($this->maintenanceService->getSummary($request->tenant_id));
    }

    /**
     * Get upcoming preventive maintenance schedule.
     */
    public function upcoming(Request $request)
    {
        $daysAhead = $request->input('days_ahead', 30);

        return response()->json($this->maintenanceService->getUpcomingPreventive(
            $request->tenant_id,
            $daysAhead
        ));
    }

    /**
     * Get maintenance history for an asset.
     */
    public function assetHistory(Request $request, int $assetId)
    {
        return response()->json($this->maintenanceService->getAssetHistory(
            $assetId,
            $request->tenant_id
        ));
    }
}
