<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Analytics\Models\Dashboard;
use App\Modules\Analytics\Models\Metric;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Analytics (papan pemuka, metrik).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Definisi metrik (`formula`, `data_source`)
 * ialah harta intelek dan papan pemuka boleh mendedahkan KPI pesaing.
 *
 * ⚠️ Modul ini turut terjejas oleh pepijat `TenantContext` tanpa `use`
 * (`DashboardController`, `MetricController`) yang dibetulkan 27 Okt.
 */
class AnalyticsTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Analytics';

    protected array $model = [
        Dashboard::class,
        Metric::class,
    ];

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected string $sebabTiadaApi = 'laluan papan pemuka memerlukan peranan/entitlemen khas';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === Metric::class) {
            return [
                'name' => 'Metrik Rahsia Tenant Satu ' . $n,
                'code' => 'metrik_rahsia_' . $n,
                'category' => 'sales',
                'type' => 'number',
                'aggregation_type' => 'sum',
            ];
        }

        return [
            'name' => 'Papan Rahsia Tenant Satu ' . $n,
            'code' => 'PAPAN-' . $n,
            'type' => 'main',
            'is_active' => true,
        ];
    }
}
