<?php

namespace App\Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Orders\Models\OrderChannelBinding;
use App\Modules\Orders\Models\OrderSyncLog;
use App\Modules\Orders\Models\UnifiedOrder;
use App\Modules\Orders\Models\UnifiedOrderItem;
use App\Modules\Orders\Services\UnifiedOrderService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private UnifiedOrderService $orders) {}

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    /**
     * Senarai pesanan unified dengan filter + pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = UnifiedOrder::query()->orderByDesc('placed_at');

        foreach (['status', 'source', 'source_platform', 'payment_status'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        if ($request->filled('date_from')) {
            $query->whereDate('placed_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('placed_at', '<=', $request->input('date_to'));
        }

        if ($request->filled('search')) {
            $s = '%' . trim($request->string('search')->toString()) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('order_no', 'like', $s)
                    ->orWhere('source_order_no', 'like', $s)
                    ->orWhere('customer_name', 'like', $s)
                    ->orWhere('customer_email', 'like', $s)
                    ->orWhere('customer_phone', 'like', $s);
            });
        }

        $perPage = min(max((int) $request->integer('per_page', 25), 1), 100);
        $paginator = $query->with('items')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    /**
     * Cipta pesanan manual / ingest pesanan dari API.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source' => ['nullable', 'in:manual,marketplace,pos,site,booking,cod,wholesale'],
            'source_platform' => ['nullable', 'string', 'max:60'],
            'source_id' => ['nullable', 'string', 'max:120'],
            'source_order_no' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', 'string', 'max:60'],
            'payment_method' => ['nullable', 'string', 'max:60'],
            'payment_status' => ['nullable', 'in:unpaid,paid,partial,refunded,failed'],
            'fulfillment_method' => ['nullable', 'string', 'max:60'],
            'currency' => ['nullable', 'string', 'size:3'],
            'customer_name' => ['nullable', 'string', 'max:180'],
            'customer_email' => ['nullable', 'email', 'max:180'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'shipping_address' => ['nullable', 'array'],
            'billing_address' => ['nullable', 'array'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer'],
            'items.*.sku' => ['nullable', 'string', 'max:100'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.variant' => ['nullable', 'string', 'max:100'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'shipping_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
            $items = $validated['items'];
            unset($validated['items']);

            $order = $this->orders->ingest($this->tenantId(), array_merge($validated, [
                'items' => $items,
                'placed_at' => now(),
            ]));

            if (isset($validated['shipping_amount'])) {
                $order->update(['shipping_amount' => $validated['shipping_amount']]);
                $this->orders->kiraJumlah($this->tenantId(), $order);
            }

            return $order;
        });

        return response()->json(['success' => true, 'data' => $order->load(['items', 'reservations'])], 201);
    }

    /**
     * Butiran pesanan + item + timeline + rizab stok.
     */
    public function show(int $id): JsonResponse
    {
        $order = UnifiedOrder::query()
            ->with(['items', 'reservations', 'timeline' => fn ($q) => $q->orderBy('created_at'), 'syncLogs'])
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $order]);
    }

    /**
     * Tukar status pesanan.
     */
    public function tukarStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,pending,confirmed,paid,processing,packed,shipped,out_for_delivery,delivered,cancelled,refunded,partially_refunded,on_hold,failed'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = $this->orders->tukarStatus(
            $this->tenantId(),
            $id,
            $validated['status'],
            $validated['note'] ?? null,
            'user',
            auth()->id()
        );

        return response()->json(['success' => true, 'data' => $order]);
    }

    /**
     * Rekod bayaran.
     */
    public function rekodBayaran(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['nullable', 'string', 'max:60'],
            'reference' => ['nullable', 'string', 'max:120'],
        ]);

        $order = $this->orders->rekodBayaran(
            $this->tenantId(),
            $id,
            (float) $validated['amount'],
            $validated['method'] ?? 'bank_transfer',
            $validated['reference'] ?? null
        );

        return response()->json(['success' => true, 'data' => $order]);
    }

    /**
     * Ringkasan jualan semua saluran.
     */
    public function ringkasan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->orders->ringkasan(
                $this->tenantId(),
                $validated['date_from'] ?? now()->startOfMonth()->toDateString(),
                $validated['date_to'] ?? now()->toDateString()
            ),
        ]);
    }

    /**
     * Pesanan yang memerlukan tindakan / reminder.
     */
    public function perluTindakan(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->integer('limit', 50), 1), 100);

        return response()->json([
            'success' => true,
            'data' => $this->orders->perlukanTindakan($this->tenantId(), $limit),
        ]);
    }

    /**
     * Timeline pesanan.
     */
    public function timeline(int $id): JsonResponse
    {
        $order = UnifiedOrder::query()->findOrFail($id);
        $events = $order->timeline()->orderBy('created_at')->get();

        return response()->json(['success' => true, 'data' => $events]);
    }

    /**
     * Channel bindings (platform mappings).
     */
    public function channels(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => OrderChannelBinding::query()->orderBy('platform')->get(),
        ]);
    }

    /**
     * Cipta/kemas kini channel binding.
     */
    public function simpanChannel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'string', 'max:60'],
            'display_name' => ['nullable', 'string', 'max:120'],
            'status_map' => ['nullable', 'array'],
            'payment_methods' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'stock_sync_enabled' => ['nullable', 'boolean'],
            'stock_push_enabled' => ['nullable', 'boolean'],
            'sync_interval_minutes' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ]);

        $binding = OrderChannelBinding::updateOrCreate(
            ['tenant_id' => $this->tenantId(), 'platform' => $validated['platform']],
            array_merge($validated, ['tenant_id' => $this->tenantId()])
        );

        return response()->json(['success' => true, 'data' => $binding], 201);
    }

    /**
     * Log sync terkini.
     */
    public function syncLogs(Request $request): JsonResponse
    {
        $query = OrderSyncLog::query()->orderByDesc('created_at');

        if ($request->filled('platform')) {
            $query->where('platform', $request->input('platform'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit(100)->get(),
        ]);
    }

    /**
     * Trigger sync (data external disediakan adapter platform).
     */
    public function sync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'platform' => ['required', 'string', 'max:60'],
            'orders' => ['nullable', 'array'],
        ]);

        $result = $this->orders->sinkronPlatform(
            $this->tenantId(),
            $validated['platform'],
            $validated['orders'] ?? []
        );

        return response()->json(['success' => true, 'data' => $result]);
    }
}
