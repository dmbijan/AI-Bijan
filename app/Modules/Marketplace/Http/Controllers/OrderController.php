<?php

namespace App\Modules\Marketplace\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Marketplace\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
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
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * List all orders with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        $orders = $this->orderService->list(
            $tenantId,
            $request->only(['status', 'payment_status', 'vendor_id', 'customer_id', 'date_from', 'date_to']),
            $request->get('per_page', 20)
        );

        return response()->json($orders);
    }

    /**
     * Get a single order by ID.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order);
    }

    /**
     * Create a new order from cart items.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'cart_items' => 'required|array',
            'shipping_method' => 'required|string',
            'shipping_address' => 'required|string',
            'billing_address' => 'nullable|string',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $order = $this->orderService->createFromCart(
            app(TenantContext::class)->id(),
            $request->user()->id,
            session()->getId(),
            $request->input('cart_items'),
            $request->only(['shipping_method', 'shipping_address', 'billing_address', 'payment_method', 'notes'])
        );

        return response()->json($order, 201);
    }

    /**
     * Update order status.
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled',
        ]);

        $order = $this->orderService->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        try {
            $order = $this->orderService->updateStatus($order, $request->input('status'));
            return response()->json(['message' => 'Order status updated', 'order' => $order]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    /**
     * Confirm payment for an order.
     */
    public function confirmPayment(Request $request, int $id): JsonResponse
    {
        $order = $this->orderService->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order = $this->orderService->confirmPayment(
            $order,
            $request->input('payment_method')
        );

        return response()->json(['message' => 'Payment confirmed', 'order' => $order]);
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string',
        ]);

        $order = $this->orderService->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order = $this->orderService->cancel($order, $request->input('reason', ''));

        return response()->json(['message' => 'Order cancelled', 'order' => $order]);
    }

    /**
     * Refund an order.
     */
    public function refund(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'full_refund' => 'boolean',
        ]);

        $order = $this->orderService->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order = $this->orderService->refund(
            $order,
            $request->input('amount', 0),
            $request->input('full_refund', true)
        );

        return response()->json(['message' => 'Order refunded', 'order' => $order]);
    }

    /**
     * Update tracking number.
     */
    public function updateTracking(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'tracking_number' => 'required|string|max:255',
        ]);

        $order = $this->orderService->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order = $this->orderService->updateTracking(
            $order,
            $request->input('tracking_number')
        );

        return response()->json(['message' => 'Tracking updated', 'order' => $order]);
    }

    /**
     * Get order statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        
        $stats = $this->orderService->getStatistics(
            $tenantId,
            $request->only(['date_from', 'date_to'])
        );

        return response()->json($stats);
    }
}
