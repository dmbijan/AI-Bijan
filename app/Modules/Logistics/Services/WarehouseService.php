<?php

namespace App\Modules\Logistics\Services;

use App\Modules\Logistics\Models\Warehouse;
use App\Modules\Logistics\Models\StockMove;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    /**
     * Create a new warehouse
     */
    public function create(array $data): Warehouse
    {
        // Ensure only one default warehouse
        if ($data['is_default'] ?? false) {
            Warehouse::where('tenant_id', $data['tenant_id'])
                ->update(['is_default' => false]);
        }

        return Warehouse::create($data);
    }

    /**
     * Get all warehouses for tenant
     */
    public function getWarehouses(int $tenantId, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Warehouse::where('tenant_id', $tenantId);

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%")
                  ->orWhere('city', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('is_default', 'desc')
                     ->orderBy('name')
                     ->get();
    }

    /**
     * Get warehouse by ID
     */
    public function getWarehouse(int $warehouseId): ?Warehouse
    {
        return Warehouse::find($warehouseId);
    }

    /**
     * Update warehouse
     */
    public function update(int $warehouseId, array $data): bool
    {
        $warehouse = Warehouse::findOrFail($warehouseId);

        // Ensure only one default warehouse
        if (($data['is_default'] ?? false) && !$warehouse->is_default) {
            Warehouse::where('tenant_id', $warehouse->tenant_id)
                ->where('id', '!=', $warehouseId)
                ->update(['is_default' => false]);
        }

        return $warehouse->update($data);
    }

    /**
     * Delete warehouse
     */
    public function delete(int $warehouseId): bool
    {
        $warehouse = Warehouse::findOrFail($warehouseId);

        // Prevent deletion if warehouse has stock moves
        if ($warehouse->stockMoves()->count() > 0) {
            throw new \RuntimeException('Cannot delete warehouse with existing stock movements.');
        }

        return $warehouse->delete();
    }

    /**
     * Get primary/default warehouse for tenant
     */
    public function getPrimaryWarehouse(int $tenantId): ?Warehouse
    {
        return Warehouse::where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Get warehouse statistics
     */
    public function getStatistics(int $tenantId): array
    {
        $warehouses = Warehouse::where('tenant_id', $tenantId)->get();

        $totalWarehouses = $warehouses->count();
        $activeWarehouses = $warehouses->where('is_active', true)->count();
        $totalStock = 0;

        foreach ($warehouses as $warehouse) {
            $totalStock += StockMove::where('warehouse_id', $warehouse->id)
                ->sum('quantity');
        }

        return [
            'total_warehouses' => $totalWarehouses,
            'active_warehouses' => $activeWarehouses,
            'total_stock_units' => $totalStock,
            'warehouses_by_type' => $warehouses->groupBy('type')->map->count()->toArray(),
        ];
    }

    /**
     * Transfer stock between warehouses
     */
    public function transferStock(
        int $fromWarehouseId,
        int $toWarehouseId,
        int $productId,
        float $quantity,
        string $referenceType,
        ?int $referenceId,
        int $movedBy
    ): array {
        return DB::transaction(function () use ($fromWarehouseId, $toWarehouseId, $productId, $quantity, $referenceType, $referenceId, $movedBy) {
            // Create outgoing move from source warehouse
            $outgoing = StockMove::create([
                'warehouse_id' => $fromWarehouseId,
                'product_id' => $productId,
                'quantity' => -$quantity,
                'move_type' => StockMove::TYPE_TRANSFER,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'moved_by' => $movedBy,
                'status' => StockMove::STATUS_COMPLETED,
                'moved_at' => now(),
            ]);

            // Create incoming move to destination warehouse
            $incoming = StockMove::create([
                'warehouse_id' => $toWarehouseId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_cost' => $outgoing->unit_cost,
                'total_value' => $outgoing->total_value,
                'move_type' => StockMove::TYPE_TRANSFER,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'from_warehouse_id' => $fromWarehouseId,
                'to_warehouse_id' => $toWarehouseId,
                'moved_by' => $movedBy,
                'status' => StockMove::STATUS_COMPLETED,
                'moved_at' => now(),
            ]);

            return ['outgoing' => $outgoing, 'incoming' => $incoming];
        });
    }
}
