<?php

namespace App\Modules\Logistics\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use App\Http\Controllers\Controller;
use App\Modules\Logistics\Services\WarehouseService;
use App\Modules\Logistics\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    protected $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    /**
     * List all warehouses
     */
    public function index(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();

        $warehouses = $this->warehouseService->getWarehouses($tenantId, [
            'type' => $request->query('type'),
            'active' => $request->query('active'),
            'search' => $request->query('search'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $warehouses,
        ]);
    }

    /**
     * Get warehouse detail
     */
    public function show(int $id, Request $request)
    {
        $warehouse = $this->warehouseService->getWarehouse($id);

        if (!$warehouse || $warehouse->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json(['message' => 'Warehouse not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $warehouse,
        ]);
    }

    /**
     * Create new warehouse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:logistics_warehouses,code',
            'type' => 'required|in:main,warehouse,store,distribution_center,popup',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:30',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'latitude' => 'nullable|numeric|-90.0,90.0',
            'longitude' => 'nullable|numeric|-180.0,180.0',
            'settings' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = app(TenantContext::class)->id();

        $warehouse = $this->warehouseService->create($validated);

        return response()->json([
            'success' => true,
            'data' => $warehouse,
            'message' => 'Warehouse created successfully',
        ], 201);
    }

    /**
     * Update warehouse
     */
    public function update(Request $request, int $id)
    {
        $warehouse = $this->warehouseService->getWarehouse($id);

        if (!$warehouse || $warehouse->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json(['message' => 'Warehouse not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:logistics_warehouses,code,' . $id,
            'type' => 'sometimes|in:main,warehouse,store,distribution_center,popup',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:30',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
            'latitude' => 'nullable|numeric|-90.0,90.0',
            'longitude' => 'nullable|numeric|-180.0,180.0',
            'settings' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $this->warehouseService->update($id, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Warehouse updated successfully',
        ]);
    }

    /**
     * Delete warehouse
     */
    public function destroy(int $id, Request $request)
    {
        $warehouse = $this->warehouseService->getWarehouse($id);

        if (!$warehouse || $warehouse->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json(['message' => 'Warehouse not found'], 404);
        }

        try {
            $this->warehouseService->delete($id);
            return response()->json([
                'success' => true,
                'message' => 'Warehouse deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get primary/default warehouse
     */
    public function getPrimary(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();
        $warehouse = $this->warehouseService->getPrimaryWarehouse($tenantId);

        return response()->json([
            'success' => true,
            'data' => $warehouse,
        ]);
    }

    /**
     * Get warehouse statistics
     */
    public function getStats(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();
        $stats = $this->warehouseService->getStatistics($tenantId);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
