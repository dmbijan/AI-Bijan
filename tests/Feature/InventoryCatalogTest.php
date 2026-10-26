<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\StockBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Katalog barang, gudang dan laporan stok (modul Inventory). */
class InventoryCatalogTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('stor@kedai-a.my', 'Kedai A');
    }

    private function barang(array $ganti = []): array
    {
        $this->sebagai($this->u, $this->t);

        return $this->postJson('/api/v1/inventory/catalog', array_merge([
            'sku' => 'SKU-1',
            'name' => 'Kotak pembungkus',
            'uom' => 'unit',
            'standard_cost' => 0.85,
            'sell_price' => 1.5,
        ], $ganti))->json();
    }

    public function test_cipta_barang_dan_senarai(): void
    {
        $item = $this->barang();
        $this->assertSame('SKU-1', $item['sku']);

        $senarai = $this->getJson('/api/v1/inventory/catalog')->assertOk()->json('data');
        $this->assertCount(1, $senarai);
        $this->assertSame('Kotak pembungkus', $senarai[0]['name']);
    }

    public function test_sku_bertindih_ditolak(): void
    {
        $this->barang();
        $this->postJson('/api/v1/inventory/catalog', ['sku' => 'SKU-1', 'name' => 'Lain', 'uom' => 'unit'])
            ->assertStatus(422);
    }

    public function test_kemas_kini_barang_dan_audit(): void
    {
        $item = $this->barang();
        $r = $this->putJson("/api/v1/inventory/catalog/{$item['id']}", ['sku' => 'SKU-1', 'name' => 'Nama Baru', 'uom' => 'unit'])
            ->assertOk()
            ->json();

        $this->assertSame('Nama Baru', $r['name']);
    }

    public function test_barang_dengan_pergerakan_tidak_boleh_dipadam(): void
    {
        $item = $this->barang();
        // Pergerakan melalui endpoint sedia ada (Api\V1) supaya lejar wujud.
        $wh = $this->postJson('/api/v1/inventory/warehouses', ['name' => 'Gudang Utama', 'code' => 'WH1', 'type' => 'gudang'])->assertCreated()->json('id');
        $movement = $this->postJson('/api/v1/inventory/movements', [
            'type' => 'receipt',
            'to_warehouse_id' => $wh,
            'lines' => [['item_id' => $item['id'], 'qty' => 5, 'unit_cost' => 1]],
        ])->assertCreated()->json('id');
        $this->postJson("/api/v1/inventory/movements/{$movement}/post")->assertOk();

        $this->deleteJson("/api/v1/inventory/catalog/{$item['id']}")->assertStatus(422);
    }

    public function test_barang_tanpa_lejar_boleh_dinyahaktifkan(): void
    {
        $item = $this->barang();
        $this->deleteJson("/api/v1/inventory/catalog/{$item['id']}")->assertOk();

        $this->dalamTenant($this->t);
        $this->assertFalse(Item::find($item['id'])->is_active);
    }

    public function test_gudang_crud_dan_tidak_boleh_padam_jika_ada_baki(): void
    {
        $this->sebagai($this->u, $this->t);
        $wh = $this->postJson('/api/v1/inventory/depots', ['name' => 'Gudang A'])->assertCreated()->json();
        $this->putJson("/api/v1/inventory/depots/{$wh['id']}", ['name' => 'Gudang A2'])->assertOk();

        $item = $this->barang();
        $this->dalamTenant($this->t);
        StockBalance::create(['item_id' => $item['id'], 'warehouse_id' => $wh['id'], 'qty' => 3, 'value' => 3]);

        $this->deleteJson("/api/v1/inventory/depots/{$wh['id']}")->assertStatus(422);
    }

    public function test_ledger_dan_stok_barang(): void
    {
        $item = $this->barang();
        $this->getJson("/api/v1/inventory/catalog/{$item['id']}/ledger")->assertOk()->assertJsonStructure(['data']);
        $r = $this->getJson("/api/v1/inventory/catalog/{$item['id']}/stock")->assertOk()->json();
        $this->assertSame('SKU-1', $r['sku']);
        $this->assertSame([], $r['balances']);
    }

    public function test_laporan_pesanan_semula(): void
    {
        $item = $this->barang(['reorder_level' => 10]);
        $wh = $this->postJson('/api/v1/inventory/depots', ['name' => 'Gudang B'])->json('id');
        $this->dalamTenant($this->t);
        StockBalance::create(['item_id' => $item['id'], 'warehouse_id' => $wh, 'qty' => 4, 'value' => 4]);

        $r = $this->getJson('/api/v1/inventory/catalog/reorder')->assertOk()->json();
        $this->assertCount(1, $r);
        $this->assertSame('SKU-1', $r[0]['sku']);
        $this->assertEquals(6, $r[0]['suggested_qty']);
    }

    public function test_tenant_lain_tidak_nampak_barang(): void
    {
        $this->barang();

        [$ub, $tb] = $this->daftar('stor@kedai-b.my', 'Kedai B');
        $senarai = $this->sebagai($ub, $tb)->getJson('/api/v1/inventory/catalog')->assertOk()->json('data');

        $this->assertCount(0, $senarai);
    }

    public function test_staf_tidak_boleh_cipta_barang(): void
    {
        $staf = $this->jemput($this->u, $this->t, 'staf@kedai-a.my', ['staff']);

        $this->sebagai($staf, $this->t)->postJson('/api/v1/inventory/catalog', ['sku' => 'X', 'name' => 'X', 'uom' => 'unit'])
            ->assertForbidden();
    }
}
