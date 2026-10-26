<?php

namespace App\Modules\Payroll\Services;

/**
 * B-Payroll Statutory Calculator Service
 * 
 * Malaysian statutory contribution calculations with CORRECT rates per audit A-02:
 * 
 * EPF (KWSP) - Employees Provident Fund Act 1991:
 *   Employee: 11% for ALL salary levels (NOT "9% for >= RM5,000")
 *   Employer: 13% for salary <= RM5,000, 12% for salary > RM5,000
 * 
 * SOCSO (PERKESO) - Social Security Act 1969:
 *   Employee & Employer: Tier-based contribution tables
 * 
 * EIS (SIP) - Employment Insurance System:
 *   Employee: 0.2% of gross salary (max RM100/year)
 *   Employer: Matches employee rate
 * 
 * HRDF Levy - Human Resource Development Fund Act 1991:
 *   Employer: 1% of gross salary — NO CAP (was incorrectly 0.5% with RM100 cap)
 * 
 * PCB - Monthly Tax Deduction:
 *   Based on LHDN mPTG tables (lookup, not formula)
 */
class StatutoryCalculator
{
    /**
     * Calculate EPF contributions.
     * 
     * @param float $grossSalary Gross monthly salary
     * @param array $rates Current statutory rates
     * @return array ['employee' => float, 'employer' => float]
     */
    public static function calculateEpf(float $grossSalary, array $rates): array
    {
        // Employee contribution: 11% for ALL salary levels (CORRECTED per audit)
        $employeeRate = $rates['epf_employee_rate'] ?? 11.00;
        $employeeContribution = ($grossSalary * $employeeRate) / 100;

        // Employer contribution: tiered based on salary threshold
        $threshold = $rates['epf_salary_threshold'] ?? 5000;
        $baseRate = $rates['epf_employer_base_rate'] ?? 13.00;
        $reducedRate = $rates['epf_employer_reduced_rate'] ?? 12.00;

        if ($grossSalary <= $threshold) {
            $employerRate = $baseRate; // 13%
        } else {
            $employerRate = $reducedRate; // 12%
        }
        $employerContribution = ($grossSalary * $employerRate) / 100;

        return [
            'employee' => round($employeeContribution, 2),
            'employer' => round($employerContribution, 2),
            'employee_rate' => $employeeRate,
            'employer_rate' => $employerRate,
            'salary_threshold' => $threshold,
        ];
    }

    /**
     * Calculate SOCSO/PERKESO contribution.
     * 
     * Uses simplified tier-based calculation.
     * For production, should reference official PERKESO contribution table.
     * 
     * @param float $monthlySalary Monthly gross salary
     * @param array $rates Current statutory rates
     * @param string $portion 'ee' for employee, 'er' for employer
     * @return float Contribution amount
     */
    public static function calculateSocso(float $monthlySalary, array $rates, string $portion = 'ee'): float
    {
        // Simplified calculation - in production, use official PERKESO table lookup
        // This is a placeholder; actual SOCSO uses complex tier tables
        
        $maxAnnual = $rates["socso_max_annual_{$portion}"] ?? 0;
        
        if ($maxAnnual <= 0) {
            // Fallback: approximate calculation
            // SOCSO contribution is roughly 1% of salary for most tiers
            return min(round($monthlySalary * 0.01, 2), $maxAnnual);
        }

        // Convert annual max to monthly approximation
        $maxMonthly = $maxAnnual / 12;
        
        // Simplified: SOCSO has a maximum insurable wage
        $maxInsurableWage = 5000; // Approximate
        $insurableSalary = min($monthlySalary, $maxInsurableWage);
        
        // Rough estimate: ~1% of insurable salary
        return min(round($insurableSalary * 0.01, 2), $maxMonthly);
    }

    /**
     * Calculate EIS/SIP (Employment Insurance System) contribution.
     * 
     * Employee pays 0.2% of gross salary, maximum RM100 per year.
     * Employer matches the employee contribution.
     * 
     * @param float $monthlySalary Monthly gross salary
     * @param array $rates Current statutory rates
     * @param string $portion 'ee' for employee, 'er' for employer
     * @return float Contribution amount
     */
    public static function calculateEis(float $monthlySalary, array $rates, string $portion = 'ee'): float
    {
        $rate = $rates['eis_rate'] ?? 0.20; // 0.2%
        $maxAnnual = $rates['eis_max_annual'] ?? 100.00; // RM100/year max
        
        $monthlyContribution = ($monthlySalary * $rate) / 100;
        $maxMonthly = round($maxAnnual / 12, 2);
        
        return min(round($monthlyContribution, 2), $maxMonthly);
    }

    /**
     * Calculate HRDF Levy.
     * 
     * CORRECTED per audit A-02:
     * - OLD (WRONG): 0.5% with RM100/month cap
     * - NEW (CORRECT): 1% of gross salary — NO CAP
     * 
     * Reference: HRDF Act 1991
     * 
     * @param float $monthlySalary Monthly gross salary
     * @param array $rates Current statutory rates
     * @return float Levy amount (no cap)
     */
    public static function calculateHrdf(float $monthlySalary, array $rates): float
    {
        // CORRECTED: 1% with NO cap
        $rate = $rates['hrdf_rate'] ?? 1.00;
        
        return round(($monthlySalary * $rate) / 100, 2);
    }

    /**
     * Calculate all statutory deductions for an employee.
     * 
     * @param float $basicSalary Basic monthly salary
     * @param float $allowances Fixed allowances
     * @param float $overtimePay Overtime pay earned
     * @param array $rates Current statutory rates
     * @return array Complete statutory breakdown
     */
    public static function calculateAll(float $basicSalary, float $allowances, float $overtimePay, array $rates): array
    {
        $grossPay = $basicSalary + $allowances + $overtimePay;

        // EPF (both employee and employer)
        $epf = self::calculateEpf($grossPay, $rates);

        // SOCSO (both portions)
        $socso_ee = self::calculateSocso($grossPay, $rates, 'ee');
        $socso_er = self::calculateSocso($grossPay, $rates, 'er');

        // EIS (both portions)
        $eis_ee = self::calculateEis($grossPay, $rates, 'ee');
        $eis_er = self::calculateEis($grossPay, $rates, 'er');

        // HRDF (employer only, NO CAP)
        $hrdf = self::calculateHrdf($grossPay, $rates);

        // PCB would be looked up from LHDN table based on annual income projection
        // This is a simplified placeholder
        $pcb = self::estimatePcb($grossPay, $rates);

        return [
            'gross_pay' => round($grossPay, 2),
            
            // Employee deductions
            'epf_ee' => $epf['employee'],
            'socso_ee' => $socso_ee,
            'eis_ee' => $eis_ee,
            'pcb' => $pcb,
            
            // Employer costs
            'epf_er' => $epf['employer'],
            'socso_er' => $socso_er,
            'eis_er' => $eis_er,
            'hrdf' => $hrdf,
            
            // Rate metadata for audit trail
            'rates_used' => [
                'epf_employee' => $epf['employee_rate'] ?? null,
                'epf_employer' => $epf['employer_rate'] ?? null,
                'eis' => $rates['eis_rate'] ?? null,
                'hrdf' => $rates['hrdf_rate'] ?? null,
            ],
        ];
    }

    /**
     * Estimate PCB using simplified LHDN table lookup.
     * 
     * In production, this should reference the official mPTG (Maklumat Potongan Cukai Berjadual) table.
     * This is a rough approximation for demonstration purposes.
     * 
     * @param float $monthlyGross Monthly gross salary
     * @param array $rates Rates config
     * @return float Estimated PCB deduction
     */
    protected static function estimatePcb(float $monthlyGross, array $rates): float
    {
        // Simplified PCB estimation
        // Actual PCB depends on:
        // - Monthly gross income
        // - Age group
        // - Reliefs claimed (EPF, SOCSO, etc.)
        // - Tax bracket
        
        $annualProjection = $monthlyGross * 12;
        
        // Basic tax-free threshold: RM34,000/year (individual relief RM9,000 + personal relief RM2,400 ≈ RM11,400)
        // Taxable income after basic reliefs
        $taxableIncome = max(0, $annualProjection - 11400);
        
        // Simplified progressive tax calculation (Malaysia individual tax rates 2026)
        $tax = 0;
        
        if ($taxableIncome > 1000000) {
            $tax = ($taxableIncome - 1000000) * 0.30 + self::calculateTaxBracket(1000000, $rates);
        } elseif ($taxableIncome > 700000) {
            $tax = ($taxableIncome - 700000) * 0.30 + self::calculateTaxBracket(700000, $rates);
        } elseif ($taxableIncome > 400000) {
            $tax = ($taxableIncome - 400000) * 0.28 + self::calculateTaxBracket(400000, $rates);
        } elseif ($taxableIncome > 300000) {
            $tax = ($taxableIncome - 300000) * 0.26 + self::calculateTaxBracket(300000, $rates);
        } elseif ($taxableIncome > 250000) {
            $tax = ($taxableIncome - 250000) * 0.24 + self::calculateTaxBracket(250000, $rates);
        } elseif ($taxableIncome > 200000) {
            $tax = ($taxableIncome - 200000) * 0.21 + self::calculateTaxBracket(200000, $rates);
        } elseif ($taxableIncome > 100000) {
            $tax = ($taxableIncome - 100000) * 0.18 + self::calculateTaxBracket(100000, $rates);
        } elseif ($taxableIncome > 60000) {
            $tax = ($taxableIncome - 60000) * 0.15 + self::calculateTaxBracket(60000, $rates);
        } elseif ($taxableIncome > 35000) {
            $tax = ($taxableIncome - 35000) * 0.10 + self::calculateTaxBracket(35000, $rates);
        } elseif ($taxableIncome > 25000) {
            $tax = ($taxableIncome - 25000) * 0.05 + self::calculateTaxBracket(25000, $rates);
        } else {
            $tax = 0; // Below taxable threshold
        }
        
        // Monthly PCB before EPF deduction relief
        $monthlyPcb = $tax / 12;
        
        // EPF provides tax relief, reducing PCB
        // Simplified: deduct EPF employee contribution effect
        $epf_relief = ($monthlyGross * ($rates['epf_employee_rate'] ?? 11)) / 100 * 0.15; // Rough estimate
        
        return max(0, round($monthlyPcb - $epf_relief, 2));
    }

    /**
     * Helper for progressive tax bracket calculation.
     */
    protected static function calculateTaxBracket(int $incomeThreshold, array $rates): float
    {
        // Pre-calculated cumulative tax at each threshold
        $brackets = [
            25000 => 0,
            35000 => 50,
            60000 => 275,
            100000 => 875,
            200000 => 3475,
            250000 => 5525,
            300000 => 8025,
            400000 => 14525,
            700000 => 36525,
            1000000 => 68525,
        ];
        
        return $brackets[$incomeThreshold] ?? 0;
    }
}
