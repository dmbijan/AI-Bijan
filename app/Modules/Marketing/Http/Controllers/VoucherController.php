<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Marketing\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VoucherController extends Controller
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
    protected VoucherService $voucherService;

    public function __construct(VoucherService $voucherService)
    {
        $this->voucherService = $voucherService;
    }

    /**
     * List all vouchers.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $filters = $request->only(['status', 'campaign_id', 'active_only']);

        $vouchers = $this->voucherService->getVouchers($tenantId, $filters);

        return response()->json([
            'success' => true,
            'data' => $vouchers,
        ]);
    }

    /**
     * Get a single voucher.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $voucher = $this->voucherService->getVoucherById($id, $tenantId);

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $voucher,
        ]);
    }

    /**
     * Create a new voucher.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:marketing_vouchers,code',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed,free_shipping',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_per_customer' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'applicable_products' => 'nullable|array',
            'applicable_categories' => 'nullable|array',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $data = $request->all();
        $data['tenant_id'] = $tenantId;

        $voucher = $this->voucherService->createVoucher($data);

        return response()->json([
            'success' => true,
            'data' => $voucher,
            'message' => 'Voucher created successfully.',
        ], 201);
    }

    /**
     * Update a voucher.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $voucher = $this->voucherService->getVoucherById($id, $tenantId);

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher not found.',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'type' => 'sometimes|in:percentage,fixed,free_shipping',
            'value' => 'nullable|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_per_customer' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'sometimes|in:active,paused,expired',
            'applicable_products' => 'nullable|array',
            'applicable_categories' => 'nullable|array',
        ]);

        $voucher = $this->voucherService->updateVoucher($voucher, $request->all());

        return response()->json([
            'success' => true,
            'data' => $voucher,
            'message' => 'Voucher updated successfully.',
        ]);
    }

    /**
     * Delete a voucher.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $voucher = $this->voucherService->getVoucherById($id, $tenantId);

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher not found.',
            ], 404);
        }

        $this->voucherService->deleteVoucher($voucher);

        return response()->json([
            'success' => true,
            'message' => 'Voucher deleted successfully.',
        ]);
    }

    /**
     * Activate a voucher.
     */
    public function activate(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $voucher = $this->voucherService->getVoucherById($id, $tenantId);

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher not found.',
            ], 404);
        }

        $voucher = $this->voucherService->activateVoucher($voucher);

        return response()->json([
            'success' => true,
            'data' => $voucher,
            'message' => 'Voucher activated.',
        ]);
    }

    /**
     * Deactivate a voucher.
     */
    public function deactivate(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $voucher = $this->voucherService->getVoucherById($id, $tenantId);

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher not found.',
            ], 404);
        }

        $voucher = $this->voucherService->deactivateVoucher($voucher);

        return response()->json([
            'success' => true,
            'data' => $voucher,
            'message' => 'Voucher deactivated.',
        ]);
    }

    /**
     * Validate a voucher code.
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'purchase_amount' => 'required|numeric|min:0',
            'product_ids' => 'nullable|array',
        ]);

        $result = $this->voucherService->validateVoucher(
            $request->code,
            $request->purchase_amount,
            $request->product_ids ?? null
        );

        return response()->json($result);
    }

    /**
     * Use a voucher (redeem).
     */
    public function use(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'contact_id' => 'required|integer',
            'order_id' => 'required|integer',
            'amount_used' => 'required|numeric|min:0',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $voucher = $this->voucherService->getVoucherById($id, $tenantId);

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher not found.',
            ], 404);
        }

        $this->voucherService->useVoucher(
            $voucher,
            $request->contact_id,
            $request->order_id,
            $request->amount_used
        );

        return response()->json([
            'success' => true,
            'message' => 'Voucher redeemed successfully.',
        ]);
    }

    /**
     * Generate multiple codes.
     */
    public function generateCodes(Request $request): JsonResponse
    {
        $request->validate([
            'count' => 'required|integer|min:1|max:1000',
        ]);

        $codes = $this->voucherService->generateMultipleCodes($request->count);

        return response()->json([
            'success' => true,
            'data' => ['codes' => $codes],
        ]);
    }

    /**
     * Get voucher statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $stats = $this->voucherService->getVoucherStats($tenantId);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
