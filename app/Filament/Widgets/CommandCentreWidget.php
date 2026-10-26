<?php

namespace App\Filament\Widgets;

use App\Modules\Expense\Models\Expense;
use App\Modules\Invoicing\Models\Invoice;
use App\Support\Tenancy\TenantContext;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Command Centre KPI — data sebenar daripada modul sedia ada.
 *
 * ⚠️ Setiap angka datang daripada domain pemiliknya (bukan salinan).
 * Jika modul tiada data / tiada kebenaran → tunjuk 0, bukan ralat.
 */
class CommandCentreWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tenantId = (int) app(TenantContext::class)->id();

        if (! $tenantId) {
            return [
                Stat::make('Tiada Syarikat Aktif', '—')
                    ->description('Pilih syarikat untuk melihat KPI')
                    ->color('gray'),
            ];
        }

        return [
            $this->statHasil($tenantId),
            $this->statBelumBayar($tenantId),
            $this->statPerbelanjaan($tenantId),
            $this->statTertunggak($tenantId),
        ];
    }

    /**
     * Hasil bulan ini (invois diterbitkan).
     */
    private function statHasil(int $tenantId): Stat
    {
        $jumlah = 0.0;

        try {
            $jumlah = (float) Invoice::query()
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['issued', 'partial', 'paid'])
                ->whereBetween('issue_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('grand_total');
        } catch (\Throwable $e) {
            // Modul tidak tersedia — tunjuk 0
        }

        return Stat::make('Hasil Bulan Ini', 'RM ' . number_format($jumlah, 2))
            ->description(now()->translatedFormat('F Y'))
            ->descriptionIcon('heroicon-m-arrow-trending-up')
            ->color($jumlah > 0 ? 'success' : 'gray');
    }

    /**
     * Invois belum dibayar.
     */
    private function statBelumBayar(int $tenantId): Stat
    {
        $jumlah = 0.0;
        $bilangan = 0;

        try {
            $query = Invoice::query()
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['issued', 'partial']);

            $jumlah = (float) (clone $query)->sum('amount_outstanding');
            $bilangan = (clone $query)->count();
        } catch (\Throwable $e) {
            // abaikan
        }

        return Stat::make('Belum Dibayar', 'RM ' . number_format($jumlah, 2))
            ->description($bilangan . ' invois')
            ->descriptionIcon('heroicon-m-clock')
            ->color($jumlah > 0 ? 'warning' : 'gray');
    }

    /**
     * Perbelanjaan bulan ini (diluluskan/dipost/dibayar).
     */
    private function statPerbelanjaan(int $tenantId): Stat
    {
        $jumlah = 0.0;

        try {
            $jumlah = (float) Expense::query()
                ->where('tenant_id', $tenantId)
                ->whereIn('status', [
                    Expense::STATUS_APPROVED,
                    Expense::STATUS_POSTED,
                    Expense::STATUS_PAID,
                ])
                ->whereBetween('expense_date', [now()->startOfMonth(), now()->endOfMonth()])
                ->sum('amount_myr');
        } catch (\Throwable $e) {
            // abaikan
        }

        return Stat::make('Perbelanjaan Bulan Ini', 'RM ' . number_format($jumlah, 2))
            ->description('Diluluskan + dipost + dibayar')
            ->descriptionIcon('heroicon-m-banknotes')
            ->color('danger');
    }

    /**
     * Tuntutan perbelanjaan menunggu kelulusan.
     */
    private function statTertunggak(int $tenantId): Stat
    {
        $bilangan = 0;

        try {
            $bilangan = Expense::query()
                ->where('tenant_id', $tenantId)
                ->where('status', Expense::STATUS_SUBMITTED)
                ->count();
        } catch (\Throwable $e) {
            // abaikan
        }

        return Stat::make('Menunggu Kelulusan', (string) $bilangan)
            ->description($bilangan > 0 ? 'Perlu tindakan anda' : 'Tiada tertunggak')
            ->descriptionIcon('heroicon-m-clipboard-document-check')
            ->color($bilangan > 0 ? 'warning' : 'success');
    }
}
