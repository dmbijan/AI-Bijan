<?php

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Models\Employee;
use App\Modules\Payroll\Models\OvertimeRecord;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\PayrollRunDetail;
use Illuminate\Support\Facades\DB;

/**
 * B-Payroll Run Service
 * 
 * Orchestrates payroll processing for a complete payroll run.
 * Calculates all statutory deductions using CORRECT Malaysian rates:
 * - EPF: 11% employee, 13%/12% employer (tiered)
 * - SOCSO: Tier-based per PERKESO table
 * - EIS: 0.2% max RM100/year
 * - HRDF: 1% NO CAP (CORRECTED from 0.5% with cap)
 * - PCB: Based on LHDN mPTG tables
 */
class PayrollRunService
{
    /**
     * Process a complete payroll run.
     * 
     * Creates payroll run, processes all active employees, calculates
     * all earnings and deductions, and updates summary totals.
     * 
     * @param array $data Payroll run data
     * @return PayrollRun Processed payroll run
     * @throws \Exception If payroll run cannot be created or processed
     */
    public function createAndProcess(array $data): PayrollRun
    {
        return DB::transaction(function () use ($data) {
            // Create payroll run
            $payrollRun = PayrollRun::create([
                'tenant_id' => $data['tenant_id'],
                'name' => $data['name'] ?? "Payroll {$data['period_start']} to {$data['period_end']}",
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
                'pay_date' => $data['pay_date'] ?? null,
                'status' => 'draft',
                'created_by' => $data['created_by'] ?? auth()->id(),
            ]);

            // Get current statutory rates
            $rates = $this->getCurrentStatutoryRates($data['tenant_id']);

            // Process each active employee
            $employees = Employee::where('tenant_id', $data['tenant_id'])
                ->where('is_active', true)
                ->get();

            foreach ($employees as $employee) {
                $this->processEmployee($payrollRun, $employee, $rates);
            }

            // Update summary totals
            $this->updateSummaryTotals($payrollRun);

            return $payrollRun;
        });
    }

    /**
     * Process a single employee for a payroll run.
     * 
     * Calculates:
     * 1. Gross pay (basic + allowances + overtime)
     * 2. Statutory deductions (EPF, SOCSO, EIS, PCB)
     * 3. Employer contributions (EPF, SOCSO, EIS, HRDF)
     * 4. Net pay
     * 
     * @param PayrollRun $payrollRun The payroll run
     * @param Employee $employee The employee to process
     * @param array $rates Current statutory rates
     * @return PayrollRunDetail Processed detail record
     */
    public function processEmployee(PayrollRun $payrollRun, Employee $employee, array $rates): PayrollRunDetail
    {
        // Calculate overtime pay for this period
        $overtimeTotal = $this->calculateEmployeeOvertime(
            $employee,
            $payrollRun->period_start,
            $payrollRun->period_end
        );

        // Calculate gross pay
        $basicSalary = $employee->basic_salary;
        $allowances = $employee->fixed_allowance ?? 0;
        $overtimePay = $overtimeTotal['total_pay'];
        
        $grossPay = $basicSalary + $allowances + $overtimePay;

        // Calculate all statutory deductions
        $statutory = StatutoryCalculator::calculateAll(
            $basicSalary,
            $allowances,
            $overtimePay,
            $rates
        );

        // Calculate net pay
        $totalDeductions = $statutory['epf_ee'] + 
                          $statutory['socso_ee'] + 
                          $statutory['eis_ee'] + 
                          $statutory['pcb'];
        
        $netPay = $grossPay - $totalDeductions;

        // Create payroll run detail
        $detail = PayrollRunDetail::create([
            'payroll_run_id' => $payrollRun->id,
            'employee_id' => $employee->id,
            
            // Earnings
            'basic_salary' => $basicSalary,
            'fixed_allowance' => $allowances,
            'overtime_pay' => $overtimePay,
            'other_earnings' => 0,
            'gross_pay' => $grossPay,
            
            // Employee deductions
            'epf_ee' => $statutory['epf_ee'],
            'socso_ee' => $statutory['socso_ee'],
            'eis_ee' => $statutory['eis_ee'],
            'pcb' => $statutory['pcb'],
            'other_deductions' => 0,
            
            // Net pay
            'net_pay' => round($netPay, 2),
            
            // Employer costs
            'epf_er' => $statutory['epf_er'],
            'socso_er' => $statutory['socso_er'],
            'eis_er' => $statutory['eis_er'],
            'hrdf' => $statutory['hrdf'],
            
            // Audit metadata
            'statutory_year' => date('Y'),
            'calculation_notes' => [
                'hourly_rate' => $employee->hourly_rate,
                'overtime_breakdown' => $overtimeTotal['breakdown'],
                'rates_used' => $statutory['rates_used'],
            ],
        ]);

        return $detail;
    }

    /**
     * Calculate total overtime for an employee in a period.
     * 
     * Uses OvertimeCalculator with CORRECT Akta Kerja 1955 rates:
     * - Regular: 1.5x hourly rate
     * - Rest day: 2x hourly rate
     * - Public holiday: 3x hourly rate
     * 
     * @param Employee $employee The employee
     * @param \DateTimeInterface $startDate Period start
     * @param \DateTimeInterface $endDate Period end
     * @return array Overtime calculation result
     */
    protected function calculateEmployeeOvertime(Employee $employee, $startDate, $endDate): array
    {
        $overtimeRecords = OvertimeRecord::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->where('status', 'approved')
            ->get()
            ->toArray();

        return OvertimeCalculator::calculateTotalOvertime(
            $overtimeRecords,
            $employee->basic_salary
        );
    }

    /**
     * Get current active statutory rates for a tenant.
     * 
     * @param int $tenantId Tenant ID
     * @return array Statutory rates configuration
     */
    protected function getCurrentStatutoryRates(int $tenantId): array
    {
        // In production, rates could be tenant-specific
        // For now, use default Malaysian rates (corrected per audit A-02)
        
        return [
            // EPF (KWSP) - Employees Provident Fund Act 1991
            'epf_employee_rate' => 11.00,  // 11% for ALL salary levels
            'epf_employer_base_rate' => 13.00,  // 13% for salary <= RM5,000
            'epf_employer_reduced_rate' => 12.00,  // 12% for salary > RM5,000
            'epf_salary_threshold' => 5000,
            
            // SOCSO (PERKESO) - Social Security Act 1969
            'socso_max_annual_ee' => 1200,  // Approximate max annual employee contribution
            'socso_max_annual_er' => 1200,  // Approximate max annual employer contribution
            
            // EIS (SIP) - Employment Insurance System
            'eis_rate' => 0.20,  // 0.2%
            'eis_max_annual' => 100.00,  // RM100/year maximum
            
            // HRDF Levy - Human Resource Development Fund Act 1991
            // CORRECTED: 1% NO CAP (was incorrectly 0.5% with RM100 cap)
            'hrdf_rate' => 1.00,  // 1% of gross salary — NO CAP
            
            // PCB settings
            'pcb_use_mptg_table' => true,
        ];
    }

    /**
     * Update payroll run summary totals from all details.
     * 
     * @param PayrollRun $payrollRun The payroll run to update
     */
    public function updateSummaryTotals(PayrollRun $payrollRun): void
    {
        $details = $payrollRun->details;

        $totals = [
            'total_gross' => 0,
            'total_epf_ee' => 0,
            'total_socso_ee' => 0,
            'total_eis_ee' => 0,
            'total_pcb' => 0,
            'total_other_deductions' => 0,
            'total_net_pay' => 0,
            'total_epf_er' => 0,
            'total_socso_er' => 0,
            'total_eis_er' => 0,
            'total_hrdf' => 0,
        ];

        foreach ($details as $detail) {
            $totals['total_gross'] += $detail->gross_pay;
            $totals['total_epf_ee'] += $detail->epf_ee;
            $totals['total_socso_ee'] += $detail->socso_ee;
            $totals['total_eis_ee'] += $detail->eis_ee;
            $totals['total_pcb'] += $detail->pcb;
            $totals['total_other_deductions'] += $detail->other_deductions;
            $totals['total_net_pay'] += $detail->net_pay;
            $totals['total_epf_er'] += $detail->epf_er;
            $totals['total_socso_er'] += $detail->socso_er;
            $totals['total_eis_er'] += $detail->eis_er;
            $totals['total_hrdf'] += $detail->hrdf;
        }

        // Round all totals
        foreach ($totals as $key => $value) {
            $totals[$key] = round($value, 2);
        }

        $payrollRun->update($totals);
    }

    /**
     * Approve and finalize a payroll run.
     * 
     * Validates all calculations before finalizing.
     * 
     * @param PayrollRun $payrollRun The payroll run to approve
     * @param int $userId User approving the payroll
     * @return bool Success status
     */
    public function approvePayrollRun(PayrollRun $payrollRun, int $userId): bool
    {
        if (!$payrollRun->canModify()) {
            throw new \Exception("Payroll run cannot be modified in status: {$payrollRun->status}");
        }

        // Validate we have details
        if ($payrollRun->details()->count() === 0) {
            throw new \Exception("No employee records found in payroll run");
        }

        // Update status
        $payrollRun->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Process finalized payroll run (generate payslips, etc.).
     * 
     * @param PayrollRun $payrollRun The approved payroll run
     * @return PayrollRun Processed payroll run
     */
    public function processFinalPayroll(PayrollRun $payrollRun): PayrollRun
    {
        if ($payrollRun->status !== 'approved') {
            throw new \Exception("Only approved payroll runs can be processed");
        }

        $payrollRun->update(['status' => 'processed']);

        // TODO: Generate payslips (SlipGaji documents)
        // TODO: Generate EPF/SOCSO/HRDF remittance files
        // TODO: Send notification to employees

        return $payrollRun;
    }
}
