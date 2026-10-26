<?php

namespace App\Modules\Cod\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cod\Models\CodCollection;
use App\Modules\Cod\Models\CodOrder;
use App\Modules\Cod\Models\CodSetting;
use App\Modules\Cod\Services\CodService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CodController extends Controller
{
    public function __construct(private CodService $cod) {}

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    /**
     * Semak kelayakan COD untuk satu nilai pesanan.
     */
    public function semakKelayakan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_amount' => ['required', 'numeric', 'min:0'],
            'state' => ['nullable', 'string', 'max:60'],
            'courier' => ['nullable', 'string', 'max:80'],
        ]);

        $setting = CodSetting::untukTenant($this->tenantId());

        $hasil = $this->cod->semakKelayakan(
            $setting,
            (float) $validated['order_amount'],
            $validated['state'] ?? null,
            $validated['courier'] ?? null
        );

        return response()->json(['success' => true, 'data' => $hasil]);
    }

    /**
     * Semak alamat & liputan.
     */
    public function semakAlamat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'address' => ['required', 'string', 'max:1000'],
            'postcode' => ['nullable', 'string', 'max:12'],
            'state' => ['nullable', 'string', 'max:60'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $hasil = $this->cod->semakAlamat(
            $validated['address'],
            $validated['postcode'] ?? null,
            $validated['state'] ?? null,
            $validated['phone'] ?? null
        );

        return response()->json(['success' => true, 'data' => $hasil]);
    }

    /**
     * Senarai pesanan COD.
     */
    public function index(Request $request): JsonResponse
    {
        $query = CodOrder::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('courier')) {
            $query->where('courier', $request->string('courier')->toString());
        }

        if ($request->filled('search')) {
            $s = '%'.trim($request->string('search')->toString()).'%';
            $query->where(function ($q) use ($s) {
                $q->where('cod_no', 'like', $s)
                    ->orWhere('customer_name', 'like', $s)
                    ->orWhere('customer_phone', 'like', $s)
                    ->orWhere('tracking_number', 'like', $s);
            });
        }

        $orders = $query->paginate(min((int) $request->integer('per_page', 25), 100));

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => ['total' => $orders->total()],
        ]);
    }

    /**
     * Buat pesanan COD.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'marketplace_order_id' => ['nullable', 'integer'],
            'reference_no' => ['nullable', 'string', 'max:60'],
            'customer_name' => ['required', 'string', 'max:180'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'postcode' => ['nullable', 'string', 'max:12'],
            'state' => ['nullable', 'string', 'max:60'],
            'city' => ['nullable', 'string', 'max:100'],
            'order_amount' => ['required', 'numeric', 'min:0'],
            'shipping_amount' => ['nullable', 'numeric', 'min:0'],
            'courier' => ['nullable', 'string', 'max:80'],
            'currency' => ['nullable', 'string', 'max:3'],
        ]);

        $tenantId = $this->tenantId();
        $setting = CodSetting::untukTenant($tenantId);

        $orderAmount = (float) $validated['order_amount'];
        $shipping = (float) ($validated['shipping_amount'] ?? 0);

        // Semak kelayakan
        $kelayakan = $this->cod->semakKelayakan(
            $setting,
            $orderAmount,
            $validated['state'] ?? null,
            $validated['courier'] ?? null
        );

        // Semak alamat
        $alamat = $this->cod->semakAlamat(
            $validated['delivery_address'],
            $validated['postcode'] ?? null,
            $validated['state'] ?? null,
            $validated['customer_phone']
        );

        $surcharge = $kelayakan['surcharge'];
        $total = round($orderAmount + $shipping + $surcharge, 2);

        $order = CodOrder::create([
            'tenant_id' => $tenantId,
            'marketplace_order_id' => $validated['marketplace_order_id'] ?? null,
            'cod_no' => $this->cod->janaCodNo($tenantId),
            'reference_no' => $validated['reference_no'] ?? null,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'delivery_address' => $validated['delivery_address'],
            'postcode' => $validated['postcode'] ?? null,
            'state' => $validated['state'] ?? null,
            'city' => $validated['city'] ?? null,
            'order_amount' => $orderAmount,
            'shipping_amount' => $shipping,
            'surcharge' => $surcharge,
            'total_amount' => $total,
            'amount_to_collect' => $total,
            'currency' => $validated['currency'] ?? 'MYR',
            'status' => CodOrder::STATUS_PENDING,
            'courier' => $validated['courier'] ?? null,
            'address_verified' => $alamat['verified'],
            'address_issue' => $alamat['issue'],
            'serviceable' => $alamat['serviceable'],
            'created_by' => auth()->id(),
            'meta' => [
                'kelayakan' => $kelayakan,
                'perlu_konfirmasi' => $kelayakan['needs_confirmation'],
                'perlu_panggilan' => $kelayakan['needs_call'],
            ],
        ]);

        // Jejak audit
        $order->histories()->create([
            'tenant_id' => $tenantId,
            'from_status' => null,
            'to_status' => CodOrder::STATUS_PENDING,
            'note' => 'Pesanan COD dicipta',
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $order,
            'kelayakan' => $kelayakan,
            'alamat' => $alamat,
        ], 201);
    }

    /**
     * Butiran pesanan COD.
     */
    public function show(int $id): JsonResponse
    {
        $order = CodOrder::with('histories')->find($id);

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Pesanan COD tidak dijumpai'], 404);
        }

        return response()->json(['success' => true, 'data' => $order]);
    }

    /**
     * Tukar status pesanan COD.
     */
    public function tukarStatus(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,dispatched,delivered,collected,failed,cancelled,refunded'],
            'note' => ['nullable', 'string', 'max:500'],
            'tracking_number' => ['nullable', 'string', 'max:80'],
            'courier' => ['nullable', 'string', 'max:80'],
            'failure_reason' => ['nullable', 'string', 'max:250'],
            'amount_collected' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = CodOrder::find($id);

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Pesanan COD tidak dijumpai'], 404);
        }

        if (! empty($validated['tracking_number'])) {
            $order->tracking_number = $validated['tracking_number'];
        }

        if (! empty($validated['courier'])) {
            $order->courier = $validated['courier'];
        }

        if (! empty($validated['failure_reason'])) {
            $order->failure_reason = $validated['failure_reason'];
        }

        $order->save();

        // Rekod kutipan jika ada
        if (! empty($validated['amount_collected'])) {
            $this->cod->rekodKutipan($order, (float) $validated['amount_collected'], auth()->id());
        } else {
            $this->cod->tukarStatus($order, $validated['status'], $validated['note'] ?? null, auth()->id());
        }

        return response()->json(['success' => true, 'data' => $order->fresh()]);
    }

    /**
     * Tetapan COD.
     */
    public function tetapan(): JsonResponse
    {
        $setting = CodSetting::untukTenant($this->tenantId());

        return response()->json(['success' => true, 'data' => $setting]);
    }

    /**
     * Kemas kini tetapan COD.
     */
    public function kemasKiniTetapan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'surcharge' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_order_amount' => ['nullable', 'numeric', 'min:0'],
            'allowed_couriers' => ['nullable', 'array'],
            'allowed_couriers.*' => ['string', 'max:80'],
            'excluded_regions' => ['nullable', 'array'],
            'excluded_regions.*' => ['string', 'max:60'],
            'require_confirmation' => ['nullable', 'boolean'],
            'require_call_confirmation' => ['nullable', 'boolean'],
        ]);

        $setting = CodSetting::untukTenant($this->tenantId());
        $setting->fill($validated)->save();

        return response()->json(['success' => true, 'data' => $setting->fresh()]);
    }

    /**
     * Analisis kegagalan COD.
     */
    public function analisis(Request $request): JsonResponse
    {
        $hari = min((int) $request->integer('days', 30), 365);

        return response()->json([
            'success' => true,
            'data' => $this->cod->analisisKegagalan($this->tenantId(), $hari),
        ]);
    }

    /**
     * Reconciliation harian.
     */
    public function reconcilesi(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
            'courier' => ['nullable', 'string', 'max:80'],
        ]);

        $hasil = $this->cod->reconcilesi(
            $this->tenantId(),
            $validated['date'] ?? now()->toDateString(),
            $validated['courier'] ?? null
        );

        return response()->json(['success' => true, 'data' => $hasil]);
    }

    /**
     * Rekod kutipan harian (batch).
     */
    public function rekodKutipan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'collection_date' => ['required', 'date'],
            'courier' => ['nullable', 'string', 'max:80'],
            'total_collected' => ['required', 'numeric', 'min:0'],
            'total_expected' => ['nullable', 'numeric', 'min:0'],
            'orders_collected' => ['nullable', 'integer', 'min:0'],
            'orders_failed' => ['nullable', 'integer', 'min:0'],
            'total_refunded' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $collection = CodCollection::create(array_merge($validated, [
            'tenant_id' => $this->tenantId(),
            'recorded_by' => auth()->id(),
        ]));

        return response()->json(['success' => true, 'data' => $collection], 201);
    }
}
