<?php

namespace App\Filament\Widgets;

use App\Modules\Invoicing\Models\Invoice;
use App\Support\Tenancy\TenantContext;
use Filament\Widgets\ChartWidget;

/**
 * Carta trend hasil 30 hari (invois diterbitkan).
 *
 * ⚠️ Setiap titik data datang daripada `Invoicing` — bukan salinan.
 * Hari tanpa invois dipaparkan sebagai 0 (bukan jurang) supaya trend
 * boleh dibaca tanpa terputus.
 */
class HasilTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Trend Hasil 30 Hari';

    protected static ?string $description = 'Invois diterbitkan, mengikut tarikh terbit';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    /**
     * ⚠️ Lazy loading menyebabkan widget hanya render melalui
     * `POST /livewire/update`. Dalam sesetengah konfigurasi (dev server,
     * sesi, CSP), permintaan itu gagal dan widget kekal sebagai
     * `fi-loading-section` kosong. Render serta-merta lebih selamat.
     */
    protected static bool $isLazy = false;

    protected static ?string $maxHeight = '280px';

    public function getDescription(): ?string
    {
        $tenantId = (int) app(TenantContext::class)->id();

        if (! $tenantId) {
            return 'Tiada syarikat aktif';
        }

        // ⚠️ JANGAN panggil `getData()` di sini — Filament memanggil
        // `getDescription()` dalam laluan berasingan daripada render carta,
        // dan cache data boleh basi. Kira terus daripada DB.
        $jumlah = (float) Invoice::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['issued', 'partial', 'paid'])
            ->whereBetween('issue_date', [
                now()->subDays(29)->startOfDay()->toDateString(),
                now()->endOfDay()->toDateString(),
            ])
            ->sum('grand_total');

        return 'Jumlah RM '.number_format($jumlah, 2).' dalam 30 hari terakhir';
    }

    protected function getData(): array
    {
        $tenantId = (int) app(TenantContext::class)->id();

        if (! $tenantId) {
            return $this->kosong();
        }

        $mula = now()->subDays(29)->startOfDay();
        $akhir = now()->endOfDay();

        $baris = Invoice::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['issued', 'partial', 'paid'])
            ->whereBetween('issue_date', [$mula->toDateString(), $akhir->toDateString()])
            ->selectRaw('DATE(issue_date) as hari, SUM(grand_total) as jumlah')
            ->groupBy('hari')
            ->pluck('jumlah', 'hari');

        $label = [];
        $nilai = [];

        for ($i = 0; $i < 30; $i++) {
            $hari = $mula->copy()->addDays($i);
            $kunci = $hari->toDateString();

            $label[] = $hari->format('d/m');
            // ⚠️ SQLite memulangkan `DATE(issue_date)` sebagai `Y-m-d`;
            // MySQL boleh memulangkan `Y-m-d` juga. Padankan tepat.
            $nilai[] = round((float) ($baris[$kunci] ?? 0), 2);
        }

        return [
            'datasets' => [[
                'label' => 'Hasil (RM)',
                'data' => $nilai,
                'borderColor' => '#f59e0b',
                'backgroundColor' => 'rgba(245, 158, 11, 0.15)',
                'fill' => true,
                'tension' => 0.35,
                'pointRadius' => 2,
            ]],
            'labels' => $label,
        ];
    }

    private function jumlahTempoh(): float
    {
        return array_sum($this->getData()['datasets'][0]['data'] ?? [0]);
    }

    private function kosong(): array    {
        return [
            'datasets' => [[
                'label' => 'Hasil (RM)',
                'data' => array_fill(0, 30, 0),
                'borderColor' => '#9ca3af',
            ]],
            'labels' => collect(range(0, 29))
                ->map(fn ($i) => now()->subDays(29 - $i)->format('d/m'))
                ->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    /**
     * ⚠️ Tanpa `beginAtZero`, Chart.js mengira skala automatik dan boleh
     * menghasilkan paksi-Y negatif apabila semua nilai kecil — carta
     * kelihatan "rata pada sifar" walaupun ada data.
     */
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'elements' => [
                'point' => ['radius' => 3, 'hoverRadius' => 5],
            ],
        ];
    }

    public static function canView(): bool
    {
        $ctx = app(TenantContext::class);

        return $ctx->has() && $ctx->can('invoicing.view');
    }
}
