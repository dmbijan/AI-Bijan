<?php

namespace App\Modules\Assets\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Assets\Services\AssetService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AssetController extends Controller
{
    protected AssetService $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * List all assets for the tenant.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'category_id', 'search', 'location']);

        return response()->json($this->assetService->listAssets(
            $request->tenant_id,
            $filters
        ));
    }

    /**
     * Show a single asset with full details.
     */
    public function show(Request $request, int $id)
    {
        $asset = $this->assetService->getAsset($id, $request->tenant_id);

        if (!$asset) {
            return response()->json(['message' => 'Asset not found.'], 404);
        }

        return response()->json($asset);
    }

    /**
     * Create a new asset.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'serial_number' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255|unique:assets,barcode',
            'category_id' => 'nullable|exists:asset_categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'vendor_id' => 'nullable|exists:crm_clients,id',
            'assigned_to' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'warranty_provider' => 'nullable|string|max:255',
            'warranty_start' => 'nullable|date',
            'warranty_end' => 'nullable|date|after_or_equal:warranty_start',
            'salvage_value' => 'nullable|numeric|min:0',
            'useful_life_years' => 'nullable|integer|min:1|max:50',
            'depreciation_method' => 'nullable|in:straight_line,declining_balance,units_of_production,sum_years_digits',
        ]);

        try {
            $asset = $this->assetService->createAsset($validated, $request->tenant_id, $request->user()->id);
            return response()->json($asset, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update an existing asset.
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'serial_number' => 'nullable|string|max:255',
            'barcode' => 'nullable|string|max:255|unique:assets,barcode,' . $id,
            'category_id' => 'nullable|exists:asset_categories,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'vendor_id' => 'nullable|exists:crm_clients,id',
            'assigned_to' => 'nullable|exists:users,id',
            'location' => 'nullable|string|max:255',
            'warranty_provider' => 'nullable|string|max:255',
            'warranty_start' => 'nullable|date',
            'warranty_end' => 'nullable|date|after_or_equal:warranty_start',
            'salvage_value' => 'nullable|numeric|min:0',
            'useful_life_years' => 'nullable|integer|min:1|max:50',
            'depreciation_method' => 'nullable|in:straight_line,declining_balance,units_of_production,sum_years_digits',
            'status' => 'nullable|in:purchased,installed,active,maintenance,stored,disposed,lost,damaged',
        ]);

        try {
            $asset = $this->assetService->updateAsset($id, $validated, $request->tenant_id);

            if (!$asset) {
                return response()->json(['message' => 'Asset not found.'], 404);
            }

            return response()->json($asset);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Delete an asset.
     */
    public function destroy(Request $request, int $id)
    {
        try {
            $success = $this->assetService->deleteAsset($id, $request->tenant_id);

            if (!$success) {
                return response()->json(['message' => 'Asset not found.'], 404);
            }

            return response()->json(['message' => 'Asset deleted successfully.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Assign an asset to a user.
     */
    public function assign(Request $request, int $id)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'location' => 'required|string|max:255',
        ]);

        try {
            $transfer = $this->assetService->assignAsset(
                $id,
                $validated['user_id'],
                $validated['location'],
                $request->tenant_id,
                $request->user()->id
            );

            return response()->json($transfer, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Unassign an asset from user.
     */
    public function unassign(Request $request, int $id)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
        ]);

        try {
            $transfer = $this->assetService->unassignAsset(
                $id,
                $validated['location'],
                $request->tenant_id,
                $request->user()->id
            );

            return response()->json($transfer, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Upload a document for an asset.
     */
    public function uploadDocument(Request $request, int $id)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx|max:10240',
            'type' => 'nullable|in:photo,manual,certificate,warranty,insurance,other',
        ]);

        try {
            $document = $this->assetService->uploadDocument(
                $id,
                $validated,
                $request->tenant_id,
                $request->user()->id
            );

            return response()->json($document, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get asset summary statistics.
     */
    public function summary(Request $request)
    {
        return response()->json($this->assetService->getSummary($request->tenant_id));
    }

    /**
     * Search asset by barcode or serial number.
     */
    public function search(Request $request)
    {
        $identifier = $request->input('identifier');

        if (!$identifier) {
            return response()->json(['message' => 'Identifier required.'], 400);
        }

        $asset = $this->assetService->searchByIdentifier($identifier);

        if (!$asset) {
            return response()->json(['message' => 'Asset not found.'], 404);
        }

        return response()->json($asset);
    }
}
