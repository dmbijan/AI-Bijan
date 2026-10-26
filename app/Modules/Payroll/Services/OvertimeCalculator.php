<?php

namespace App\Modules\Payroll\Services;

/**
 * B-Payroll Overtime Calculator Service
 * 
 * Overtime calculation per Akta Kerja 1955 (Employment Act 1955):
 * 
 * Section 67A - Overtime:
 * - Regular working day: 1.5x hourly rate for hours worked beyond normal working hours
 * - Rest day: 2x hourly rate for work performed on rest day
 * - Public Holiday: 3x hourly rate for work performed on public holiday
 * 
 * Hourly rate calculation:
 * - Monthly rate employee: Basic Salary ÷ 26 ÷ 8 = Hourly Rate
 * - Daily rate employee: Daily Rate ÷ 8 = Hourly Rate
 */
class OvertimeCalculator
{
    /**
     * Standard working hours per month for calculation.
     * Per Employment Act 1955: 8 hours/day × 26 days = 208 hours/month
     */
    const HOURS_PER_MONTH = 208;
    const HOURS_PER_DAY = 8;

    /**
     * Overtime multipliers per Akta Kerja 1955.
     */
    const MULTIPLIER_REGULAR = 1.5;   // 1.5x hourly rate
    const MULTIPLIER_REST_DAY = 2.0;  // 2x hourly rate
    const MULTIPLIER_PUBLIC_HOLIDAY = 3.0; // 3x hourly rate

    /**
     * Calculate hourly rate from monthly basic salary.
     * 
     * Formula: Basic Salary ÷ 26 ÷ 8
     * 
     * @param float $basicSalary Monthly basic salary in RM
     * @return float Hourly rate
     */
    public static function calculateHourlyRate(float $basicSalary): float
    {
        // Simpan 4 tempat perpuluhan; pembundaran 2 tempat hanya untuk paparan.
        return round($basicSalary / self::HOURS_PER_MONTH, 4);
    }

    /**
     * Calculate overtime pay based on day type and hours worked.
     * 
     * @param float $hourlyRate Base hourly rate
     * @param string $dayType 'regular', 'rest_day', or 'public_holiday'
     * @param float $hoursWorked Hours worked for overtime
     * @return array Calculation details
     */
    public static function calculateOvertimePay(float $hourlyRate, string $dayType, float $hoursWorked): array
    {
        $multiplier = match ($dayType) {
            'rest_day' => self::MULTIPLIER_REST_DAY,
            'public_holiday' => self::MULTIPLIER_PUBLIC_HOLIDAY,
            default => self::MULTIPLIER_REGULAR, // regular day
        };

        $otPay = round($hourlyRate * $multiplier * $hoursWorked, 2);

        return [
            'hourly_rate' => $hourlyRate,
            'day_type' => $dayType,
            'hours_worked' => $hoursWorked,
            'multiplier' => $multiplier,
            'ot_pay' => $otPay,
            'formula' => "{$hourlyRate} × {$multiplier} × {$hoursWorked} = {$otPay}",
        ];
    }

    /**
     * Calculate total overtime pay for an employee in a payroll period.
     * 
     * Aggregates all approved overtime records and calculates total OT pay.
     * 
     * @param array $overtimeRecords Array of overtime records with day_type, hours_worked
     * @param float $basicSalary Monthly basic salary
     * @return array Total OT breakdown by day type
     */
    public static function calculateTotalOvertime(array $overtimeRecords, float $basicSalary): array
    {
        $hourlyRate = self::calculateHourlyRate($basicSalary);

        $breakdown = [
            'regular' => ['hours' => 0, 'pay' => 0],
            'rest_day' => ['hours' => 0, 'pay' => 0],
            'public_holiday' => ['hours' => 0, 'pay' => 0],
        ];

        foreach ($overtimeRecords as $record) {
            if ($record['status'] !== 'approved') {
                continue; // Only count approved OT
            }

            $dayType = $record['day_type'] ?? 'regular';
            $hours = (float) $record['hours_worked'];

            $calc = self::calculateOvertimePay($hourlyRate, $dayType, $hours);

            $breakdown[$dayType]['hours'] += $hours;
            $breakdown[$dayType]['pay'] += $calc['ot_pay'];
        }

        // Round totals
        foreach ($breakdown as &$type) {
            $type['hours'] = round($type['hours'], 2);
            $type['pay'] = round($type['pay'], 2);
        }

        $totalHours = $breakdown['regular']['hours'] + $breakdown['rest_day']['hours'] + $breakdown['public_holiday']['hours'];
        $totalPay = $breakdown['regular']['pay'] + $breakdown['rest_day']['pay'] + $breakdown['public_holiday']['pay'];

        return [
            'hourly_rate' => $hourlyRate,
            'breakdown' => $breakdown,
            'total_hours' => round($totalHours, 2),
            'total_pay' => round($totalPay, 2),
        ];
    }
}
