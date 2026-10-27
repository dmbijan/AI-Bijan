<?php

namespace App\Modules\Payroll\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Payroll\Models\OvertimeRecord;
use App\Modules\Payroll\Services\OvertimeCalculator;
use Illuminate\Http\Request;

/**
 * B-Payroll Overtime Controller
 * 
 * Manages overtime requests and approvals.
 * Uses CORRECT Akta Kerja 1955 rates:
 * - Regular day: 1.5x hourly rate
 * - Rest day: 2x hourly rate
 * - Public holiday: 3x hourly rate
 */
class OvertimeController extends Controller
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
    /**
     * List overtime records for current tenant.
     */
    public function index(Request $request)
    {
        $query = OvertimeRecord::where('tenant_id', $this->tenantId())
            ->with(['employee'])
            ->orderBy('date', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        return response()->json($query->paginate(50));
    }

    /**
     * Create a new overtime record.
     * 
     * POST /api/v1/payroll/overtime
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'day_type' => 'required|in:regular,rest_day,public_holiday',
            'hours_worked' => 'required|numeric|min:0.25|max:12',
        ]);

        // Verify employee belongs to tenant
        $employee = \App\Modules\Payroll\Models\Employee::where('tenant_id', $this->tenantId())
            ->findOrFail($validated['employee_id']);

        // Calculate OT pay immediately
        $hourlyRate = $employee->hourly_rate ?? OvertimeCalculator::calculateHourlyRate($employee->basic_salary);
        $otCalc = OvertimeCalculator::calculateOvertimePay(
            $hourlyRate,
            $validated['day_type'],
            $validated['hours_worked']
        );

        $overtime = OvertimeRecord::create([
            'tenant_id' => $this->tenantId(),
            'employee_id' => $validated['employee_id'],
            'date' => $validated['date'],
            'day_type' => $validated['day_type'],
            'hours_worked' => $validated['hours_worked'],
            'hourly_rate' => $hourlyRate,
            'multiplier' => $otCalc['multiplier'],
            'ot_pay' => $otCalc['ot_pay'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Overtime request submitted',
            'data' => $overtime->load('employee'),
        ], 201);
    }

    /**
     * Approve an overtime request.
     * 
     * PUT /api/v1/payroll/overtime/{overtimeId}/approve
     */
    public function approve(Request $request, int $overtimeId)
    {
        $overtime = OvertimeRecord::where('tenant_id', $this->tenantId())
            ->findOrFail($overtimeId);

        if ($overtime->status !== 'pending') {
            return response()->json([
                'error' => "Cannot approve overtime with status: {$overtime->status}",
            ], 422);
        }

        $overtime->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Overtime approved',
            'data' => $overtime->fresh(),
        ]);
    }

    /**
     * Reject an overtime request.
     * 
     * PUT /api/v1/payroll/overtime/{overtimeId}/reject
     */
    public function reject(Request $request, int $overtimeId)
    {
        $overtime = OvertimeRecord::where('tenant_id', $this->tenantId())
            ->findOrFail($overtimeId);

        if ($overtime->status !== 'pending') {
            return response()->json([
                'error' => "Cannot reject overtime with status: {$overtime->status}",
            ], 422);
        }

        $overtime->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Overtime rejected',
            'data' => $overtime->fresh(),
        ]);
    }
}
