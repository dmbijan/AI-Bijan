<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Marketplace\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$request->tenant_id` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Get cart items for current user.
     */
    public function index(Request $request): JsonResponse
    {
        $items = $this->cartService->getForUser($request->user()->id);
        $totals = $this->cartService->getTotals($request->user()->id);

        return response()->json([
            'items' => $items,
            'totals' => $totals,
        ]);
    }

    /**
     * Add item to cart.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $item = $this->cartService->addItem(
                app(TenantContext::class)->id(),
                $request->user()->id,
                $request->input('product_id'),
                $request->input('quantity', 1)
            );

            return response()->json($item, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Update cart item quantity.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Find cart item for user
        $cartItem = \App\Modules\Marketplace\Models\Cart::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $cartItem = $this->cartService->updateQuantity($cartItem, $request->input('quantity'));

        return response()->json($cartItem);
    }

    /**
     * Remove item from cart.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $cartItem = \App\Modules\Marketplace\Models\Cart::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }

        $this->cartService->removeItem($cartItem);

        return response()->json(['message' => 'Cart item removed']);
    }

    /**
     * Clear entire cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $count = $this->cartService->clearCart($request->user()->id);

        return response()->json(['message' => "{$count} items cleared from cart"]);
    }

    /**
     * Validate cart before checkout.
     */
    public function validate(Request $request): JsonResponse
    {
        $validation = $this->cartService->validateCart($request->user()->id);

        return response()->json($validation);
    }

    /**
     * Get cart totals.
     */
    public function totals(Request $request): JsonResponse
    {
        $totals = $this->cartService->getTotals($request->user()->id);

        return response()->json($totals);
    }
}
