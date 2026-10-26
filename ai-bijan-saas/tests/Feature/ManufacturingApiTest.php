<?php

namespace Tests\Feature;

use App\Models\Bom;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Liputan API BOM & work order (melengkapkan ManufacturingTest). */
class ManufacturingApiTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    private $u;
    private $t;
    private int $wh;
    private int $bahan;
    private int $produk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('kilang@kedai-a.my', 'Kilang A');
        $this->sebagai($this->u, $this->t);

        $this->wh = $this->postJson('/api/v1/inventory/warehouses', ['code' => 'KLG', 'name' => 'Kilang', 'type' => 'gudang'])->json('id');

        $this->bahan = $this->postJson('/api/v1/inventory/items', ['sku' => 'BHN', 'name' => 'Bahan', 'uom' => 'kg'])->json('id');
        $m = $this->postJson('/api/v1/inventory/movements', ['type' => 'receipt', 'to_warehouse_id' => $this->wh, 'lines' => [['item_id' => $this->bahan, 'qty' => 1000, 'unit_cost' => 2]]])->json('id');
        $this->postJson("/api/v1/inventory/movements/{$m}/post")->assertOk();

        $this->produk = $this->postJson('/api/v1/inventory/items', ['sku' => 'PRD', 'name' => 'Produk Siap', 'uom' => 'unit', 'is_stock' => true])->json('id');
    }

    private function bom(array $ganti = []): array
    {
        return $this->postJson('/api/v1/manufacturing/boms', array_merge([
            'item_id' => $this->produk,
            'type' => 'manufacture',
            'quantity' => 1,
            'lines' => [['item_id' => $this->bahan, 'qty' => 2]],
            'labour_cost' => 5,
            'overhead_cost' => 2,
        ], $ganti))->assertCreated()->json();
    }

    public function test_senarai_bom_kosong_awal(): void
    {
        $r = $this->getJson('/api/v1/manufacturing/boms')->assertOk()->json();
        $this->assertSame([], $r['data'] ?? $r);
    }

    public function test_cipta_bom_dan_lihat_kos_standard(): void
    {
        $b = $this->bom();
        $id = $b['id'];

        $show = $this->getJson("/api/v1/manufacturing/boms/{$id}")->assertOk()->json();
        $this->assertArrayHasKey('standard_cost', $show);
    }

    public function test_bom_tidak_boleh_padam_hanya_nyahaktif(): void
    {
        $b = $this->bom();
        $this->postJson("/api/v1/manufacturing/boms/{$b['id']}/deactivate")->assertOk();

        $this->dalamTenant($this->t);
        $this->assertFalse((bool) Bom::find($b['id'])->is_active);
    }

    public function test_work_order_kitaran_penuh(): void
    {
        $b = $this->bom();

        $wo = $this->postJson('/api/v1/manufacturing/work-orders', [
            'bom_id' => $b['id'],
            'qty' => 10,
            'source_warehouse_id' => $this->wh,
            'target_warehouse_id' => $this->wh,
        ])->assertCreated()->json();

        $senarai = $this->getJson('/api/v1/manufacturing/work-orders')->assertOk()->json();
        $this->assertNotEmpty($senarai['data'] ?? $senarai);

        $siap = $this->postJson("/api/v1/manufacturing/work-orders/{$wo['id']}/complete", [
            'qty_good' => 9,
            'qty_reject' => 1,
        ])->assertOk()->json();

        $this->assertSame('completed', $siap['status']);
    }

    public function test_work_order_batal(): void
    {
        $b = $this->bom();
        $wo = $this->postJson('/api/v1/manufacturing/work-orders', [
            'bom_id' => $b['id'],
            'qty' => 5,
            'source_warehouse_id' => $this->wh,
            'target_warehouse_id' => $this->wh,
        ])->assertCreated()->json();

        $this->postJson("/api/v1/manufacturing/work-orders/{$wo['id']}/cancel")->assertOk();

        $this->dalamTenant($this->t);
        $this->assertSame('cancelled', WorkOrder::find($wo['id'])->status);
    }

    public function test_tenant_lain_tidak_nampak_bom(): void
    {
        $this->bom();

        [$ub, $tb] = $this->daftar('kilang@kedai-b.my', 'Kilang B');
        $r = $this->sebagai($ub, $tb)->getJson('/api/v1/manufacturing/boms')->assertOk()->json();

        $this->assertSame([], $r['data'] ?? $r);
    }

    public function test_juruwang_tidak_boleh_cipta_bom(): void
    {
        $juruwang = $this->jemput($this->u, $this->t, 'kasir@kedai-a.my', ['cashier']);

        $this->sebagai($juruwang, $this->t)
            ->postJson('/api/v1/manufacturing/boms', ['product_id' => $this->produk, 'qty' => 1, 'lines' => []])
            ->assertForbidden();
    }
}
