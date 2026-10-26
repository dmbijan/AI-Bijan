<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\StockLedgerEntry;
use App\Support\Dec;
use App\Support\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    private $u;

    private $t;

    private int $gudang;

    private int $outlet;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $this->sebagai($this->u, $this->t);
        $this->gudang = $this->postJson('/api/v1/inventory/warehouses', ['code' => 'GDG', 'name' => 'Gudang Utama', 'type' => 'gudang'])->assertCreated()->json('id');
        $this->outlet = $this->postJson('/api/v1/inventory/warehouses', ['code' => 'KJG', 'name' => 'Outlet Kajang', 'type' => 'outlet'])->assertCreated()->json('id');
    }

    private function barang(array $x = []): int
    {
        return $this->postJson('/api/v1/inventory/items', $x + ['sku' => 'SKU'.random_int(1000, 99999), 'name' => 'Barang', 'uom' => 'unit'])->assertCreated()->json('id');
    }

    private function pos(string $type, array $lines, array $extra = []): array
    {
        $m = $this->postJson('/api/v1/inventory/movements', ['type' => $type, 'lines' => $lines] + $extra)->assertCreated()->json();

        return $this->postJson("/api/v1/inventory/movements/{$m['id']}/post")->assertOk()->json();
    }

    private function nilai(int $item): array
    {
        $b = collect($this->getJson('/api/v1/inventory/balances?item_id='.$item)->json());

        return [Dec::round($b->sum('qty'), 4), Dec::round($b->sum('value'), 2)];
    }

    public function test_fifo_set_ujian_diketahui(): void
    {
        $i = $this->barang();
        $this->pos('receipt', [['item_id' => $i, 'qty' => 10, 'unit_cost' => 5]], ['to_warehouse_id' => $this->gudang]);
        $this->pos('receipt', [['item_id' => $i, 'qty' => 10, 'unit_cost' => 7]], ['to_warehouse_id' => $this->gudang]);
        $keluar = $this->pos('issue', [['item_id' => $i, 'qty' => 15]], ['from_warehouse_id' => $this->gudang]);
        // FIFO: 10×5 + 5×7 = 85; baki 5×7 = 35
        $this->assertSame('85.00', Dec::round($keluar['lines'][0]['cost_value']));
        $this->assertSame(['5.0000', '35.00'], $this->nilai($i));
    }

    public function test_purata_wajaran_set_ujian_diketahui(): void
    {
        $this->putJson('/api/v1/inventory/valuation', ['method' => 'wac'])->assertOk();
        $i = $this->barang();
        $this->pos('receipt', [['item_id' => $i, 'qty' => 10, 'unit_cost' => 5]], ['to_warehouse_id' => $this->gudang]);
        $this->pos('receipt', [['item_id' => $i, 'qty' => 10, 'unit_cost' => 7]], ['to_warehouse_id' => $this->gudang]);
        $keluar = $this->pos('issue', [['item_id' => $i, 'qty' => 15]], ['from_warehouse_id' => $this->gudang]);
        // WAC: purata 6.00 → 15×6 = 90; baki 5×6 = 30
        $this->assertSame('90.00', Dec::round($keluar['lines'][0]['cost_value']));
        $this->assertSame(['5.0000', '30.00'], $this->nilai($i));
        $this->putJson('/api/v1/inventory/valuation', ['method' => 'fifo'])->assertStatus(422); // tak boleh tukar selepas ada transaksi
    }

    public function test_tiada_stok_negatif_dan_lejar_tidak_boleh_diubah(): void
    {
        $i = $this->barang();
        $this->pos('receipt', [['item_id' => $i, 'qty' => 3, 'unit_cost' => 2]], ['to_warehouse_id' => $this->gudang]);
        $m = $this->postJson('/api/v1/inventory/movements', ['type' => 'issue', 'from_warehouse_id' => $this->gudang, 'lines' => [['item_id' => $i, 'qty' => 4]]])->json('id');
        $this->postJson("/api/v1/inventory/movements/$m/post")->assertStatus(422)->assertJsonFragment(['qty' => ['Stok '.Item::withoutGlobalScopes()->find($i)->sku.' tidak mencukupi (perlu 4, ada 3).']]);
        $this->assertSame(['3.0000', '6.00'], $this->nilai($i));
        app(TenantContext::class)->set($this->t);
        $this->expectException(\LogicException::class);
        StockLedgerEntry::first()->update(['qty' => 999]);
    }

    public function test_batch_luput_keluar_ikut_fefo(): void
    {
        $i = $this->barang(['has_expiry' => true, 'name' => 'Susu']);
        $this->pos('receipt', [['item_id' => $i, 'qty' => 5, 'unit_cost' => 3, 'batch_no' => 'B-LEWAT', 'expires_on' => '2027-06-30'],
            ['item_id' => $i, 'qty' => 5, 'unit_cost' => 4, 'batch_no' => 'B-AWAL', 'expires_on' => '2026-12-31']], ['to_warehouse_id' => $this->gudang]);
        $this->postJson('/api/v1/inventory/movements', ['type' => 'receipt', 'to_warehouse_id' => $this->gudang, 'lines' => [['item_id' => $i, 'qty' => 1, 'unit_cost' => 1]]])->assertCreated();
        $keluar = $this->pos('issue', [['item_id' => $i, 'qty' => 7]], ['from_warehouse_id' => $this->gudang]);
        // FEFO: 5 dari B-AWAL (4.00) + 2 dari B-LEWAT (3.00) = 26
        $this->assertSame('26.00', Dec::round($keluar['lines'][0]['cost_value']));
        $baki = collect($this->getJson("/api/v1/inventory/balances?item_id=$i")->json());
        $this->assertSame(['B-LEWAT'], $baki->pluck('batch_no')->all());
        $this->assertSame(1, count($this->getJson('/api/v1/inventory/reports/expiring?days=400')->json()));
    }

    public function test_pindah_dua_langkah_kekalkan_kos(): void
    {
        $i = $this->barang();
        $this->pos('receipt', [['item_id' => $i, 'qty' => 10, 'unit_cost' => 5]], ['to_warehouse_id' => $this->gudang]);
        $p = $this->pos('transfer', [['item_id' => $i, 'qty' => 4]], ['from_warehouse_id' => $this->gudang, 'to_warehouse_id' => $this->outlet]);
        $this->assertSame('in_transit', $p['status']);
        $this->assertSame('6.0000', Dec::round(collect($this->getJson("/api/v1/inventory/balances?item_id=$i")->json())->sum('qty'), 4)); // 4 dalam transit
        $this->postJson("/api/v1/inventory/movements/{$p['id']}/receive")->assertOk();
        $this->postJson("/api/v1/inventory/movements/{$p['id']}/receive")->assertStatus(422);
        $outlet = collect($this->getJson("/api/v1/inventory/balances?item_id=$i&warehouse_id={$this->outlet}")->json())->first();
        $this->assertSame('20.00', Dec::round($outlet['value']));
        $this->assertSame(['10.0000', '50.00'], $this->nilai($i));
    }

    public function test_freight_in_diagih_ke_kos_dan_tukar_unit(): void
    {
        $a = $this->barang(['uoms' => [['uom' => 'kotak', 'factor' => 12]]]);
        $b = $this->barang();
        $this->pos('receipt', [['item_id' => $a, 'qty' => 2, 'uom' => 'kotak', 'unit_cost' => 120], ['item_id' => $b, 'qty' => 10, 'unit_cost' => 6]], ['to_warehouse_id' => $this->gudang, 'freight_in' => 30]);
        // Nilai: A 240, B 60 (jumlah 300). Freight 30 → A +24, B +6. A: 24 unit @ 11.00; B: 10 @ 6.60
        $this->assertSame(['24.0000', '264.00'], $this->nilai($a));
        $this->assertSame(['10.0000', '66.00'], $this->nilai($b));
    }

    public function test_pelarasan_dan_kira_stok_perlu_kelulusan_orang_lain(): void
    {
        $i = $this->barang();
        $this->pos('receipt', [['item_id' => $i, 'qty' => 10, 'unit_cost' => 5]], ['to_warehouse_id' => $this->gudang]);
        $stor = $this->jemput($this->u, $this->t, 'stor@kedai-a.my', ['inventory_manager']);
        $staf = $this->jemput($this->u, $this->t, 'staf@kedai-a.my', ['staff']);
        $m = $this->sebagai($stor, $this->t)->postJson('/api/v1/inventory/movements', ['type' => 'count', 'to_warehouse_id' => $this->gudang, 'reason' => 'Kira bulanan', 'lines' => [['item_id' => $i, 'qty' => 8]]])->json('id');
        $this->postJson("/api/v1/inventory/movements/$m/post")->assertForbidden(); // pencipta tak boleh lulus sendiri
        $this->sebagai($staf, $this->t)->postJson("/api/v1/inventory/movements/$m/post")->assertForbidden();
        $r = $this->sebagai($this->u, $this->t)->postJson("/api/v1/inventory/movements/$m/post")->assertOk()->json();
        $this->assertSame('10.000000', $r['lines'][0]['system_qty']);
        $this->assertSame('-10.00', Dec::round($r['lines'][0]['cost_value']));
        $this->assertSame(['8.0000', '40.00'], $this->nilai($i));
        $adj = $this->postJson('/api/v1/inventory/movements', ['type' => 'adjustment', 'to_warehouse_id' => $this->gudang, 'lines' => [['item_id' => $i, 'qty' => -1]]])->json('id');
        $this->postJson("/api/v1/inventory/movements/$adj/post")->assertStatus(422); // sebab wajib
    }

    public function test_staf_tiada_akses_dan_tenant_lain_tak_nampak(): void
    {
        $i = $this->barang(['sku' => 'RAHSIA-A']);
        $staf = $this->jemput($this->u, $this->t, 'staf@kedai-a.my', ['staff']);
        $this->sebagai($staf, $this->t)->getJson('/api/v1/inventory/items')->assertForbidden();
        [$ub, $tb] = $this->daftar('b@kedai-b.my', 'Kedai B');
        $r = $this->sebagai($ub, $tb)->getJson('/api/v1/inventory/items')->assertOk();
        $this->assertStringNotContainsString('RAHSIA-A', $r->getContent());
        $wh = $this->postJson('/api/v1/inventory/warehouses', ['code' => 'GB', 'name' => 'B', 'type' => 'gudang'])->json('id');
        $this->postJson('/api/v1/inventory/movements', ['type' => 'receipt', 'to_warehouse_id' => $wh, 'lines' => [['item_id' => $i, 'qty' => 1, 'unit_cost' => 1]]])->assertStatus(422);
        $this->postJson('/api/v1/inventory/movements', ['type' => 'receipt', 'to_warehouse_id' => $this->gudang, 'lines' => [['item_id' => $i, 'qty' => 1, 'unit_cost' => 1]]])->assertStatus(422);
    }

    public function test_laporan_nilai_stok_rendah_tidak_bergerak(): void
    {
        $i = $this->barang(['reorder_level' => 5]);
        $this->pos('receipt', [['item_id' => $i, 'qty' => 4, 'unit_cost' => 2.5]], ['to_warehouse_id' => $this->gudang]);
        $this->assertSame('10.00', $this->getJson('/api/v1/inventory/reports/valuation')->json('total'));
        $this->assertCount(1, $this->getJson('/api/v1/inventory/reports/low-stock')->json());
        $this->assertCount(1, $this->getJson('/api/v1/inventory/reports/non-moving')->json());
    }
}
