<?php

namespace App\Modules\Logistics\Services;

use App\Modules\Logistics\Models\StockMove;
use Illuminate\Support\Facades\DB;

class StockMoveService
{
    /**
     * Create an incoming stock move (receive goods)
     */
    public function createIncoming(array $data): StockMove
    {
        $data['move_type'] = StockMove::TYPE_INCOMING;
        $data['status'] = StockMove::STATUS_COMPLETED;
        $data['moved_at'] = now();
        $data['quantity'] = abs($data['quantity']);

        $move = StockMove::create($data);
        $move->calculateTotalValue()->save();

        return $move;
    }

    /**
     * Create an outgoing stock move (dispatch goods)
     */
    public function createOutgoing(array $data): StockMove
    {
        // Check available stock if negative stock not allowed
        if (!config('logistics.stock_move.allow_negative_stock', false)) {
            $available = $this->getAvailableStock(
                $data['warehouse_id'],
                $data['product_id'],
                $data['lot_number'] ?? null
            );

            if ($available < $data['quantity']) {
                throw new \RuntimeException(
                    "Insufficient stock. Available: {$available}, Requested: {$data['quantity']}"
                );
            }
        }

        $data['move_type'] = StockMove::TYPE_OUTGOING;
        $data['status'] = StockMove::STATUS_COMPLETED;
        $data['moved_at'] = now();
        $data['quantity'] = -$data['quantity']; // Negative for outgoing

        $move = StockMove::create($data);
        $move->calculateTotalValue()->save();

        return $move;
    }

    /**
     * Create a stock adjustment
     */
    public function createAdjustment(array $data): StockMove
    {
        $data['move_type'] = StockMove::TYPE_ADJUSTMENT;
        $data['status'] = StockMove::STATUS_COMPLETED;
        $data['moved_at'] = now();

        $move = StockMove::create($data);
        $move->calculateTotalValue()->save();

        return $move;
    }

    /**
     * Get available stock for product in warehouse
     */
    public function getAvailableStock(int $warehouseId, int $productId, ?string $lotNumber = null): float
    {
        $query = StockMove::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->where('status', StockMove::STATUS_COMPLETED);

        if ($lotNumber) {
            $query->where('lot_number', $lotNumber);
        }

        // Sum all quantities (incoming positive, outgoing negative)
        return $query->sum('quantity');
    }

    /**
     * Get stock levels across all warehouses
     */
    public function getStockLevels(int $tenantId, ?int $productId = null): array
    {
        $query = StockMove::join('warehouses', 'logistics_stock_moves.warehouse_id', '=', 'warehouses.id')
            ->where('warehouses.tenant_id', $tenantId)
            ->where('logistics_stock_moves.status', StockMove::STATUS_COMPLETED);

        if ($productId) {
            $query->where('logistics_stock_moves.product_id', $productId);
        }

        $results = $query->select(
            'logistics_stock_moves.warehouse_id',
            'warehouses.name as warehouse_name',
            'logistics_stock_moves.product_id',
            DB::raw('SUM(logistics_stock_moves.quantity) as total_quantity'),
            DB::raw('AVG(logistics_stock_moves.unit_cost) as avg_unit_cost')
        )
        ->groupBy(
            'logistics_stock_moves.warehouse_id',
            'warehouses.name',
            'logistics_stock_moves.product_id'
        )
        ->having('total_quantity', '>', 0)
        ->get();

        return $results->toArray();
    }

    /**
     * Get stock movement history
     */
    public function getMovementHistory(int $warehouseId, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = StockMove::where('warehouse_id', $warehouseId);

        if (isset($filters['move_type'])) {
            $query->where('move_type', $filters['move_type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from'])) {
            $query->where('moved_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('moved_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('moved_at', 'desc')
                     ->paginate(50);
    }

    /**
     * Cancel a pending stock move
     */
    public function cancelMove(int $moveId): bool
    {
        $move = StockMove::findOrFail($moveId);

        if ($move->status !== StockMove::STATUS_PENDING) {
            throw new \RuntimeException('Only pending moves can be cancelled.');
        }

        return $move->update(['status' => StockMove::STATUS_CANCELLED]);
    }

    /**
     * Approve a pending stock move requiring approval
     */
    public function approveMove(int $moveId, int $approvedBy): bool
    {
        $move = StockMove::findOrFail($moveId);

        if ($move->status !== StockMove::STATUS_PENDING) {
            throw new \RuntimeException('Only pending moves can be approved.');
        }

        return $move->update([
            'status' => StockMove::STATUS_COMPLETED,
            'approved_by' => $approvedBy,
            'moved_at' => now(),
        ]);
    }

    /**
     * Calculate total stock value for warehouse
     */
    public function calculateWarehouseValue(int $warehouseId): float
    {
        return StockMove::where('warehouse_id', $warehouseId)
            ->where('status', StockMove::STATUS_COMPLETED)
            ->sum('total_value');
    }
}
