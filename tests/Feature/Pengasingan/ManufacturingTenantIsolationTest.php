<?php

namespace Tests\Feature\Pengasingan;

use App\Models\Bom;
use App\Models\Item;
use App\Models\WorkOrder;
use App\Models\Warehouse;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Manufacturing (BOM, work order).
 *
 * Ditambah 27 Okt 2026 (P1-1d). BOM mengandungi resipi pembuatan (kos bahan
 * + buruh) — harta intelek paling sensitif bagi pengeluar; work order
 * mendedahkan kuantiti pengeluaran.
 *
 * Model tinggal di `app/Models` (modul hanya ada service) — lihat nota
 * `HelpdeskInventoryIsolationTest`.
 */
class ManufacturingTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Manufacturing';

    protected array $model = [
        Bom::class,
        WorkOrder::class,
    ];

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected string $sebabTiadaApi = 'laluan BOM/work order memerlukan entiti rujukan penuh';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        $itemId = Item::create([
            'sku' => 'MFG-' . $n,
            'name' => 'Item Pembuatan ' . $n,
            'uom' => 'unit',
        ])->id;

        if ($this->rekodSemasa === WorkOrder::class) {
            $bom = Bom::create([
                'item_id' => $itemId,
                'version' => 1,
                'type' => 'manufacture',
                'quantity' => 1,
            ]);

            $gudang = Warehouse::create([
                'name' => 'Gudang Ujian ' . $n,
                'code' => 'GDG-' . $n,
            ]);

            return [
                'number' => 'WO-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT),
                'bom_id' => $bom->id,
                'item_id' => $itemId,
                'qty_planned' => 1,
                'source_warehouse_id' => $gudang->id,
                'target_warehouse_id' => $gudang->id,
                'status' => 'draft',
            ];
        }

        return [
            'item_id' => $itemId,
            'version' => 1,
            'type' => 'manufacture',
            'quantity' => 1,
            'is_active' => true,
        ];
    }
}
