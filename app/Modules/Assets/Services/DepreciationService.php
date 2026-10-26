<?php

namespace App\Modules\Assets\Services;

use App\Modules\Assets\Models\Asset;
use App\Modules\Assets\Models\DepreciationSchedule;
use Illuminate\Support\Facades\DB;

class DepreciationService
{
    /**
     * Generate depreciation schedule for an asset.
     */
    public function generateDepreciationSchedule(Asset $asset): void
    {
        // Delete existing unposted schedules
        $asset->depreciationSchedules()->where('is_posted', false)->delete();

        $schedule = $this->calculateDepreciation($asset);

        foreach ($schedule as $period) {
            DepreciationSchedule::create([
                'asset_id' => $asset->id,
                'tenant_id' => $asset->tenant_id,
                'year' => $period['year'],
                'period' => $period['period'],
                'opening_value' => $period['opening_value'],
                'depreciation_amount' => $period['depreciation_amount'],
                'closing_value' => $period['closing_value'],
                'calculated_at' => now(),
                'is_posted' => false,
            ]);
        }
    }

    /**
     * Calculate depreciation schedule for an asset.
     */
    public function calculateDepreciation(Asset $asset): array
    {
        $depreciableAmount = $asset->purchase_price - $asset->salvage_value;
        $schedule = [];

        switch ($asset->depreciation_method) {
            case 'straight_line':
                $schedule = $this->calculateStraightLine($asset, $depreciableAmount);
                break;

            case 'declining_balance':
                $schedule = $this->calculateDecliningBalance($asset, $depreciableAmount);
                break;

            case 'sum_years_digits':
                $schedule = $this->calculateSumYearsDigits($asset, $depreciableAmount);
                break;

            case 'units_of_production':
                // Requires additional data - fallback to straight line
                $schedule = $this->calculateStraightLine($asset, $depreciableAmount);
                break;
        }

        return $schedule;
    }

    /**
     * Calculate straight-line depreciation.
     */
    protected function calculateStraightLine(Asset $asset, float $depreciableAmount): array
    {
        $annualDepreciation = $depreciableAmount / max($asset->useful_life_years, 1);
        $monthlyDepreciation = $annualDepreciation / 12;
        $schedule = [];

        $currentValue = $asset->purchase_price;
        $startYear = (int) date('Y', strtotime($asset->purchase_date));
        $startMonth = (int) date('m', strtotime($asset->purchase_date));

        for ($year = 0; $year < $asset->useful_life_years; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                // Skip periods before purchase date
                if ($year === 0 && $month < $startMonth) {
                    continue;
                }

                $openingValue = $currentValue;
                $depreciationAmount = min($monthlyDepreciation, $currentValue - $asset->salvage_value);
                $closingValue = $openingValue - $depreciationAmount;

                $schedule[] = [
                    'year' => $startYear + $year,
                    'period' => $month,
                    'opening_value' => $openingValue,
                    'depreciation_amount' => $depreciationAmount,
                    'closing_value' => max($closingValue, $asset->salvage_value),
                ];

                $currentValue = $closingValue;

                // Stop if we've reached salvage value
                if ($currentValue <= $asset->salvage_value) {
                    break 2;
                }
            }
        }

        return $schedule;
    }

    /**
     * Calculate declining balance depreciation.
     */
    protected function calculateDecliningBalance(Asset $asset, float $depreciableAmount): array
    {
        $rate = 2 / max($asset->useful_life_years, 1); // Double declining
        $schedule = [];

        $currentValue = $asset->purchase_price;
        $startYear = (int) date('Y', strtotime($asset->purchase_date));
        $startMonth = (int) date('m', strtotime($asset->purchase_date));

        for ($year = 0; $year < $asset->useful_life_years; $year++) {
            $annualDepreciation = $currentValue * $rate;
            $monthlyDepreciation = $annualDepreciation / 12;

            for ($month = 1; $month <= 12; $month++) {
                if ($year === 0 && $month < $startMonth) {
                    continue;
                }

                $openingValue = $currentValue;
                $depreciationAmount = min($monthlyDepreciation, $currentValue - $asset->salvage_value);
                $closingValue = $openingValue - $depreciationAmount;

                $schedule[] = [
                    'year' => $startYear + $year,
                    'period' => $month,
                    'opening_value' => $openingValue,
                    'depreciation_amount' => $depreciationAmount,
                    'closing_value' => max($closingValue, $asset->salvage_value),
                ];

                $currentValue = $closingValue;

                if ($currentValue <= $asset->salvage_value) {
                    break 2;
                }
            }
        }

        return $schedule;
    }

    /**
     * Calculate sum-of-years-digits depreciation.
     */
    protected function calculateSumYearsDigits(Asset $asset, float $depreciableAmount): array
    {
        $life = max($asset->useful_life_years, 1);
        $sumOfYears = ($life * ($life + 1)) / 2;
        $schedule = [];

        $currentValue = $asset->purchase_price;
        $startYear = (int) date('Y', strtotime($asset->purchase_date));
        $startMonth = (int) date('m', strtotime($asset->purchase_date));

        for ($year = 1; $year <= $life; $year++) {
            $remainingLife = $life - $year + 1;
            $annualDepreciation = ($depreciableAmount * $remainingLife) / $sumOfYears;
            $monthlyDepreciation = $annualDepreciation / 12;

            for ($month = 1; $month <= 12; $month++) {
                if ($year === 1 && $month < $startMonth) {
                    continue;
                }

                $openingValue = $currentValue;
                $depreciationAmount = min($monthlyDepreciation, $currentValue - $asset->salvage_value);
                $closingValue = $openingValue - $depreciationAmount;

                $schedule[] = [
                    'year' => $startYear + $year - 1,
                    'period' => $month,
                    'opening_value' => $openingValue,
                    'depreciation_amount' => $depreciationAmount,
                    'closing_value' => max($closingValue, $asset->salvage_value),
                ];

                $currentValue = $closingValue;

                if ($currentValue <= $asset->salvage_value) {
                    break 2;
                }
            }
        }

        return $schedule;
    }

    /**
     * Post a year's depreciation schedules.
     */
    public function postDepreciation(int $assetId, int $year, int $tenantId): int
    {
        $count = DepreciationSchedule::forTenant($tenantId)
            ->where('asset_id', $assetId)
            ->where('year', $year)
            ->where('is_posted', false)
            ->update(['is_posted' => true]);

        return $count;
    }

    /**
     * Get total depreciation for a period.
     */
    public function getDepreciationForPeriod(int $tenantId, int $year, int $month = null): float
    {
        $query = DepreciationSchedule::forTenant($tenantId)
            ->where('year', $year)
            ->where('is_posted', true);

        if ($month) {
            $query->where('period', $month);
        }

        return (float) $query->sum('depreciation_amount');
    }
}
