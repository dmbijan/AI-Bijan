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
     *
     * BAB 88 / T-80: kini mengambil kira tender tunai (pembayaran berpisah) dan
     * pergerakan tunai (drop/expense/pickup). Corak B-POS:
     *
     *   expected = float + tunai diterima - baki dipulangkan + pergerakan bersih
     *
     * ⚠️ Tunai dikira daripada `cash_received` (wang yang MASUK laci), bukan
     * `amount`. Jika pelanggan bayar RM50 untuk jualan RM42.50, laci menerima
     * RM50 dan memulangkan RM7.50 — jumlah bersih masih RM42.50.
     */
    public function calculateExpectedCash(CashShift $shift): float
    {
        $tunaiDiterima = (float) \App\Modules\POS\Models\POSSaleTender::query()
            ->whereHas('sale', fn ($q) => $q
                ->where('cash_shift_id', $shift->id)
                ->where('status', 'completed'))
            ->where('method', \App\Modules\POS\Models\POSSaleTender::METHOD_CASH)
            ->sum('cash_received');

        $bakiDipulangkan = (float) \App\Modules\POS\Models\POSSaleTender::query()
            ->whereHas('sale', fn ($q) => $q
                ->where('cash_shift_id', $shift->id)
                ->where('status', 'completed'))
            ->where('method', \App\Modules\POS\Models\POSSaleTender::METHOD_CASH)
            ->sum('change_amount');

        // Rekod lama (sebelum T-79) hanya ada pos_sale_payments.
        $tunaiLama = (float) \App\Modules\POS\Models\POSSalePayment::join('pos_sales', 'pos_sale_payments.sale_id', '=', 'pos_sales.id')
            ->where('pos_sales.cash_shift_id', $shift->id)
            ->where('pos_sales.status', 'completed')
            ->where('pos_sale_payments.payment_method', 'cash')
            ->sum('pos_sale_payments.amount');

        $pergerakan = (float) $shift->cashMovements->sum(fn ($m) => $m->kesanPadaLaci());

        return round(
            (float) $shift->opening_cash + $tunaiLama + $tunaiDiterima - $bakiDipulangkan + $pergerakan,
            2
        );
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

        $shift->update(['status' => 'locked', 'locked_at' => now()]);

        return $shift;
    }

    // ===============================================================
    // BAB 88 / T-80 — PERGERAKAN TUNAI & RINGKASAN SYIF
    // Dipindah daripada B-POS `ShiftCashMovement` + `CashierShiftService`.
    // ===============================================================

    /**
     * Rekod pergerakan tunai dalam syif yang masih terbuka.
     *
     * Syif tertutup/terkunci TIDAK menerima pergerakan — jika tidak, jumlah
     * tunai yang sudah diaudit boleh berubah selepas itu.
     */
    public function recordCashMovement(
        int $shiftId,
        int $tenantId,
        string $jenis,
        float $amaun,
        ?string $sebab = null,
        ?int $userId = null
    ): \App\Modules\POS\Models\POSShiftCashMovement {
        $jenisSah = [
            \App\Modules\POS\Models\POSShiftCashMovement::TYPE_DROP,
            \App\Modules\POS\Models\POSShiftCashMovement::TYPE_PICKUP,
            \App\Modules\POS\Models\POSShiftCashMovement::TYPE_EXPENSE,
            \App\Modules\POS\Models\POSShiftCashMovement::TYPE_ADJUSTMENT,
        ];

        if (! in_array($jenis, $jenisSah, true)) {
            throw ValidationException::withMessages(['type' => ['Jenis pergerakan tunai tidak sah.']]);
        }

        if ($amaun <= 0) {
            throw ValidationException::withMessages(['amount' => ['Amaun mesti melebihi sifar.']]);
        }

        $shift = $this->getShift($shiftId, $tenantId);

        if (! $shift || $shift->status !== 'open') {
            throw ValidationException::withMessages([
                'shift_id' => ['Pergerakan tunai hanya untuk syif yang masih terbuka.'],
            ]);
        }

        return $shift->cashMovements()->create([
            'tenant_id' => $tenantId,
            'type' => $jenis,
            'amount' => round($amaun, 2),
            'reason' => $sebab,
            'created_by' => $userId,
        ]);
    }

    /**
     * Ringkasan tunai syif — untuk penutupan syif & paparan pengurus.
     *
     * @return array<string, mixed>
     */
    public function ringkasan(CashShift $shift): array
    {
        $jualan = \App\Modules\POS\Models\POSSale::where('cash_shift_id', $shift->id)
            ->where('status', 'completed')
            ->get();

        $tender = \App\Modules\POS\Models\POSSaleTender::query()
            ->whereHas('sale', fn ($q) => $q
                ->where('cash_shift_id', $shift->id)
                ->where('status', 'completed'))
            ->get();

        $tunaiDiterima = (float) $tender->where('method', \App\Modules\POS\Models\POSSaleTender::METHOD_CASH)->sum('cash_received');
        $bakiDipulangkan = (float) $tender->where('method', \App\Modules\POS\Models\POSSaleTender::METHOD_CASH)->sum('change_amount');
        $bukanTunai = (float) $tender->where('method', '!=', \App\Modules\POS\Models\POSSaleTender::METHOD_CASH)->sum('amount');

        $pergerakanKeluar = (float) $shift->cashMovements
            ->filter(fn ($m) => $m->kesanPadaLaci() < 0)
            ->sum(fn ($m) => abs($m->kesanPadaLaci()));
        $pergerakanMasuk = (float) $shift->cashMovements
            ->filter(fn ($m) => $m->kesanPadaLaci() > 0)
            ->sum(fn ($m) => $m->kesanPadaLaci());

        return [
            'opening_cash' => round((float) $shift->opening_cash, 2),
            'cash_sales_total' => round($tunaiDiterima - $bakiDipulangkan, 2),
            'cash_received_total' => round($tunaiDiterima, 2),
            'change_total' => round($bakiDipulangkan, 2),
            'non_cash_sales_total' => round($bukanTunai, 2),
            'cash_out' => round($pergerakanKeluar, 2),
            'cash_in' => round($pergerakanMasuk, 2),
            'expected_cash' => $this->calculateExpectedCash($shift),
            'transactions_count' => $jualan->count(),
            'sales_total' => round((float) $jualan->sum('total_amount'), 2),
            'service_charge_total' => round((float) $jualan->sum('service_charge_amount'), 2),
        ];
    }

    /**
     * Tutup syif dan simpan ringkasan ke baris syif (audit selepas tutup).
     */
    public function closeShiftDenganRingkasan(int $shiftId, int $tenantId, float $tunaiSebenar, int $userId, ?string $nota = null): CashShift
    {
        $shift = $this->getShift($shiftId, $tenantId);

        if (! $shift || $shift->status !== 'open') {
            throw ValidationException::withMessages([
                'shift_id' => ['Syif tidak dijumpai atau sudah ditutup.'],
            ]);
        }

        $ringkasan = $this->ringkasan($shift);

        return tap($this->closeShift($shiftId, [
            'closing_cash' => $tunaiSebenar,
            'notes' => $nota,
        ], $userId, $tenantId), function (CashShift $s) use ($ringkasan, $tunaiSebenar, $nota) {
            $s->update([
                'actual_cash' => round($tunaiSebenar, 2),
                'cash_sales_total' => $ringkasan['cash_sales_total'],
                'non_cash_sales_total' => $ringkasan['non_cash_sales_total'],
                'transactions_count' => $ringkasan['transactions_count'],
                'close_notes' => $nota,
            ]);
        });
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
