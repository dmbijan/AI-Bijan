<?php

namespace App\Modules\POS\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\POS\Services\SaleService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$this->tenantId()` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
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

        return response()->json($this->saleService->listSales($this->tenantId(), $filters));
    }

    /**
     * Show a single sale.
     */
    public function show(Request $request, int $id)
    {
        $sale = $this->saleService->getSale($id, $this->tenantId());

        if (!$sale) {
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
                $this->tenantId(),
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
            // ⚠️ 27 Okt 2026: `inventory_products` sudah dipetakan semula kepada
            // `items` oleh migrasi `2026_10_27_000200_fix_broken_foreign_keys`.
            // Validasi lama menunjuk jadual yang tidak wujud → 500/pemilih produk gagal.
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
            $item = $this->saleService->addItem($saleId, $validated, $this->tenantId());
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

        $result = $this->saleService->updateItem($itemId, $validated, $this->tenantId());

        if (!$result) {
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
            $success = $this->saleService->removeItem($itemId, $this->tenantId());

            if (!$success) {
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
            'payments.*.method' => 'required|in:cash,card,ewallet,credit,voucher,split',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.reference' => 'nullable|string',
            'payments.*.details' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        try {
            $sale = $this->saleService->completeSale($saleId, $validated, $this->tenantId());
            return response()->json($sale, 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Bayar & selesaikan jualan dengan tender berbilang (BAB 88 / T-79).
     *
     * Berbeza daripada `complete()` (yang guna pos_sale_payments lama):
     * endpoint ini menggunakan `pos_sale_tenders`, mengira caj perkhidmatan
     * (T-78), memeriksa kelulusan diskaun (T-83) dan menyokong idempotency
     * luar talian (T-81) melalui `client_uuid`.
     */
    /**
     * Checkout satu jalan: draf + item + bayar dalam satu transaksi.
     * Dipakai kasa, termasuk segerak jualan luar talian: `client_uuid` sama → jualan sedia ada dipulangkan
     * (tiada pendua); `sold_at` = masa jualan asal di peranti.
     */
    public function checkout(Request $request)
    {
        $v = $request->validate([
            'register_id' => 'required|integer|exists:pos_registers,id',
            'notes' => 'nullable|string|max:1000',
            'sold_at' => 'nullable|date|before_or_equal:now|after:-30 days',
            'items' => 'required|array|min:1|max:200',
            'items.*.product_id' => 'nullable|integer|exists:items,id',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.product_sku' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'tenders' => 'required|array|min:1|max:2',
            'tenders.*.method' => 'required|in:cash,card,ewallet,tng,credit,voucher,duitnow',
            'tenders.*.amount' => 'required|numeric|min:0.01',
            'tenders.*.cash_received' => 'nullable|numeric|min:0',
            'tenders.*.reference' => 'nullable|string|max:255',
            'client_uuid' => 'nullable|uuid',
        ]);
        $tenantId = $this->tenantId();

        if (! empty($v['client_uuid'])) {
            $sedia = \App\Modules\POS\Models\POSSale::forTenant($tenantId)->where('client_uuid', $v['client_uuid'])->where('status', 'completed')->first();
            if ($sedia) {
                return response()->json(['message' => 'Jualan sudah direkod.', 'data' => $sedia->load('items', 'tenders')]);
            }
        }

        try {
            $sale = \Illuminate\Support\Facades\DB::transaction(function () use ($v, $tenantId, $request) {
                $sale = $this->saleService->createDraft(['notes' => $v['notes'] ?? null], $tenantId, (int) $v['register_id'], $request->user()->id);
                foreach ($v['items'] as $item) {
                    $this->saleService->addItem($sale->id, $item, $tenantId);
                }
                $sale = app(\App\Modules\POS\Services\SalePaymentService::class)->bayar($sale->fresh(), $v['tenders'], $v['client_uuid'] ?? null);
                if (! empty($v['sold_at'])) {
                    $masa = \Illuminate\Support\Carbon::parse($v['sold_at']);
                    $sale->forceFill(['completed_at' => $masa, 'sale_date' => $masa->format('Y-m-d')])->save();
                }

                return $sale;
            });
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        }

        return response()->json(['message' => 'Jualan selesai.', 'data' => $sale->fresh(['items', 'tenders'])], 201);
    }

    public function pay(Request $request, int $saleId)
    {
        $validated = $request->validate([
            'tenders' => 'required|array|min:1|max:2',
            'tenders.*.method' => 'required|in:cash,card,ewallet,tng,credit,voucher,duitnow',
            'tenders.*.amount' => 'required|numeric|min:0.01',
            'tenders.*.cash_received' => 'nullable|numeric|min:0',
            'tenders.*.reference' => 'nullable|string|max:255',
            'client_uuid' => 'nullable|uuid',
        ]);

        $sale = \App\Modules\POS\Models\POSSale::forTenant($this->tenantId())->find($saleId);

        if (! $sale) {
            return response()->json(['message' => 'Jualan tidak dijumpai.'], 404);
        }

        try {
            $sale = app(\App\Modules\POS\Services\SalePaymentService::class)
                ->bayar($sale, $validated['tenders'], $validated['client_uuid'] ?? null);

            return response()->json([
                'message' => 'Pembayaran berjaya.',
                'data' => [
                    'id' => $sale->id,
                    'sale_no' => $sale->sale_no,
                    'status' => $sale->status,
                    'payment_method' => $sale->payment_method,
                    'payment_status' => $sale->payment_status,
                    'subtotal' => (float) $sale->subtotal,
                    'discount_amount' => (float) $sale->discount_amount,
                    'service_charge_amount' => (float) $sale->service_charge_amount,
                    'service_charge_rate' => $sale->service_charge_rate !== null ? (float) $sale->service_charge_rate : null,
                    'total_amount' => (float) $sale->total_amount,
                    'amount_paid' => (float) $sale->amount_paid,
                    'change_amount' => (float) $sale->change_amount,
                    'tenders' => $sale->tenders->map(fn ($t) => [
                        'method' => $t->method,
                        'amount' => (float) $t->amount,
                        'cash_received' => $t->cash_received !== null ? (float) $t->cash_received : null,
                        'change_amount' => (float) $t->change_amount,
                        'payment_status' => $t->payment_status,
                    ])->values(),
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage(), 'errors' => $e->errors()], 422);
        }
    }


    /**
     * Cancel a draft sale.
     */
    public function cancel(Request $request, int $saleId)
    {
        try {
            $success = $this->saleService->cancelSale($saleId, $this->tenantId());

            if (!$success) {
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
            $return = $this->saleService->refundSale($saleId, $validated, $this->tenantId(), $request->user()->id);
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
            $this->tenantId(),
            $request->input('start_date'),
            $request->input('end_date')
        ));
    }
}
