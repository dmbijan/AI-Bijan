<?php

namespace App\Modules\Logistics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Logistics\Services\StockMoveService;
use Illuminate\Http\Request;

class StockMoveController extends Controller
{
    protected $stockMoveService;

    public function __construct(StockMoveService $stockMoveService)
    {
        $this->stockMoveService = $stockMoveService;
    }

    /**
     * List stock moves for warehouse
     */
    public function index(Request $request, int $warehouseId)
    {
        $moves = $this->stockMoveService->getMovementHistory($warehouseId, [
            'move_type' => $request->query('move_type'),
            'status' => $request->query('status'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $moves,
        ]);
    }

    /**
     * Receive incoming stock
     */
    public function receive(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:logistics_warehouses,id',
            'product_id' => 'required|integer',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'lot_number' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'batch_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'reference_type' => 'required|in:purchase_order,transfer,adjustment,return',
            'reference_id' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = app(TenantContext::class)->id();
        $validated['moved_by'] = $request->user()->id;

        try {
            $move = $this->stockMoveService->createIncoming($validated);

            return response()->json([
                'success' => true,
                'data' => $move,
                'message' => 'Stock received successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Dispatch outgoing stock
     */
    public function dispatch(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:logistics_warehouses,id',
            'product_id' => 'required|integer',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'lot_number' => 'nullable|string|max:100',
            'serial_number' => 'nullable|string|max:100',
            'batch_number' => 'nullable|string|max:100',
            'expiry_date' => 'nullable|date',
            'reference_type' => 'required|in:sales_order,transfer,adjustment,return',
            'reference_id' => 'required|integer',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = app(TenantContext::class)->id();
        $validated['moved_by'] = $request->user()->id;

        try {
            $move = $this->stockMoveService->createOutgoing($validated);

            return response()->json([
                'success' => true,
                'data' => $move,
                'message' => 'Stock dispatched successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create stock adjustment
     */
    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:logistics_warehouses,id',
            'product_id' => 'required|integer',
            'quantity' => 'required|numeric',
            'unit_cost' => 'nullable|numeric|min:0',
            'lot_number' => 'nullable|string|max:100',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = app(TenantContext::class)->id();
        $validated['moved_by'] = $request->user()->id;

        try {
            $move = $this->stockMoveService->createAdjustment($validated);

            return response()->json([
                'success' => true,
                'data' => $move,
                'message' => 'Stock adjusted successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get available stock
     */
    public function getAvailable(Request $request, int $productId)
    {
        $warehouseId = $request->query('warehouse_id');
        $lotNumber = $request->query('lot_number');

        if (!$warehouseId) {
            return response()->json([
                'message' => 'warehouse_id is required',
            ], 400);
        }

        $available = $this->stockMoveService->getAvailableStock($warehouseId, $productId, $lotNumber);

        return response()->json([
            'success' => true,
            'data' => [
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'lot_number' => $lotNumber,
                'available_quantity' => $available,
            ],
        ]);
    }

    /**
     * Get stock levels
     */
    public function getStockLevels(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();
        $productId = $request->query('product_id');

        $levels = $this->stockMoveService->getStockLevels($tenantId, $productId);

        return response()->json([
            'success' => true,
            'data' => $levels,
        ]);
    }

    /**
     * Approve pending move
     */
    public function approve(Request $request, int $moveId)
    {
        try {
            $this->stockMoveService->approveMove($moveId, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Stock move approved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
