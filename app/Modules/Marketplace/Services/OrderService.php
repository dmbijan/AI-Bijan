<?php

namespace App\Modules\Marketplace\Services;

use App\Modules\Marketplace\Models\Order;
use App\Modules\Marketplace\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Get all orders with pagination.
     */
    public function list(int $tenantId, array $filters = [], int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Order::where('tenant_id', $tenantId);

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['payment_status'])) {
            $query->byPaymentStatus($filters['payment_status']);
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->with(['items', 'vendor'])->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get a single order by ID.
     */
    public function find(int $orderId): ?Order
    {
        return Order::with(['items.product', 'vendor', 'customer'])->find($orderId);
    }

    /**
     * Create a new order from cart items.
     */
    public function createFromCart(int $tenantId, int $userId, string $sessionId, array $cartItems, array $orderData): Order
    {
        return DB::transaction(function () use ($tenantId, $userId, $cartItems, $orderData) {
            // Generate order number
            $orderNo = Order::generateOrderNo();

            // Calculate totals
            $subtotal = 0;
            $taxAmount = 0;
            $shippingCost = $orderData['shipping_cost'] ?? 0;
            $discountAmount = $orderData['discount_amount'] ?? 0;

            foreach ($cartItems as $item) {
                $subtotal += $item['subtotal'] ?? 0;
                $taxAmount += $item['tax_amount'] ?? 0;
            }

            $totalAmount = $subtotal + $taxAmount + $shippingCost - $discountAmount;

            // Create order
            $order = Order::create(array_merge($orderData, [
                'tenant_id' => $tenantId,
                'order_no' => $orderNo,
                'customer_id' => $userId,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_cost' => $shippingCost,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'currency' => $orderData['currency'] ?? 'MYR',
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]));

            // Create order items and deduct stock
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'tenant_id' => $tenantId,
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'vendor_id' => $item['vendor_id'],
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['product_sku'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'total' => $item['total'] ?? $item['subtotal'],
                ]);
            }

            // Deduct stock for each product
            foreach ($cartItems as $item) {
                $productService = app(ProductService::class);
                $productService->updateStock(
                    \App\Modules\Marketplace\Models\Product::find($item['product_id']),
                    $item['quantity'],
                    'remove'
                );
            }

            // Clear cart after order creation
            // This would be handled by CartService

            return $order->fresh(['items']);
        });
    }

    /**
     * Update order status.
     */
    public function updateStatus(Order $order, string $status): Order
    {
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing', 'cancelled'],
            'processing' => ['shipped'],
            'shipped' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];

        $currentStatus = $order->status;
        $allowedTransitions = $validTransitions[$currentStatus] ?? [];

        if (!in_array($status, $allowedTransitions)) {
            throw new \InvalidArgumentException(
                "Cannot transition order from '{$currentStatus}' to '{$status}'"
            );
        }

        $now = now();
        $updateData = ['status' => $status];

        // Set timestamps based on status
        if ($status === 'shipped') {
            $updateData['shipped_at'] = $now;
        } elseif ($status === 'delivered') {
            $updateData['delivered_at'] = $now;
        } elseif ($status === 'cancelled') {
            $updateData['cancelled_at'] = $now;
        }

        $order->update($updateData);
        return $order->fresh();
    }

    /**
     * Confirm payment for an order.
     */
    public function confirmPayment(Order $order, string $paymentMethod = null): Order
    {
        $updateData = ['payment_status' => 'paid'];

        if ($paymentMethod) {
            $updateData['payment_method'] = $paymentMethod;
        }

        // If order was pending, move to confirmed
        if ($order->status === 'pending') {
            $updateData['status'] = 'confirmed';
        }

        $order->update($updateData);
        return $order->fresh();
    }

    /**
     * Cancel an order.
     */
    public function cancel(Order $order, string $reason = ''): Order
    {
        return DB::transaction(function () use ($order, $reason) {
            // Restore stock
            foreach ($order->items as $item) {
                $productService = app(ProductService::class);
                $productService->updateStock(
                    \App\Modules\Marketplace\Models\Product::find($item->product_id),
                    $item->quantity,
                    'add'
                );
            }

            $order->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            return $order->fresh();
        });
    }

    /**
     * Refund an order.
     */
    public function refund(Order $order, float $amount = 0, bool $fullRefund = true): Order
    {
        $refundAmount = $amount > 0 ? $amount : ($fullRefund ? $order->total_amount : 0);

        $order->update([
            'payment_status' => $fullRefund ? 'refunded' : 'partially_refunded',
        ]);

        // Record refund in finance module (integration point)
        // This would call B-Finance service

        return $order->fresh();
    }

    /**
     * Update tracking number for shipped order.
     */
    public function updateTracking(Order $order, string $trackingNumber): Order
    {
        $order->update(['tracking_number' => $trackingNumber]);
        return $order->fresh();
    }

    /**
     * Get order statistics.
     */
    public function getStatistics(int $tenantId, array $filters = []): array
    {
        $query = Order::where('tenant_id', $tenantId);

        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return [
            'total_orders' => $query->count(),
            'total_revenue' => $query->sum('total_amount'),
            'average_order_value' => $query->avg('total_amount'),
            'by_status' => $query->selectRaw('status, COUNT(*) as count, SUM(total_amount) as total')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray(),
        ];
    }

    /**
     * Process pending orders older than X minutes.
     */
    public function processPendingOrders(int $minutes = 30): int
    {
        $cutoffTime = now()->subMinutes($minutes);

        $pendingOrders = Order::where('tenant_id', '>', 0)
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoffTime)
            ->get();

        $processed = 0;
        foreach ($pendingOrders as $order) {
            try {
                $this->updateStatus($order, 'confirmed');
                $processed++;
            } catch (\Exception $e) {
                // Log error and continue
                logger()->error("Failed to process order {$order->id}: " . $e->getMessage());
            }
        }

        return $processed;
    }
}
