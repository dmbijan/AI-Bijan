<?php

namespace App\Modules\POS\Services;

use App\Modules\POS\Models\CashShift;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashShiftService
{
    /**
     * Open a new cash shift for a register.
     */
    public function openShift(int $registerId, int $userId, float $openingCash = 200.00, int $tenantId): CashShift
    {
        // Check if there's already an open shift for this register
        $existingOpen = CashShift::forTenant($tenantId)
            ->where('register_id', $registerId)
            ->where('status', 'open')
            ->first();

        if ($existingOpen) {
            throw ValidationException::withMessages([
                'base' => ['There is already an open shift for this register. Please close it first.'],
            ]);
        }

        return DB::transaction(function () use ($registerId, $userId, $openingCash, $tenantId) {
            $shift = CashShift::create([
                'tenant_id' => $tenantId,
                'register_id' => $registerId,
                'opened_by' => $userId,
                'status' => 'open',
                'opening_cash' => $openingCash,
                'shift_date' => now()->format('Y-m-d'),
                'opened_at' => now(),
            ]);

            return $shift;
        });
    }

    /**
     * Get the current open shift for a register.
     */
    public function getOpenShift(int $registerId, int $tenantId): ?CashShift
    {
        return CashShift::forTenant($tenantId)
            ->where('register_id', $registerId)
            ->where('status', 'open')
            ->first();
    }

    /**
     * Close a cash shift.
     */
    public function closeShift(int $shiftId, array $closureData, int $userId, int $tenantId): CashShift
    {
        $shift = $this->getShift($shiftId, $tenantId);

        if (!$shift || $shift->status !== 'open') {
            throw ValidationException::withMessages([
                'shift_id' => ['Shift not found or already closed.'],
            ]);
        }

        return DB::transaction(function () use ($shift, $closureData, $userId, $tenantId) {
            $closingCash = $closureData['closing_cash'] ?? 0;
            $expectedCash = $this->calculateExpectedCash($shift);
            $cashDiscrepancy = $closingCash - $expectedCash;

            $shift->update([
                'status' => 'closed',
                'closed_by' => $userId,
                'closing_cash' => $closingCash,
                'expected_cash' => $expectedCash,
                'cash_discrepancy' => $cashDiscrepancy,
                'closed_at' => now(),
                'notes' => $closureData['notes'] ?? null,
            ]);

            return $shift;
        });
    }

    /**
     * Calculate expected cash in the shift.
     */
    public function calculateExpectedCash(CashShift $shift): float
    {
        $salesTotal = \App\Modules\POS\Models\POSSale::where('cash_shift_id', $shift->id)
            ->where('status', 'completed')
            ->sum('total_amount');

        $payments = \App\Modules\POS\Models\POSSalePayment::join('pos_sales', 'pos_sale_payments.sale_id', '=', 'pos_sales.id')
            ->where('pos_sales.cash_shift_id', $shift->id)
            ->where('pos_sales.status', 'completed')
            ->where('pos_sale_payments.payment_method', 'cash')
            ->sum('pos_sale_payments.amount');

        return $shift->opening_cash + $payments;
    }

    /**
     * List cash shifts with filters.
     */
    public function listShifts(int $tenantId, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = CashShift::forTenant($tenantId)
            ->with(['register', 'opener', 'closer']);

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['register_id'])) {
            $query->where('register_id', $filters['register_id']);
        }

        if (isset($filters['date'])) {
            $query->where('shift_date', $filters['date']);
        }

        return $query->orderBy('opened_at', 'desc')->paginate(50);
    }

    /**
     * Get a single shift by ID.
     */
    public function getShift(int $shiftId, int $tenantId): ?CashShift
    {
        return CashShift::forTenant($tenantId)
            ->with(['register', 'opener', 'closer', 'sales'])
            ->find($shiftId);
    }

    /**
     * Lock a closed shift (prevent further changes).
     */
    public function lockShift(int $shiftId, int $userId, int $tenantId): ?CashShift
    {
        $shift = $this->getShift($shiftId, $tenantId);

        if (!$shift || $shift->status !== 'closed') {
            return null;
        }

        $shift->update(['status' => 'locked']);

        return $shift;
    }

    /**
     * Get shift summary statistics.
     */
    public function getSummary(int $tenantId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = CashShift::forTenant($tenantId);

        if ($startDate) {
            $query->where('shift_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('shift_date', '<=', $endDate);
        }

        $shifts = $query->get();

        return [
            'total_shifts' => $shifts->count(),
            'open_shifts' => $shifts->where('status', 'open')->count(),
            'closed_shifts' => $shifts->where('status', 'closed')->count(),
            'locked_shifts' => $shifts->where('status', 'locked')->count(),
            'total_discrepancy' => $shifts->sum('cash_discrepancy'),
            'avg_shift_duration' => $shifts->filter(fn($s) => $s->closed_at)->avg(fn($s) => $s->opened_at->diffInMinutes($s->closed_at)),
        ];
    }
}
