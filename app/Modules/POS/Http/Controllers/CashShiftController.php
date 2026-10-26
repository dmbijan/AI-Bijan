<?php

namespace App\Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Services\CashShiftService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CashShiftController extends Controller
{
    protected CashShiftService $cashShiftService;

    public function __construct(CashShiftService $cashShiftService)
    {
        $this->cashShiftService = $cashShiftService;
    }

    /**
     * List all cash shifts.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'register_id', 'date']);

        return response()->json($this->cashShiftService->listShifts($request->tenant_id, $filters));
    }

    /**
     * Show a single shift.
     */
    public function show(Request $request, int $id)
    {
        $shift = $this->cashShiftService->getShift($id, $request->tenant_id);

        if (!$shift) {
            return response()->json(['message' => 'Shift not found.'], 404);
        }

        return response()->json($shift);
    }

    /**
     * Open a new cash shift.
     */
    public function open(Request $request)
    {
        $validated = $request->validate([
            'register_id' => 'required|exists:pos_registers,id',
            'opening_cash' => 'nullable|numeric|min:0',
        ]);

        try {
            $shift = $this->cashShiftService->openShift(
                $validated['register_id'],
                $request->user()->id,
                $validated['opening_cash'] ?? config('pos.cash_shift.float_cash', 200.00),
                $request->tenant_id
            );

            return response()->json($shift, 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Get current open shift for a register.
     */
    public function getOpen(Request $request, int $registerId)
    {
        $shift = $this->cashShiftService->getOpenShift($registerId, $request->tenant_id);

        if (!$shift) {
            return response()->json(['message' => 'No open shift for this register.'], 404);
        }

        return response()->json($shift);
    }

    /**
     * Close a cash shift.
     */
    public function close(Request $request, int $id)
    {
        $validated = $request->validate([
            'closing_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $shift = $this->cashShiftService->closeShift(
                $id,
                $validated,
                $request->user()->id,
                $request->tenant_id
            );

            return response()->json($shift);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Lock a closed shift.
     */
    public function lock(Request $request, int $id)
    {
        $result = $this->cashShiftService->lockShift($id, $request->user()->id, $request->tenant_id);

        if (!$result) {
            return response()->json(['message' => 'Shift not found or not in closed status.'], 404);
        }

        return response()->json($result);
    }

    /**
     * Get cash shift summary statistics.
     */
    public function summary(Request $request)
    {
        return response()->json($this->cashShiftService->getSummary(
            $request->tenant_id,
            $request->input('start_date'),
            $request->input('end_date')
        ));
    }
}
