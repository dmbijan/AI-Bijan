<?php

namespace App\Modules\Marketplace\Services;

use App\Modules\Marketplace\Models\Cart;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Get all cart items for a user.
     */
    public function getForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Cart::where('user_id', $userId)
            ->with(['product', 'vendor'])
            ->get();
    }

    /**
     * Get all cart items for a session (guest user).
     */
    public function getForSession(string $sessionId): \Illuminate\Database\Eloquent\Collection
    {
        return Cart::where('session_id', $sessionId)
            ->with(['product', 'vendor'])
            ->get();
    }

    /**
     * Add item to cart.
     */
    public function addItem(int $tenantId, int $userId, int $productId, int $quantity = 1): Cart
    {
        return DB::transaction(function () use ($tenantId, $userId, $productId, $quantity) {
            $product = \App\Modules\Marketplace\Models\Product::find($productId);

            if (!$product) {
                throw new \InvalidArgumentException("Product not found");
            }

            if (!$product->isInStock()) {
                throw new \InvalidArgumentException("Product is out of stock");
            }

            if ($quantity > $product->stock_quantity) {
                throw new \InvalidArgumentException("Not enough stock available");
            }

            // Check if item already exists in cart
            $existingCart = Cart::where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($existingCart) {
                // Update quantity
                $newQuantity = $existingCart->quantity + $quantity;
                $existingCart->update([
                    'quantity' => $newQuantity,
                    'subtotal' => $newQuantity * $existingCart->unit_price,
                ]);
                return $existingCart->fresh(['product', 'vendor']);
            }

            // Create new cart item
            $cartItem = Cart::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'vendor_id' => $product->vendor_id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'subtotal' => $quantity * $product->price,
            ]);

            return $cartItem->fresh(['product', 'vendor']);
        });
    }

    /**
     * Add item to cart for guest user (using session).
     */
    public function addGuestItem(int $tenantId, string $sessionId, int $productId, int $quantity = 1): Cart
    {
        return DB::transaction(function () use ($tenantId, $sessionId, $productId, $quantity) {
            $product = \App\Modules\Marketplace\Models\Product::find($productId);

            if (!$product) {
                throw new \InvalidArgumentException("Product not found");
            }

            // Check if item already exists in cart
            $existingCart = Cart::where('tenant_id', $tenantId)
                ->where('session_id', $sessionId)
                ->where('product_id', $productId)
                ->first();

            if ($existingCart) {
                $newQuantity = $existingCart->quantity + $quantity;
                $existingCart->update([
                    'quantity' => $newQuantity,
                    'subtotal' => $newQuantity * $existingCart->unit_price,
                ]);
                return $existingCart->fresh(['product', 'vendor']);
            }

            $cartItem = Cart::create([
                'tenant_id' => $tenantId,
                'session_id' => $sessionId,
                'vendor_id' => $product->vendor_id,
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'subtotal' => $quantity * $product->price,
            ]);

            return $cartItem->fresh(['product', 'vendor']);
        });
    }

    /**
     * Update cart item quantity.
     */
    public function updateQuantity(Cart $cartItem, int $quantity): Cart
    {
        if ($quantity <= 0) {
            return $this->removeItem($cartItem);
        }

        $cartItem->update([
            'quantity' => $quantity,
            'subtotal' => $quantity * $cartItem->unit_price,
        ]);

        return $cartItem->fresh();
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(Cart $cartItem): bool
    {
        return $cartItem->delete();
    }

    /**
     * Clear all items from cart.
     */
    public function clearCart(int $userId): int
    {
        return Cart::where('user_id', $userId)->delete();
    }

    /**
     * Clear session cart.
     */
    public function clearSessionCart(string $sessionId): int
    {
        return Cart::where('session_id', $sessionId)->delete();
    }

    /**
     * Merge guest cart into user cart on login.
     */
    public function mergeGuestCart(string $sessionId, int $userId, int $tenantId): int
    {
        $guestItems = Cart::where('session_id', $sessionId)->get();
        $mergedCount = 0;

        foreach ($guestItems as $guestItem) {
            // Check if item already exists in user cart
            $existingItem = Cart::where('tenant_id', $tenantId)
                ->where('user_id', $userId)
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existingItem) {
                // Merge quantities
                $newQuantity = $existingItem->quantity + $guestItem->quantity;
                $existingItem->update([
                    'quantity' => $newQuantity,
                    'subtotal' => $newQuantity * $existingItem->unit_price,
                ]);
            } else {
                // Move to user cart
                $guestItem->update([
                    'user_id' => $userId,
                    'session_id' => null,
                ]);
            }

            $mergedCount++;
        }

        // Clear guest cart
        Cart::where('session_id', $sessionId)->delete();

        return $mergedCount;
    }

    /**
     * Get cart totals.
     */
    public function getTotals(int $userId): array
    {
        $items = Cart::where('user_id', $userId)->get();

        $totalItems = $items->sum('quantity');
        $subtotal = $items->sum('subtotal');
        $taxAmount = $subtotal * config('tax.default_rate', 0.08);
        $shippingCost = $subtotal > config('marketplace.free_shipping_threshold', 100) ? 0 : 10;
        $totalAmount = $subtotal + $taxAmount + $shippingCost;

        return [
            'total_items' => $totalItems,
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($taxAmount, 2),
            'shipping_cost' => $shippingCost,
            'total_amount' => round($totalAmount, 2),
        ];
    }

    /**
     * Validate cart items before checkout.
     */
    public function validateCart(int $userId): array
    {
        $items = Cart::where('user_id', $userId)->with('product')->get();
        $invalidItems = [];
        $validItems = [];

        foreach ($items as $item) {
            if (!$item->product || !$item->product->isInStock()) {
                $invalidItems[] = $item->product_id;
            } elseif ($item->quantity > $item->product->stock_quantity) {
                $invalidItems[] = $item->product_id;
            } else {
                $validItems[] = $item;
            }
        }

        return [
            'is_valid' => empty($invalidItems),
            'invalid_items' => $invalidItems,
            'valid_items' => $validItems,
        ];
    }

    /**
     * Expire old cart items (older than configured duration).
     */
    public function expireOldCartItems(int $days = 30): int
    {
        $cutoffDate = now()->subDays($days);

        return Cart::where('updated_at', '<', $cutoffDate)->delete();
    }
}
