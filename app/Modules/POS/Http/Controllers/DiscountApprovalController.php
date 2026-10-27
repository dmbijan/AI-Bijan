<?php

namespace App\Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\POSDiscountApproval;
use App\Modules\POS\Services\DiscountApprovalService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

/**
 * Kelulusan diskaun (BAB 88 / T-83).
 */
class DiscountApprovalController extends Controller
{
    public function __construct(private DiscountApprovalService $discountService)
    {
    }

    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    /** Had diskaun berkesan untuk tenant (untuk dipaparkan di POS). */
    public function limits()
    {
        return response()->json([
            'requires_approval_above' => $this->discountService->hadKelulusan(),
            'max_percent' => $this->discountService->hadMutlak(),
        ]);
    }

    /** Juruwang meminta kelulusan. */
    public function request(Request $request)
    {
        $data = $request->validate([
            'sale_id' => 'nullable|integer',
            'discount_percent' => 'required|numeric|min:0.01|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $permohonan = $this->discountService->minta(
            $this->tenantId(),
            $data['sale_id'] ?? null,
            (float) $data['discount_percent'],
            (float) ($data['discount_amount'] ?? 0),
            (int) $request->user()->id,
            $data['reason'] ?? null
        );

        return response()->json(['data' => $permohonan], 201);
    }

    public function pending()
    {
        return response()->json([
            'data' => POSDiscountApproval::where('status', POSDiscountApproval::STATUS_PENDING)
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function approve(Request $request, int $id)
    {
        $permohonan = $this->discountService->luluskan(
            POSDiscountApproval::findOrFail($id),
            (int) $request->user()->id
        );

        return response()->json(['message' => 'Diskaun diluluskan.', 'data' => $permohonan]);
    }

    public function reject(Request $request, int $id)
    {
        $data = $request->validate(['reason' => 'nullable|string|max:255']);

        $permohonan = $this->discountService->tolak(
            POSDiscountApproval::findOrFail($id),
            (int) $request->user()->id,
            $data['reason'] ?? null
        );

        return response()->json(['message' => 'Diskaun ditolak.', 'data' => $permohonan]);
    }
}
