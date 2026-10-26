<?php

namespace App\Filament\Widgets;

use App\Modules\Orders\Models\UnifiedOrder;
use App\Support\Tenancy\TenantContext;
use Filament\Widgets\ChartWidget;

/**
 * Pecahan pesanan mengikut saluran (30 hari).
 *
 * Saluran = `unified_orders.source` (marketplace|pos|site|booking|cod|manual|wholesale).
 * Pesanan batal / bayar balik dikecualikan supaya angka tidak mengelirukan.
 */
class SaluranPesananChart extends ChartWidget
{
    protected static ?string $heading = 'Pesanan Ikut Saluran';

    protected static ?string $description = '30 hari terakhir';

    protected static ?int $sort = 4;

    /** Nyahaktifkan lazy loading — lihat nota dalam `HasilTrendChart`. */
    protected static bool $isLazy = false;

    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $tenantId = (int) app(TenantContext::class)->id();

        if (! $tenantId) {
            return $this->kosong();
        }

        $baris = UnifiedOrder::query()
            ->where('tenant_id', $tenantId)
            ->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()])
            ->whereNotIn('status', [
                UnifiedOrder::STATUS_CANCELLED,
                UnifiedOrder::STATUS_REFUNDED,
                UnifiedOrder::STATUS_FAILED,
            ])
            ->selectRaw('source, COUNT(*) as bilangan, SUM(total_amount) as nilai')
            ->groupBy('source')
            ->orderByDesc('bilangan')
            ->get();

        if ($baris->isEmpty()) {
            return $this->kosong();
        }

        $label = [
            'pos' => 'POS',
            'marketplace' => 'Marketplace',
            'site' => 'Laman Web',
            'booking' => 'Tempahan',
            'cod' => 'COD',
            'wholesale' => 'Borong',
            'manual' => 'Manual',
        ];

        return [
            'datasets' => [[
                'label' => 'Bilangan Pesanan',
                'data' => $baris->pluck('bilangan')->map(fn ($v) => (int) $v)->all(),
                'backgroundColor' => [
                    '#f59e0b', '#3b82f6', '#10b981', '#8b5cf6',
                    '#ef4444', '#06b6d4', '#6b7280',
                ],
                'borderWidth' => 0,
            ]],
            'labels' => $baris->pluck('source')
                ->map(fn ($s) => $label[$s] ?? ucfirst((string) $s))
                ->all(),
        ];
    }

    private function kosong(): array
    {
        return [
            'datasets' => [[
                'label' => 'Bilangan Pesanan',
                'data' => [],
                'backgroundColor' => [],
            ]],
            'labels' => [],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    public static function canView(): bool
    {
        $ctx = app(TenantContext::class);

        return $ctx->has() && $ctx->can('omnichannel.view');
    }
}
