<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\BusinessIntel\Models\BiCompetitor;
use App\Modules\BusinessIntel\Models\BiIdea;
use App\Modules\COGS\Models\ProductCost;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul COGS (kos produk) + BusinessIntel (pesaing).
 *
 * Ditambah 27 Okt 2026 (P1-1d).
 *
 * • COGS: kos sebenar setiap produk — kebocoran mendedahkan margin dan
 *   strategi harga tenant lain.
 * • BusinessIntel: penyelidikan pesaing dan idea pertumbuhan — data
 *   strategik yang tidak boleh dikongsi.
 */
class CogsIntelIsolationTest extends UjianPengasingan
{
    protected string $modul = 'BusinessIntel,COGS';

    protected array $model = [
        ProductCost::class,
        BiCompetitor::class,
        BiIdea::class,
    ];

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected string $sebabTiadaApi = 'tiada laluan senarai kos/pesaing untuk diuji';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === BiCompetitor::class) {
            return [
                'name' => 'Pesaing Rahsia Tenant Satu ' . $n,
                'industry' => 'retail',
            ];
        }

        if ($this->rekodSemasa === BiIdea::class) {
            return [
                'title' => 'Idea Rahsia Tenant Satu ' . $n,
                'category' => 'growth',
                'source' => 'manual',
            ];
        }

        // `cogs_product_costs.product_id` FK → `items` (modul Inventory),
        // NOT NULL. Cipta item melalui DB::table supaya ujian COGS tidak
        // bergantung pada trait/modul lain.
        $itemId = \Illuminate\Support\Facades\DB::table('items')->insertGetId([
            'tenant_id' => $this->t->id,
            'sku' => 'COGS-' . $n,
            'name' => 'Item Ujian ' . $n,
            'uom' => 'unit',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'product_id' => $itemId,
            'cost_date' => '2026-10-20',
            'method' => 'weighted_average',
            'total_unit_cost' => 10,
        ];
    }
}
