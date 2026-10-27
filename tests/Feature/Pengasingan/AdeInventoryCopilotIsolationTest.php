<?php

namespace Tests\Feature\Pengasingan;

use App\Models\Item;
use App\Models\StockBalance;
use App\Models\Warehouse;
use App\Modules\ADE\Models\AdeSandbox;
use App\Modules\Blast\Models\BlastMessage;
use App\Modules\Prospecting\Models\Prospect;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul ADE (sandbox), Inventory (stok) + SalesCopilot.
 *
 * Ditambah 27 Okt 2026 (P1-1d) untuk menutup liputan 59/59 modul.
 *
 * • ADE: `AdeSandbox` menyimpan `path`/`repo_path` — lokasi fail pada disk
 *   pelayan. Kebocoran membolehkan satu tenant membaca laluan kerja tenant lain.
 *   (`AdeSkill` SENGAJA tiada trait — katalog global + salinan tenant, lihat
 *   `config/tenant-global-models.php`.)
 * • Inventory: `StockBalance` menggabungkan item + gudang + kuantiti.
 * • SalesCopilot: tiada model sendiri — membaca `Prospect` & `BlastMessage`
 *   (modul lain). Ujian ini mengesahkan data yang dibacanya terasing.
 */
class AdeInventoryCopilotIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Inventory';

    protected array $model = [
        StockBalance::class,
        AdeSandbox::class,
        Prospect::class,
        BlastMessage::class,
    ];

    protected ?string $laluanApi = '/api/v1/inventory/items';

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        // Item + gudang dicipta dalam konteks sama (tenant aktif).
        $itemId = Item::create([
            'sku' => 'INV-' . $n,
            'name' => 'Item Rahsia Tenant Satu ' . $n,
            'uom' => 'unit',
        ])->id;

        $gudangId = Warehouse::create([
            'name' => 'Gudang Rahsia ' . $n,
            'code' => 'GD-' . $n,
        ])->id;

        if ($this->rekodSemasa === StockBalance::class) {
            return [
                'item_id' => $itemId,
                'warehouse_id' => $gudangId,
                'qty' => 10,
                'reserved' => 0,
                'value' => 100,
            ];
        }

        if ($this->rekodSemasa === AdeSandbox::class) {
            return [
                'name' => 'Sandbox Rahsia Tenant Satu ' . $n,
                'slug' => 'sandbox-rahsia-' . $n,
                'status' => 'ready',
                'path' => '/tmp/rahsia-' . $n,
            ];
        }

        if ($this->rekodSemasa === BlastMessage::class) {
            // `blast_messages.campaign_id` FK NOT NULL → perlu kempen.
            $kempen = \App\Modules\Blast\Models\BlastCampaign::create([
                'name' => 'Blast Rahsia ' . $n,
                'channel' => 'email',
                'status' => 'draft',
            ]);

            return [
                'campaign_id' => $kempen->id,
                'channel' => 'email',
                'recipient' => 'rahsia' . $n . '@blast-tenant-satu.test',
                'status' => 'queued',
            ];
        }

        return [
            'first_name' => 'Rahsia',
            'last_name' => 'Tenant Satu ' . $n,
            'email' => 'rahsia' . $n . '@prospek-copilot-tenant-satu.test',
            'company' => 'Syarikat Rahsia ' . $n,
        ];
    }
}
