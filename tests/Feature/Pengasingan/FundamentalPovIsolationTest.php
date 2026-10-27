<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Fundamental\Models\HealthCheck;
use App\Modules\ProofOfValue\Models\PovBaseline;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Fundamental (kesihatan sistem) + ProofOfValue
 * (baseline nilai).
 *
 * Ditambah 27 Okt 2026 (P1-1d).
 *
 * • Fundamental: log kesihatan boleh mendedahkan konfigurasi infrastruktur.
 * • ProofOfValue: baseline metrik digunakan untuk mengira nilai yang
 *   dibuktikan kepada pelanggan — data komersial.
 */
class FundamentalPovIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Fundamental';

    protected array $model = [
        HealthCheck::class,
        PovBaseline::class,
    ];

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected string $sebabTiadaApi = 'laluan diagnostik hanya untuk peranan platform';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === PovBaseline::class) {
            // `pov_baselines.metric_id` FK → `pov_metrics` (NOT NULL).
            $metricId = \Illuminate\Support\Facades\DB::table('pov_metrics')->insertGetId([
                'tenant_id' => $this->t->id,
                'module' => 'pos',
                'key' => 'metrik_rahsia_' . $n,
                'name' => 'Metrik Rahsia Tenant Satu ' . $n,
                'unit' => 'count',
                'direction' => 'up',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'metric_id' => $metricId,
                'baseline_date' => '2026-10-01',
                'baseline_value' => 100,
                'note' => 'Rahsia Tenant Satu ' . $n,
            ];
        }

        return [
            'check_key' => 'semakan_rahsia_' . $n,
            'name' => 'Rahsia Tenant Satu ' . $n,
            'component' => 'database',
            'status' => 'ok',
        ];
    }
}
