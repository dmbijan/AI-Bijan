<?php

namespace App\Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\CashShift;
use App\Modules\POS\Services\CashShiftService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

/**
 * Pergerakan tunai syif (BAB 88 / T-80).
 */
class ShiftCashController extends Controller
{
    public function __construct(private CashShiftService $shiftService)
    {
    }

    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    /** Rekod drop / pickup / expanse dalam laci. */
    public function store(Request $request, int $shiftId)
    {
        $data = $request->validate([
            'type' => 'required|in:drop,pickup,expense,adjustment',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
        ]);

        $gerak = $this->shiftService->recordCashMovement(
            $shiftId,
            $this->tenantId(),
            $data['type'],
            (float) $data['amount'],
            $data['reason'] ?? null,
            (int) $request->user()->id
        );

        return response()->json(['data' => $gerak], 201);
    }

    /** Ringkasan tunai syif (untuk penutupan). */
    public function summary(int $shiftId)
    {
        $shift = CashShift::with('cashMovements')->findOrFail($shiftId);

        return response()->json(['data' => $this->shiftService->ringkasan($shift)]);
    }

    /** Tutup syif dengan kiraan tunai sebenar. */
    public function close(Request $request, int $shiftId)
    {
        $data = $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $shift = $this->shiftService->closeShiftDenganRingkasan(
            $shiftId,
            $this->tenantId(),
            (float) $data['actual_cash'],
            (int) $request->user()->id,
            $data['notes'] ?? null
        );

        return response()->json([
            'message' => 'Syif ditutup.',
            'data' => [
                'id' => $shift->id,
                'status' => $shift->status,
                'expected_cash' => (float) $shift->expected_cash,
                'actual_cash' => (float) $shift->actual_cash,
                'cash_discrepancy' => (float) $shift->cash_discrepancy,
            ],
        ]);
    }
}
