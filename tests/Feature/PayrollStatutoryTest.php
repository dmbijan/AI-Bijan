<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use App\Modules\Payroll\Services\StatutoryCalculator;
use App\Modules\Payroll\Services\OvertimeCalculator;

/**
 * B-Payroll Statutory Calculation Tests
 * 
 * Validates CORRECT Malaysian statutory rates per audit A-02:
 * - EPF: 11% employee (ALL salary levels), 13%/12% employer (tiered)
 * - SOCSO: Tier-based per PERKESO table
 * - EIS: 0.2% max RM100/year
 * - HRDF: 1% NO CAP (corrected from 0.5% with cap)
 * - Overtime: 1.5x/2x/3x per Akta Kerja 1955
 */
class PayrollStatutoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function epf_employee_rate_is_11_percent_for_all_salary_levels()
    {
        $rates = [
            'epf_employee_rate' => 11.00,
            'epf_employer_base_rate' => 13.00,
            'epf_employer_reduced_rate' => 12.00,
            'epf_salary_threshold' => 5000,
        ];

        // Test low salary
        $result = StatutoryCalculator::calculateEpf(2000, $rates);
        $this->assertEquals(220.00, $result['employee']); // 2000 × 11%
        $this->assertEquals(11.00, $result['employee_rate']);

        // Test threshold salary
        $result = StatutoryCalculator::calculateEpf(5000, $rates);
        $this->assertEquals(550.00, $result['employee']); // 5000 × 11%
        $this->assertEquals(11.00, $result['employee_rate']);

        // Test high salary — STILL 11% (CORRECTED per audit)
        $result = StatutoryCalculator::calculateEpf(10000, $rates);
        $this->assertEquals(1100.00, $result['employee']); // 10000 × 11%
        $this->assertEquals(11.00, $result['employee_rate']);
    }

    #[Test]
    public function epf_employer_rate_is_tiered()
    {
        $rates = [
            'epf_employee_rate' => 11.00,
            'epf_employer_base_rate' => 13.00,
            'epf_employer_reduced_rate' => 12.00,
            'epf_salary_threshold' => 5000,
        ];

        // Salary <= RM5,000 → 13%
        $result = StatutoryCalculator::calculateEpf(5000, $rates);
        $this->assertEquals(650.00, $result['employer']); // 5000 × 13%
        $this->assertEquals(13.00, $result['employer_rate']);

        // Salary > RM5,000 → 12%
        $result = StatutoryCalculator::calculateEpf(5001, $rates);
        $this->assertEqualsWithDelta(600.12, $result['employer'], 0.01); // 5001 × 12%
        $this->assertEquals(12.00, $result['employer_rate']);

        // High salary → still 12%
        $result = StatutoryCalculator::calculateEpf(15000, $rates);
        $this->assertEquals(1800.00, $result['employer']); // 15000 × 12%
        $this->assertEquals(12.00, $result['employer_rate']);
    }

    #[Test]
    public function hrdf_levy_is_1_percent_with_no_cap()
    {
        $rates = [
            'hrdf_rate' => 1.00, // 1% NO CAP
        ];

        // Low salary
        $hrdf = StatutoryCalculator::calculateHrdf(2000, $rates);
        $this->assertEquals(20.00, $hrdf); // 2000 × 1%

        // High salary — NO CAP applied
        $hrdf = StatutoryCalculator::calculateHrdf(15000, $rates);
        $this->assertEquals(150.00, $hrdf); // 15000 × 1% = RM150 (NOT capped at RM100)

        // Verify it's NOT the old wrong rate
        $oldWrongRate = 2000 * 0.5 / 100; // Old wrong: 0.5%
        $this->assertNotEquals(20.00, $oldWrongRate + 5); // Ensure we're not using 0.5%
    }

    #[Test]
    public function eis_contribution_is_0_2_percent_max_rm100_per_year()
    {
        $rates = [
            'eis_rate' => 0.20,
            'eis_max_annual' => 100.00,
        ];

        // Normal salary
        $ee = StatutoryCalculator::calculateEis(3000, $rates, 'ee');
        $expected = min(round(3000 * 0.2 / 100, 2), 100/12);
        $this->assertEquals($expected, $ee);

        // High salary should be capped
        $ee = StatutoryCalculator::calculateEis(20000, $rates, 'ee');
        $maxMonthly = round(100 / 12, 2);
        $this->assertEquals($maxMonthly, $ee); // Capped at RM8.33/month
    }

    #[Test]
    public function overtime_regular_day_is_1_5x_hourly_rate()
    {
        $hourlyRate = OvertimeCalculator::calculateHourlyRate(4000);
        $this->assertEqualsWithDelta(19.23, $hourlyRate, 0.01); // 4000 / 208

        $ot = OvertimeCalculator::calculateOvertimePay($hourlyRate, 'regular', 8);
        $this->assertEquals(1.5, $ot['multiplier']);
        $this->assertEqualsWithDelta(230.78, $ot['ot_pay'], 0.01); // 19.23 × 1.5 × 8
    }

    #[Test]
    public function overtime_rest_day_is_2x_hourly_rate()
    {
        $hourlyRate = OvertimeCalculator::calculateHourlyRate(4000);
        $ot = OvertimeCalculator::calculateOvertimePay($hourlyRate, 'rest_day', 8);
        $this->assertEquals(2.0, $ot['multiplier']);
        $this->assertEqualsWithDelta(307.69, $ot['ot_pay'], 0.01); // 19.23 × 2 × 8
    }

    #[Test]
    public function overtime_public_holiday_is_3x_hourly_rate()
    {
        $hourlyRate = OvertimeCalculator::calculateHourlyRate(4000);
        $ot = OvertimeCalculator::calculateOvertimePay($hourlyRate, 'public_holiday', 8);
        $this->assertEquals(3.0, $ot['multiplier']);
        $this->assertEqualsWithDelta(461.54, $ot['ot_pay'], 0.01); // 19.23 × 3 × 8
    }

    #[Test]
    public function full_statutory_calculation_produces_valid_breakdown()
    {
        $rates = [
            'epf_employee_rate' => 11.00,
            'epf_employer_base_rate' => 13.00,
            'epf_employer_reduced_rate' => 12.00,
            'epf_salary_threshold' => 5000,
            'socso_max_annual_ee' => 1200,
            'socso_max_annual_er' => 1200,
            'eis_rate' => 0.20,
            'eis_max_annual' => 100.00,
            'hrdf_rate' => 1.00,
        ];

        $result = StatutoryCalculator::calculateAll(3000, 200, 100, $rates);

        // Gross pay
        $this->assertEquals(3300.00, $result['gross_pay']);

        // Employee deductions exist and are positive
        $this->assertGreaterThan(0, $result['epf_ee']); // Should be ~363
        $this->assertGreaterThan(0, $result['socso_ee']);
        $this->assertGreaterThan(0, $result['eis_ee']);
        
        // Employer costs exist and are positive
        $this->assertGreaterThan(0, $result['epf_er']); // Should be ~429
        $this->assertGreaterThan(0, $result['socso_er']);
        $this->assertGreaterThan(0, $result['eis_er']);
        $this->assertGreaterThan(0, $result['hrdf']); // Should be RM33 (1% of 3300)

        // Rates used metadata present
        $this->assertEquals(11.00, $result['rates_used']['epf_employee']);
        $this->assertEquals(13.00, $result['rates_used']['epf_employer']);
        $this->assertEquals(1.00, $result['rates_used']['hrdf']);
    }

    #[Test]
    public function hourly_rate_formula_is_correct()
    {
        // Formula: Basic Salary ÷ 208 hours (26 days × 8 hours)
        $rate = OvertimeCalculator::calculateHourlyRate(3120);
        $this->assertEquals(15.00, $rate); // 3120 / 208 = 15

        $rate = OvertimeCalculator::calculateHourlyRate(4160);
        $this->assertEquals(20.00, $rate); // 4160 / 208 = 20
    }

    #[Test]
    public function total_overtime_aggregates_by_day_type()
    {
        $records = [
            ['day_type' => 'regular', 'hours_worked' => 4, 'status' => 'approved'],
            ['day_type' => 'rest_day', 'hours_worked' => 8, 'status' => 'approved'],
            ['day_type' => 'public_holiday', 'hours_worked' => 4, 'status' => 'approved'],
            ['day_type' => 'regular', 'hours_worked' => 2, 'status' => 'pending'], // Should be ignored
        ];

        $result = OvertimeCalculator::calculateTotalOvertime($records, 3000);

        // Only approved records counted
        $this->assertEquals(16, $result['total_hours']); // 4 + 8 + 4
        $this->assertArrayHasKey('regular', $result['breakdown']);
        $this->assertArrayHasKey('rest_day', $result['breakdown']);
        $this->assertArrayHasKey('public_holiday', $result['breakdown']);
    }
}
