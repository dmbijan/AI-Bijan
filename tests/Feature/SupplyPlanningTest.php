<?php

namespace Tests\Feature;

use App\Models\StockLayer;
use App\Modules\Inventory\StockEngine;
use App\Modules\SCM\Models\PurchaseOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Kelompok 2 stok: SUP-011 ramalan, SUP-013 PIM, SUP-014 pesanan semula, SUP-015 kos pendaratan. */
class SupplyPlanningTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    private $u;

    private $t;

    private int $wh;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('stok@bunga.my', 'Kedai Bunga Stok');
        $this->sebagai($this->u, $this->t);
        $this->wh = $this->postJson('/api/v1/inventory/warehouses', ['code' => 'GDG', 'name' => 'Gudang', 'type' => 'gudang'])->assertCreated()->json('id');
    }

    private function barang(string $sku, array $x = []): int
    {
        return $this->postJson('/api/v1/inventory/items', $x + ['sku' => $sku, 'name' => 'Ros '.$sku, 'uom' => 'batang'])->assertCreated()->json('id');
    }

    private function terima(int $item, float $qty, float $cost): array
    {
        $m = $this->postJson('/api/v1/inventory/movements', ['type' => 'receipt', 'to_warehouse_id' => $this->wh, 'lines' => [['item_id' => $item, 'qty' => $qty, 'unit_cost' => $cost]]])->assertCreated()->json();

        return $this->postJson("/api/v1/inventory/movements/{$m['id']}/post")->assertOk()->json();
    }

    /** Jual $qty sehari selama $days hari lepas (lejar 'sale' seperti POS). */
    private function jualan(int $item, float $perDay, int $days): void
    {
        $this->dalamTenant($this->t);
        $this->actingAs($this->u);
        $e = app(StockEngine::class);
        $base = now()->startOfDay();
        for ($i = $days; $i >= 1; $i--) {
            Carbon::setTestNow($base->copy()->subDays($i)->addHours(12));
            $e->issue($item, $this->wh, null, $perDay, 'sale', 1);
        }
        Carbon::setTestNow();
    }

    private function pembekal(): int
    {
        return $this->postJson('/api/v1/scm/suppliers', ['company_name' => 'Ladang Cameron Sdn Bhd'])->assertCreated()->json('id');
    }

    #[Test]
    public function ramalan_ikut_jualan_sebenar_dan_cadang_pesanan_semula(): void
    {
        $ros = $this->barang('ROS1');
        $this->terima($ros, 600, 2);
        $this->jualan($ros, 10, 56);

        $fc = collect($this->getJson('/api/v1/scm/planning/forecast?days=30')->assertOk()->json('data'))->firstWhere('item_id', $ros);
        $this->assertEqualsWithDelta(10, $fc['avg_daily'], 0.01);
        $this->assertEqualsWithDelta(300, $fc['forecast'], 1);
        $this->assertEquals(1.0, $fc['trend']);

        // Baki 600 - 560 = 40, masa tiba 7 hari → perlu 70 + stok keselamatan 20 → cadang.
        $sup = $this->pembekal();
        $this->putJson("/api/v1/scm/planning/items/{$ros}", ['lead_time_days' => 7, 'safety_stock' => 20, 'preferred_supplier_id' => $sup])->assertOk();
        $s = collect($this->getJson('/api/v1/scm/planning/reorder')->assertOk()->json('data'))->firstWhere('item_id', $ros);
        $this->assertNotNull($s);
        $this->assertEqualsWithDelta(40, $s['on_hand'], 0.001);
        $this->assertSame('urgent', $s['urgency']);
        $this->assertEqualsWithDelta(90, $s['reorder_point'], 0.5); // 10×7 + 20
        $this->assertSame((int) ceil(90 + 300 - $s['on_hand']), $s['suggested_qty']);

        // Cipta PO draf → perlu kelulusan; cadangan tidak berulang kerana sudah dalam pesanan.
        $pos = $this->postJson('/api/v1/scm/planning/reorder/draft-pos', ['lines' => [['item_id' => $ros]]])->assertCreated()->json('data');
        $this->assertCount(1, $pos);
        $this->dalamTenant($this->t);
        $this->assertSame('draft', PurchaseOrder::find($pos[0]['id'])->status);
        $this->assertNull(collect($this->getJson('/api/v1/scm/planning/reorder')->json('data'))->firstWhere('item_id', $ros));
    }

    #[Test]
    public function tanpa_pembekal_po_tidak_dicipta(): void
    {
        $i = $this->barang('ROS2', ['reorder_level' => 10]);
        $this->postJson('/api/v1/scm/planning/reorder/draft-pos', ['lines' => [['item_id' => $i, 'qty' => 5]]])->assertStatus(422);
    }

    #[Test]
    public function terima_po_masuk_stok_dan_kos_pendaratan_diagih(): void
    {
        $a = $this->barang('A1');
        $b = $this->barang('B1');
        $sup = $this->pembekal();
        $po = $this->postJson('/api/v1/scm/purchase-orders', ['supplier_id' => $sup, 'order_date' => now()->toDateString(), 'lines' => [
            ['item_id' => $a, 'item_name' => 'A', 'quantity_ordered' => 100, 'unit_price' => 3],
            ['item_id' => $b, 'item_name' => 'B', 'quantity_ordered' => 50, 'unit_price' => 4],
        ]])->assertCreated()->json();
        $id = $po['id'] ?? $po['data']['id'];
        foreach (['submit', 'approve', 'send'] as $s) {
            $this->postJson("/api/v1/scm/purchase-orders/{$id}/{$s}")->assertSuccessful();
        }
        $lines = $this->getJson("/api/v1/scm/purchase-orders/{$id}")->json('lines') ?? $this->getJson("/api/v1/scm/purchase-orders/{$id}")->json('data.lines');
        $grn = $this->postJson("/api/v1/scm/purchase-orders/{$id}/receive", ['warehouse_id' => $this->wh, 'received_items' => collect($lines)->map(fn ($l) => ['purchase_order_line_id' => $l['id'], 'quantity_received' => $l['quantity_ordered']])->all()])
            ->assertCreated()->json();
        $mv = $grn['stock_movement']['id'];

        // Jual 50 unit A dahulu: separuh kos pendaratan A masuk kos jualan.
        $this->dalamTenant($this->t);
        $this->actingAs($this->u);
        app(StockEngine::class)->issue($a, $this->wh, null, 50, 'sale', 1);

        // Nilai: A 300, B 200 → caj 100 diagih 60 / 40.
        $lc = $this->postJson('/api/v1/scm/landed-costs', ['stock_movement_id' => $mv, 'charges' => [['type' => 'freight', 'amount' => 70], ['type' => 'duty', 'amount' => 30]]])->assertCreated()->json('data');
        $al = collect($lc['lines'])->keyBy('item_id');
        $this->assertEqualsWithDelta(60, $al[$a]['allocated'], 0.001);
        $this->assertEqualsWithDelta(40, $al[$b]['allocated'], 0.001);

        $p = $this->postJson("/api/v1/scm/landed-costs/{$lc['id']}/post")->assertOk()->json('data');
        $this->assertEqualsWithDelta(70, $p['capitalised'], 0.01); // 30 (A baki separuh) + 40 (B)
        $this->assertEqualsWithDelta(30, $p['expensed'], 0.01);

        $bal = collect($this->getJson('/api/v1/inventory/balances?item_id='.$a)->json());
        $this->assertEqualsWithDelta(150 + 30, $bal->sum('value'), 0.01);
        $this->dalamTenant($this->t);
        $this->assertEqualsWithDelta(3.6, (float) StockLayer::where('item_id', $a)->where('qty_remaining', '>', 0)->first()->unit_cost, 0.0001);
        $this->postJson("/api/v1/scm/landed-costs/{$lc['id']}/post")->assertStatus(422);
    }

    #[Test]
    public function pim_skor_kelengkapan_dan_jana_varian(): void
    {
        $i = $this->barang('BQT', ['sell_price' => 89]);
        $row = collect($this->getJson('/api/v1/scm/pim')->assertOk()->json('data'))->firstWhere('id', $i);
        $this->assertLessThan(100, $row['score']);
        $this->assertContains('Gambar', $row['missing']);

        $r = $this->putJson("/api/v1/scm/pim/{$i}", ['barcode' => '9551234567890', 'category' => 'Jambangan', 'brand' => 'Bijan Florist',
            'description' => str_repeat('Jambangan ros segar dibalut kemas untuk hari istimewa. ', 2), 'images' => ['https://contoh.my/a.jpg'], 'weight_g' => 800])->assertOk()->json('data');
        $this->assertSame(100, $r['score']);

        $v = $this->postJson("/api/v1/scm/pim/{$i}/variants", ['options' => ['Saiz' => ['Kecil', 'Besar'], 'Warna' => ['Merah', 'Putih']]])->assertCreated();
        $this->assertSame(4, $v->json('meta.created'));
        $this->assertSame(0, $this->postJson("/api/v1/scm/pim/{$i}/variants", ['options' => ['Saiz' => ['Kecil', 'Besar'], 'Warna' => ['Merah', 'Putih']]])->json('meta.created'));
    }

    #[Test]
    public function juruwang_tidak_boleh_pos_kos_pendaratan(): void
    {
        $kasyer = $this->jemput($this->u, $this->t, 'kasyer@bunga.my', ['cashier']);
        $this->sebagai($kasyer, $this->t)->postJson('/api/v1/scm/landed-costs/1/post')->assertForbidden();
    }
}
