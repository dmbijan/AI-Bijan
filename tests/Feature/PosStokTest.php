<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Warehouse;
use App\Modules\Inventory\StockEngine;
use App\Modules\POS\Models\POSSaleItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Jualan POS potong stok; stok tak cukup ditanda untuk semakan tanpa menyekat jualan (polisi owner 31/10). */
class PosStokTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
    }

    private function sedia(bool $denganGudang = true, bool $stok = true, $awal = 10): array
    {
        [$u, $t] = $this->daftar('kedai@bunga.my', 'Kedai Bunga Sari');
        $this->dalamTenant($t);
        $item = Item::create(['sku' => 'ROS', 'name' => 'Jambangan Ros', 'uom' => 'unit', 'sell_price' => 25, 'is_stock' => $stok]);
        $wh = null;
        if ($denganGudang) {
            $wh = Warehouse::create(['code' => 'KDI', 'name' => 'Kedai', 'type' => 'outlet']);
            if ($stok && $awal > 0) {
                app(StockEngine::class)->receive($item->id, $wh->id, null, $awal, 10, 'adjustment', 1, 'Stok awal');
            }
        }
        $reg = $this->sebagai($u, $t)->postJson('/api/v1/pos/registers', ['name' => 'Kaunter 1', 'code' => 'K1'])->assertCreated();

        return [$u, $t, $item, $wh, $reg->json('id') ?? $reg->json('data.id')];
    }

    private function jual(int $reg, int $itemId, int $qty, array $tambah = []): array
    {
        return array_merge([
            'register_id' => $reg,
            'items' => [['product_id' => $itemId, 'product_name' => 'Jambangan Ros', 'quantity' => $qty, 'unit_price' => 25]],
            'tenders' => [['method' => 'cash', 'amount' => 25 * $qty]],
        ], $tambah);
    }

    private function baki(int $itemId, int $whId): float
    {
        return (float) app(StockEngine::class)->onHand($itemId, $whId);
    }

    #[Test]
    public function jualan_potong_stok_dan_simpan_cogs(): void
    {
        [$u, $t, $item, $wh, $reg] = $this->sedia();

        $r = $this->sebagai($u, $t)->postJson('/api/v1/pos/sales/checkout', $this->jual($reg, $item->id, 2))->assertCreated();

        $this->dalamTenant($t);
        $this->assertEquals(8, $this->baki($item->id, $wh->id));
        $baris = POSSaleItem::find($r->json('data.items.0.id'));
        $this->assertSame('deducted', $baris->stock_status);
        $this->assertEquals(20, (float) $baris->cogs);
    }

    #[Test]
    public function stok_tak_cukup_jualan_tetap_masuk_dan_ditanda(): void
    {
        [$u, $t, $item, $wh, $reg] = $this->sedia(awal: 1);
        $http = $this->sebagai($u, $t);

        $r = $http->postJson('/api/v1/pos/sales/checkout', $this->jual($reg, $item->id, 3))->assertCreated();
        $this->assertSame('completed', $r->json('data.status'));

        $this->dalamTenant($t);
        $this->assertEquals(1, $this->baki($item->id, $wh->id), 'Stok tak boleh jadi separuh dipotong.');

        $senarai = $http->getJson('/api/v1/pos/stock-exceptions')->assertOk()->json('data');
        $this->assertCount(1, $senarai);
        $this->assertSame('insufficient', $senarai[0]['stock_status']);
        $this->assertStringContainsString('tidak mencukupi', $senarai[0]['stock_note']);

        $http->postJson("/api/v1/pos/stock-exceptions/{$senarai[0]['id']}/resolve")->assertOk();
        $this->assertCount(0, $http->getJson('/api/v1/pos/stock-exceptions')->json('data'));
    }

    #[Test]
    public function segerak_berulang_tidak_potong_stok_dua_kali(): void
    {
        [$u, $t, $item, $wh, $reg] = $this->sedia();
        $http = $this->sebagai($u, $t);
        $uuid = '3b1c2b8e-1d2a-4c3b-9e8f-0a1b2c3d4e5f';

        $http->postJson('/api/v1/pos/sales/checkout', $this->jual($reg, $item->id, 2, ['client_uuid' => $uuid]))->assertCreated();
        $http->postJson('/api/v1/pos/sales/checkout', $this->jual($reg, $item->id, 2, ['client_uuid' => $uuid]))->assertOk();

        $this->dalamTenant($t);
        $this->assertEquals(8, $this->baki($item->id, $wh->id));
    }

    #[Test]
    public function tiada_gudang_ditanda_untuk_semakan(): void
    {
        [$u, $t, $item, , $reg] = $this->sedia(denganGudang: false);

        $r = $this->sebagai($u, $t)->postJson('/api/v1/pos/sales/checkout', $this->jual($reg, $item->id, 1))->assertCreated();

        $this->dalamTenant($t);
        $this->assertSame('no_warehouse', POSSaleItem::find($r->json('data.items.0.id'))->stock_status);
    }

    #[Test]
    public function barang_bukan_stok_tidak_dipotong(): void
    {
        [$u, $t, $item, , $reg] = $this->sedia(stok: false);

        $r = $this->sebagai($u, $t)->postJson('/api/v1/pos/sales/checkout', $this->jual($reg, $item->id, 1))->assertCreated();

        $this->dalamTenant($t);
        $this->assertSame('not_stock', POSSaleItem::find($r->json('data.items.0.id'))->stock_status);
    }

    #[Test]
    public function produk_syarikat_lain_ditolak(): void
    {
        [$u, $t, , , $reg] = $this->sedia();
        [, $lain] = $this->daftar('lain@kedai.my', 'Kedai Lain');
        $this->dalamTenant($lain);
        $asing = Item::create(['sku' => 'X', 'name' => 'Asing', 'uom' => 'unit', 'sell_price' => 25]);

        $this->sebagai($u, $t)->postJson('/api/v1/pos/sales/checkout', $this->jual($reg, $asing->id, 1))->assertStatus(422);
    }
}
