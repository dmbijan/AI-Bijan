<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use App\Http\Controllers\Controller;
use App\Modules\Marketplace\Services\VendorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VendorController extends Controller
{
    protected VendorService $vendorService;

    public function __construct(VendorService $vendorService)
    {
        $this->vendorService = $vendorService;
    }

    /**
     * List all vendors with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        $vendors = $this->vendorService->list(
            $tenantId,
            $request->only(['is_active', 'is_verified', 'search']),
            $request->get('per_page', 20)
        );

        return response()->json($vendors);
    }

    /**
     * Get a single vendor by ID.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $vendor = $this->vendorService->find($id);

        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        return response()->json($vendor);
    }

    /**
     * Create a new vendor.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
        ]);

        $vendor = $this->vendorService->create(array_merge($request->all(), [
            'tenant_id' => app(TenantContext::class)->id(),
            'user_id' => $request->user()->id,
        ]));

        return response()->json($vendor, 201);
    }

    /**
     * Update an existing vendor.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $vendor = $this->vendorService->find($id);

        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'contact_email' => 'sometimes|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $vendor = $this->vendorService->update($vendor, $request->all());

        return response()->json($vendor);
    }

    /**
     * Delete a vendor.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $vendor = $this->vendorService->find($id);

        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $this->vendorService->delete($vendor);

        return response()->json(['message' => 'Vendor deleted successfully']);
    }

    /**
     * Approve a vendor.
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $vendor = $this->vendorService->find($id);

        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $vendor = $this->vendorService->approve($vendor);

        return response()->json(['message' => 'Vendor approved successfully', 'vendor' => $vendor]);
    }

    /**
     * Reject a vendor.
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $vendor = $this->vendorService->find($id);

        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $vendor = $this->vendorService->reject($vendor);

        return response()->json(['message' => 'Vendor rejected', 'vendor' => $vendor]);
    }

    /**
     * Toggle vendor active status.
     */
    public function toggleActive(Request $request, int $id): JsonResponse
    {
        $vendor = $this->vendorService->find($id);

        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $vendor = $this->vendorService->toggleActive($vendor, !$vendor->is_active);

        return response()->json(['message' => 'Vendor ' . ($vendor->is_active ? 'activated' : 'deactivated'), 'vendor' => $vendor]);
    }

    /**
     * Get top vendors by sales.
     */
    public function topVendors(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        $vendors = $this->vendorService->getTopVendors(
            $tenantId,
            $request->get('limit', 10)
        );

        return response()->json($vendors);
    }
}
