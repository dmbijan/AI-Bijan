<?php

namespace App\Filament\Widgets;

use App\Modules\Orders\Models\UnifiedOrder;
use App\Support\Tenancy\TenantContext;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Ringkasan pesanan — widget KPI kedua.
 *
 * Diasingkan daripada `CommandCentreWidget` supaya pesanan (omnichannel)
 * boleh disembunyikan secara berasingan daripada kewangan (invoicing).
 */
class PesananRingkasanWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $ctx = app(TenantContext::class);
        $tenantId = (int) $ctx->id();

        if (! $tenantId) {
            return [];
        }

        $bulanIni = UnifiedOrder::query()
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->whereNotIn('status', [
                UnifiedOrder::STATUS_CANCELLED,
                UnifiedOrder::STATUS_REFUNDED,
                UnifiedOrder::STATUS_FAILED,
            ]);

        $bilangan = (clone $bulanIni)->count();
        $nilai = (float) (clone $bulanIni)->sum('total_amount');

        $perluTindakan = UnifiedOrder::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [
                UnifiedOrder::STATUS_PENDING,
                UnifiedOrder::STATUS_CONFIRMED,
                UnifiedOrder::STATUS_PROCESSING,
            ])
            ->count();

        return [
            Stat::make('Pesanan Bulan Ini', (string) $bilangan)
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),

            Stat::make('Nilai Pesanan', 'RM '.number_format($nilai, 2))
                ->description('Tidak termasuk batal/bayar balik')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($nilai > 0 ? 'success' : 'gray'),

            Stat::make('Perlu Tindakan', (string) $perluTindakan)
                ->description($perluTindakan > 0 ? 'Menunggu diproses' : 'Semua selesai')
                ->descriptionIcon('heroicon-m-clock')
                ->color($perluTindakan > 0 ? 'warning' : 'gray'),
        ];
    }

    public static function canView(): bool
    {
        $ctx = app(TenantContext::class);

        return $ctx->has() && $ctx->can('omnichannel.view');
    }
}
