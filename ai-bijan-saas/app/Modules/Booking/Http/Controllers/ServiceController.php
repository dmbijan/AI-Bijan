<?php

namespace App\Modules\Booking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Services\ServiceCatalogService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    protected ServiceCatalogService $serviceCatalogService;

    public function __construct(ServiceCatalogService $serviceCatalogService)
    {
        $this->serviceCatalogService = $serviceCatalogService;
    }

    /**
     * List all services.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'active', 'provider_id']);

        return response()->json($this->serviceCatalogService->listServices($request->tenant_id, $filters));
    }

    /**
     * Show a single service.
     */
    public function show(Request $request, int $id)
    {
        $service = $this->serviceCatalogService->getService($id, $request->tenant_id);

        if (!$service) {
            return response()->json(['message' => 'Service not found.'], 404);
        }

        return response()->json($service);
    }

    /**
     * Create a new service.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:5|max:480',
            'price' => 'nullable|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'provider_id' => 'nullable|exists:booking_service_providers,id',
            'is_active' => 'boolean',
            'max_capacity' => 'nullable|integer|min:1',
            'requires_prep' => 'boolean',
            'prep_time_minutes' => 'nullable|integer|min:0',
            'cancellation_policy' => 'nullable|string',
        ]);

        try {
            $service = $this->serviceCatalogService->createService(
                $validated,
                $request->tenant_id,
                $request->user()->id
            );

            return response()->json($service, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update a service.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'duration_minutes' => 'sometimes|integer|min:5|max:480',
            'price' => 'sometimes|numeric|min:0',
            'cost' => 'sometimes|numeric|min:0',
            'provider_id' => 'sometimes|exists:booking_service_providers,id',
            'is_active' => 'boolean',
            'max_capacity' => 'nullable|integer|min:1',
            'requires_prep' => 'boolean',
            'prep_time_minutes' => 'nullable|integer|min:0',
            'cancellation_policy' => 'nullable|string',
        ]);

        $result = $this->serviceCatalogService->updateService($id, $validated, $request->tenant_id);

        if (!$result) {
            return response()->json(['message' => 'Service not found.'], 404);
        }

        return response()->json($result);
    }

    /**
     * Delete a service.
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $success = $this->serviceCatalogService->deleteService($id, $request->tenant_id);

            if (!$success) {
                return response()->json(['message' => 'Service not found.'], 404);
            }

            return response()->json(['message' => 'Service deleted successfully.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Toggle service active status.
     */
    public function toggleActive(Request $request, int $id)
    {
        $result = $this->serviceCatalogService->toggleActive($id, $request->tenant_id);

        if (!$result) {
            return response()->json(['message' => 'Service not found.'], 404);
        }

        return response()->json($result);
    }
}
