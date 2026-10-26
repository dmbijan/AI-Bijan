<?php

namespace App\Modules\Assets\Services;

use App\Modules\Assets\Models\Asset;
use App\Modules\Assets\Models\AssetCategory;
use App\Modules\Assets\Models\AssetDocument;
use App\Modules\Assets\Models\AssetTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AssetService
{
    /**
     * Get all assets for a tenant.
     */
    public function listAssets(int $tenantId, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Asset::forTenant($tenantId)->with(['category', 'assignedUser', 'vendor']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['category_id'])) {
            $query->byCategory($filters['category_id']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('asset_no', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if (isset($filters['location'])) {
            $query->where('location', 'like', "%{$filters['location']}%");
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Get a single asset by ID.
     */
    public function getAsset(int $assetId, int $tenantId): ?Asset
    {
        return Asset::forTenant($tenantId)
            ->with([
                'category',
                'assignedUser',
                'vendor',
                'depreciationSchedules',
                'maintenanceRequests',
                'transfers',
                'documents',
                'disposals',
            ])
            ->find($assetId);
    }

    /**
     * Create a new asset.
     */
    public function createAsset(array $data, int $tenantId, int $userId): Asset
    {
        return DB::transaction(function () use ($data, $tenantId, $userId) {
            // Generate unique asset number
            $data['asset_no'] = Asset::generateAssetNo();
            $data['tenant_id'] = $tenantId;
            $data['created_by'] = $userId;

            // Set default values
            $data['total_depreciation'] = 0;
            $data['current_book_value'] = $data['purchase_price'];
            $data['salvage_value'] = $data['salvage_value'] ?? 0;
            $data['useful_life_years'] = $data['useful_life_years'] ?? 10;
            $data['depreciation_method'] = $data['depreciation_method'] ?? 'straight_line';

            $asset = Asset::create($data);

            // Generate initial depreciation schedule
            app(DepreciationService::class)->generateDepreciationSchedule($asset);

            return $asset;
        });
    }

    /**
     * Update an existing asset.
     */
    public function updateAsset(int $assetId, array $data, int $tenantId): ?Asset
    {
        $asset = $this->getAsset($assetId, $tenantId);

        if (!$asset) {
            return null;
        }

        // Prevent updating disposed assets
        if ($asset->status === 'disposed') {
            throw ValidationException::withMessages([
                'status' => ['Cannot update a disposed asset.'],
            ]);
        }

        $asset->update($data);

        // Recalculate book value if purchase price or useful life changed
        if (isset($data['purchase_price']) || isset($data['useful_life_years'])) {
            $asset->calculateBookValue();
            $asset->save();
        }

        return $asset;
    }

    /**
     * Delete an asset (only if not yet in use).
     */
    public function deleteAsset(int $assetId, int $tenantId): bool
    {
        $asset = $this->getAsset($assetId, $tenantId);

        if (!$asset) {
            return false;
        }

        // Cannot delete active or assigned assets
        if ($asset->isActive() && $asset->assigned_to) {
            throw ValidationException::withMessages([
                'base' => ['Cannot delete an asset that is currently assigned to a user.'],
            ]);
        }

        // Delete associated documents first
        $asset->documents()->delete();
        $asset->depreciationSchedules()->delete();
        $asset->maintenanceRequests()->each(fn($mr) => $mr->parts()->delete());
        $asset->maintenanceRequests()->delete();
        $asset->transfers()->delete();
        $asset->disposals()->delete();

        return $asset->delete();
    }

    /**
     * Assign an asset to a user.
     */
    public function assignAsset(int $assetId, int $userId, string $location, int $tenantId, int $requesterId): AssetTransfer
    {
        $asset = $this->getAsset($assetId, $tenantId);

        if (!$asset) {
            throw ValidationException::withMessages([
                'asset_id' => ['Asset not found.'],
            ]);
        }

        return DB::transaction(function () use ($asset, $userId, $location, $tenantId, $requesterId) {
            // Record transfer
            $transfer = AssetTransfer::create([
                'tenant_id' => $tenantId,
                'asset_id' => $asset->id,
                'from_user' => $asset->assigned_to,
                'to_user' => $userId,
                'from_location' => $asset->location,
                'to_location' => $location,
                'reason' => 'Asset assignment',
            ]);

            // Update asset
            $asset->update([
                'assigned_to' => $userId,
                'location' => $location,
                'status' => 'active',
            ]);

            return $transfer;
        });
    }

    /**
     * Unassign an asset from user.
     */
    public function unassignAsset(int $assetId, string $location, int $tenantId, int $requesterId): AssetTransfer
    {
        $asset = $this->getAsset($assetId, $tenantId);

        if (!$asset) {
            throw ValidationException::withMessages([
                'asset_id' => ['Asset not found.'],
            ]);
        }

        return DB::transaction(function () use ($asset, $location, $tenantId, $requesterId) {
            $transfer = AssetTransfer::create([
                'tenant_id' => $tenantId,
                'asset_id' => $asset->id,
                'from_user' => $asset->assigned_to,
                'to_user' => null,
                'from_location' => $asset->location,
                'to_location' => $location,
                'reason' => 'Asset unassignment',
            ]);

            $asset->update([
                'assigned_to' => null,
                'location' => $location,
                'status' => 'stored',
            ]);

            return $transfer;
        });
    }

    /**
     * Upload a document for an asset.
     */
    public function uploadDocument(int $assetId, array $data, int $tenantId, int $userId): AssetDocument
    {
        $asset = $this->getAsset($assetId, $tenantId);

        if (!$asset) {
            throw ValidationException::withMessages([
                'asset_id' => ['Asset not found.'],
            ]);
        }

        if (!isset($data['file']) || !$data['file']->isValid()) {
            throw ValidationException::withMessages([
                'file' => ['Please upload a valid file.'],
            ]);
        }

        $path = $data['file']->store('assets/' . $assetId, 'public');

        $document = AssetDocument::create([
            'tenant_id' => $tenantId,
            'asset_id' => $assetId,
            'uploaded_by' => $userId,
            'file_path' => $path,
            'file_name' => $data['file']->getClientOriginalName(),
            'mime_type' => $data['file']->getMimeType(),
            'file_size' => $data['file']->getSize(),
            'type' => $data['type'] ?? 'other',
        ]);

        return $document;
    }

    /**
     * Delete an asset document.
     */
    public function deleteDocument(int $documentId, int $tenantId): bool
    {
        $document = AssetDocument::forTenant($tenantId)->find($documentId);

        if (!$document) {
            return false;
        }

        Storage::disk('public')->delete($document->file_path);

        return $document->delete();
    }

    /**
     * Get asset summary statistics.
     */
    public function getSummary(int $tenantId): array
    {
        $assets = Asset::forTenant($tenantId);

        return [
            'total_assets' => $assets->count(),
            'active_assets' => $assets->cloneQuery()->active()->count(),
            'in_maintenance' => $assets->cloneQuery()->where('status', 'maintenance')->count(),
            'stored_assets' => $assets->cloneQuery()->where('status', 'stored')->count(),
            'disposed_assets' => $assets->cloneQuery()->where('status', 'disposed')->count(),
            'total_value' => $assets->sum('purchase_price'),
            'total_book_value' => $assets->sum('current_book_value') ?: $assets->sum('purchase_price'),
            'total_depreciation' => $assets->sum('total_depreciation'),
            'under_warranty' => $assets->whereNotNull('warranty_end')->where('warranty_end', '>=', now())->count(),
            'categories' => $assets->selectRaw('category_id, count(*) as count')->groupBy('category_id')->get()->pluck('count', 'category_id'),
        ];
    }

    /**
     * Search assets by barcode or serial number.
     */
    public function searchByIdentifier(string $identifier): ?Asset
    {
        return Asset::where(function ($q) use ($identifier) {
            $q->where('barcode', $identifier)
              ->orWhere('serial_number', $identifier)
              ->orWhere('asset_no', $identifier);
        })->first();
    }

    /**
     * Bulk import assets from CSV data.
     */
    public function bulkImportAssets(array $rows, int $tenantId, int $userId): array
    {
        $created = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                $this->createAsset([
                    'name' => $row['name'] ?? 'Unnamed Asset',
                    'description' => $row['description'] ?? null,
                    'serial_number' => $row['serial_number'] ?? null,
                    'barcode' => $row['barcode'] ?? null,
                    'category_id' => $row['category_id'] ?? null,
                    'purchase_price' => $row['purchase_price'] ?? 0,
                    'purchase_date' => $row['purchase_date'] ?? now()->format('Y-m-d'),
                    'vendor_id' => $row['vendor_id'] ?? null,
                    'location' => $row['location'] ?? null,
                    'warranty_provider' => $row['warranty_provider'] ?? null,
                    'warranty_start' => $row['warranty_start'] ?? null,
                    'warranty_end' => $row['warranty_end'] ?? null,
                    'useful_life_years' => $row['useful_life_years'] ?? 10,
                    'depreciation_method' => $row['depreciation_method'] ?? 'straight_line',
                ], $tenantId, $userId);

                $created++;
            } catch (\Exception $e) {
                $errors[$index] = $e->getMessage();
            }
        }

        return ['created' => $created, 'errors' => $errors];
    }
}
