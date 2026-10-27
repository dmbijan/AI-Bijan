<?php

namespace App\Modules\Payroll\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Payroll\Models\Employee;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\PayrollRunDetail;
use App\Modules\Payroll\Services\PayrollRunService;
use Illuminate\Http\Request;

/**
 * B-Payroll Run Controller
 * 
 * Handles payroll run lifecycle:
 * 1. Create draft payroll run
 * 2. Process all employees (calculate statutory deductions)
 * 3. Approve payroll run
 * 4. Finalize and generate payslips
 */
class PayrollRunController extends Controller
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
    protected $payrollService;

    public function __construct(PayrollRunService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * List payroll runs for current tenant.
     */
    public function index(Request $request)
    {
        $query = PayrollRun::query()
            ->with(['createdBy', 'approvedBy'])
            ->orderBy('period_end', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        return response()->json($query->paginate(20));
    }

    /**
     * Create a new payroll run.
     * 
     * POST /api/v1/payroll/payroll-runs
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'pay_date' => 'nullable|date',
        ]);

        try {
            // Create and process in one step
            $payrollRun = $this->payrollService->createAndProcess([
                'tenant_id' => $this->tenantId(),
                'name' => $validated['name'] ?? null,
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'pay_date' => $validated['pay_date'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Payroll run created and processed successfully',
                'data' => $payrollRun->load('details.employee'),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create payroll run',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get details of a specific payroll run.
     */
    public function show(Request $request, int $runId)
    {
        $payrollRun = PayrollRun::query()
            ->with(['details.employee', 'createdBy', 'approvedBy'])
            ->findOrFail($runId);

        return response()->json($payrollRun);
    }

    /**
     * Approve a payroll run.
     * 
     * POST /api/v1/payroll/payroll-runs/{runId}/approve
     */
    public function approve(Request $request, int $runId)
    {
        $payrollRun = PayrollRun::query()
            ->findOrFail($runId);

        try {
            $this->payrollService->approvePayrollRun(
                $payrollRun,
                $request->user()->id
            );

            return response()->json([
                'message' => 'Payroll run approved successfully',
                'data' => $payrollRun->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to approve payroll run',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Finalize payroll run and generate payslips.
     * 
     * POST /api/v1/payroll/payroll-runs/{runId}/finalize
     */
    public function finalize(Request $request, int $runId)
    {
        $payrollRun = PayrollRun::query()
            ->findOrFail($runId);

        try {
            $processed = $this->payrollService->processFinalPayroll($payrollRun);

            return response()->json([
                'message' => 'Payroll finalized. Payslips generated.',
                'data' => $processed->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to finalize payroll',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Generate payslip document for an employee.
     * 
     * GET /api/v1/payroll/payslips/{detailId}
     */
    public function payslip(Request $request, int $detailId)
    {
        $detail = PayrollRunDetail::query()
            ->with(['employee', 'payrollRun'])
            ->findOrFail($detailId);

        // Return structured data for SlipGaji template
        return response()->json([
            'employee' => [
                'name' => $detail->employee->name,
                'staff_id' => $detail->employee->ic_no,
                'position' => $detail->employee->position,
                'department' => $detail->employee->department,
                'epf_no' => $detail->employee->epf_no,
                'socso_no' => $detail->employee->socso_no,
                'bank_name' => $detail->employee->bank_name,
                'bank_account' => $detail->employee->bank_account_no,
            ],
            'payroll_run' => [
                'name' => $detail->payrollRun->name,
                'period' => "{$detail->payrollRun->period_start->format('Y-m-d')} to {$detail->payrollRun->period_end->format('Y-m-d')}",
                'pay_date' => $detail->payrollRun->pay_date?->format('Y-m-d'),
            ],
            'earnings' => [
                'basic_salary' => $detail->basic_salary,
                'fixed_allowance' => $detail->fixed_allowance,
                'overtime_pay' => $detail->overtime_pay,
                'other_earnings' => $detail->other_earnings,
                'gross_pay' => $detail->gross_pay,
            ],
            'deductions' => [
                'epf_ee' => $detail->epf_ee,
                'socso_ee' => $detail->socso_ee,
                'eis_ee' => $detail->eis_ee,
                'pcb' => $detail->pcb,
                'other_deductions' => $detail->other_deductions,
            ],
            'net_pay' => $detail->net_pay,
            'employer_costs' => [
                'epf_er' => $detail->epf_er,
                'socso_er' => $detail->socso_er,
                'eis_er' => $detail->eis_er,
                'hrdf' => $detail->hrdf,
            ],
        ]);
    }

    /**
     * Download payslip as PDF.
     */
    public function downloadPayslip(Request $request, int $detailId)
    {
        $payslipData = $this->payslip($request, $detailId);
        
        // TODO: Use DomPDF or similar to generate PDF from SlipGaji template
        // For now, return JSON
        
        return $payslipData;
    }
}
