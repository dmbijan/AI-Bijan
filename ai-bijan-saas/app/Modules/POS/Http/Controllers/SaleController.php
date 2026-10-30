<?php

namespace App\Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    protected SaleService $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    /**
     * List all sales.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'register_id', 'user_id', 'start_date', 'end_date', 'search']);

        return response()->json($this->saleService->listSales($request->tenant_id, $filters));
    }

    /**
     * Show a single sale.
     */
    public function show(Request $request, int $id)
    {
        $sale = $this->saleService->getSale($id, $request->tenant_id);

        if (! $sale) {
            return response()->json(['message' => 'Sale not found.'], 404);
        }

        return response()->json($sale);
    }

    /**
     * Create a draft sale.
     */
    public function storeDraft(Request $request)
    {
        $validated = $request->validate([
            'register_id' => 'required|exists:pos_registers,id',
            'notes' => 'nullable|string',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        try {
            $sale = $this->saleService->createDraft(
                $validated,
                $request->tenant_id,
                $validated['register_id'],
                $request->user()->id
            );

            return response()->json($sale, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Add item to a sale.
     */
    public function addItem(Request $request, int $saleId)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:items,id',
            'product_name' => 'required|string|max:255',
            'product_sku' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $item = $this->saleService->addItem($saleId, $validated, $request->tenant_id);

            return response()->json($item, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Update an item in a sale.
     */
    public function updateItem(Request $request, int $itemId)
    {
        $validated = $request->validate([
            'quantity' => 'sometimes|integer|min:1',
            'unit_price' => 'sometimes|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
        ]);

        $result = $this->saleService->updateItem($itemId, $validated, $request->tenant_id);

        if (! $result) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        return response()->json($result);
    }

    /**
     * Remove an item from a sale.
     */
    public function removeItem(Request $request, int $itemId)
    {
        try {
            $success = $this->saleService->removeItem($itemId, $request->tenant_id);

            if (! $success) {
                return response()->json(['message' => 'Item not found.'], 404);
            }

            return response()->json(['message' => 'Item removed successfully.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Complete a sale.
     */
    public function complete(Request $request, int $saleId)
    {
        $validated = $request->validate([
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|in:cash,card,ewallet,duitnow,credit,voucher,split',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.reference' => 'nullable|string',
            'payments.*.details' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        try {
            $sale = $this->saleService->completeSale($saleId, $validated, $request->tenant_id);

            return response()->json($sale, 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Checkout satu jalan. Hantar header Idempotency-Key supaya segerak luar talian tak jadi pendua.
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'register_id' => 'required|exists:pos_registers,id',
            'customer_id' => 'nullable|exists:customers,id',
            'notes' => 'nullable|string',
            'sold_at' => 'nullable|date|before_or_equal:now|after:-30 days',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:items,id',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.product_sku' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payments' => 'required|array|min:1',
            'payments.*.method' => 'required|in:cash,card,ewallet,duitnow,credit,voucher,split',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.reference' => 'nullable|string',
            'payments.*.details' => 'nullable|array',
        ]);

        try {
            $sale = $this->saleService->checkout($validated, $request->tenant_id, $request->user()->id);

            return response()->json($sale, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Cancel a draft sale.
     */
    public function cancel(Request $request, int $saleId)
    {
        try {
            $success = $this->saleService->cancelSale($saleId, $request->tenant_id);

            if (! $success) {
                return response()->json(['message' => 'Sale not found or cannot be cancelled.'], 404);
            }

            return response()->json(['message' => 'Sale cancelled successfully.']);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Refund a completed sale.
     */
    public function refund(Request $request, int $saleId)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string',
        ]);

        try {
            $return = $this->saleService->refundSale($saleId, $validated, $request->tenant_id, $request->user()->id);

            return response()->json($return, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get POS summary statistics.
     */
    public function summary(Request $request)
    {
        return response()->json($this->saleService->getSummary(
            $request->tenant_id,
            $request->input('start_date'),
            $request->input('end_date')
        ));
    }
}
