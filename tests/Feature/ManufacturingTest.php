<?php

namespace Tests\Feature;

use App\Support\Dec;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Contoh V3.0: 1 burger = 1 roti + 100 g daging + 10 g sos + 1 kotak. */
class ManufacturingTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    private $u;

    private $t;

    private int $wh;

    private array $i = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('a@kedai-a.my', 'Kedai A');
        $this->sebagai($this->u, $this->t);
        $this->wh = $this->postJson('/api/v1/inventory/warehouses', ['code' => 'DAPUR', 'name' => 'Dapur', 'type' => 'outlet'])->json('id');
        foreach ([['ROTI', 'unit', 1.20], ['DAGING', 'g', 0.04], ['SOS', 'g', 0.02], ['KOTAK', 'unit', 0.30]] as [$sku, $uom, $kos]) {
            $this->i[$sku] = $this->postJson('/api/v1/inventory/items', ['sku' => $sku, 'name' => $sku, 'uom' => $uom])->json('id');
            $m = $this->postJson('/api/v1/inventory/movements', ['type' => 'receipt', 'to_warehouse_id' => $this->wh, 'lines' => [['item_id' => $this->i[$sku], 'qty' => $uom === 'g' ? 10000 : 200, 'unit_cost' => $kos]]])->json('id');
            $this->postJson("/api/v1/inventory/movements/$m/post")->assertOk();
        }
        $this->i['BURGER'] = $this->postJson('/api/v1/inventory/items', ['sku' => 'BURGER', 'name' => 'Burger Daging', 'uom' => 'unit', 'is_stock' => false, 'sell_price' => 12])->json('id');
        $this->i['PATI'] = $this->postJson('/api/v1/inventory/items', ['sku' => 'PATI', 'name' => 'Pati daging (sedia masak)', 'uom' => 'unit'])->json('id');
    }

    private function baki(string $sku): string
    {
        return Dec::round(collect($this->getJson('/api/v1/inventory/balances?item_id='.$this->i[$sku])->json())->sum('qty'), 2);
    }

    public function test_kit_burger_tolak_bahan_semasa_jual_idempoten(): void
    {
        $bom = $this->postJson('/api/v1/manufacturing/boms', ['item_id' => $this->i['BURGER'], 'type' => 'kit', 'lines' => [
            ['item_id' => $this->i['ROTI'], 'qty' => 1], ['item_id' => $this->i['DAGING'], 'qty' => 100], ['item_id' => $this->i['SOS'], 'qty' => 10], ['item_id' => $this->i['KOTAK'], 'qty' => 1]]])->assertCreated()->json('id');
        // Kos standard: 1.20 + 100×0.04 + 10×0.02 + 0.30 = 5.70
        $this->assertSame('5.7000', $this->getJson("/api/v1/manufacturing/boms/$bom")->json('standard_cost.unit'));
        $r = $this->postJson('/api/v1/inventory/sale-deduct', ['item_id' => $this->i['BURGER'], 'qty' => 3, 'warehouse_id' => $this->wh, 'idempotency_key' => 'POS-0001-1'])->assertOk()->json();
        $this->assertSame('17.1000', $r['cogs']);
        $this->assertFalse($r['replayed']);
        $semula = $this->postJson('/api/v1/inventory/sale-deduct', ['item_id' => $this->i['BURGER'], 'qty' => 3, 'warehouse_id' => $this->wh, 'idempotency_key' => 'POS-0001-1'])->assertOk()->json();
        $this->assertTrue($semula['replayed']);
        $this->assertSame('197.00', $this->baki('ROTI'));   // ditolak SEKALI sahaja
        $this->assertSame('9700.00', $this->baki('DAGING'));
        $this->assertSame('197.00', $this->baki('KOTAK'));
    }

    public function test_kit_tidak_cukup_bahan_gagal_tanpa_tolak_separuh(): void
    {
        $this->postJson('/api/v1/manufacturing/boms', ['item_id' => $this->i['BURGER'], 'type' => 'kit', 'lines' => [['item_id' => $this->i['ROTI'], 'qty' => 1], ['item_id' => $this->i['DAGING'], 'qty' => 100]]])->assertCreated();
        $this->postJson('/api/v1/inventory/sale-deduct', ['item_id' => $this->i['BURGER'], 'qty' => 150, 'warehouse_id' => $this->wh, 'idempotency_key' => 'X1'])->assertStatus(422); // daging cukup 100 sahaja
        $this->assertSame('200.00', $this->baki('ROTI')); // transaksi digulung balik: roti tidak ditolak
    }

    public function test_work_order_kos_sebenar_buruh_overhead_varians(): void
    {
        $bom = $this->postJson('/api/v1/manufacturing/boms', ['item_id' => $this->i['PATI'], 'type' => 'manufacture', 'quantity' => 10, 'labour_cost' => 5, 'overhead_cost' => 2, 'lines' => [
            ['item_id' => $this->i['DAGING'], 'qty' => 1000, 'wastage_pct' => 5], ['item_id' => $this->i['SOS'], 'qty' => 50]]])->assertCreated()->json('id');
        // Standard bagi 10: daging 1050×0.04=42 + sos 50×0.02=1 + buruh 5 + overhead 2 = 50 → 5.00 seunit
        $this->assertSame('5.0000', $this->getJson("/api/v1/manufacturing/boms/$bom")->json('standard_cost.unit'));
        $wo = $this->postJson('/api/v1/manufacturing/work-orders', ['bom_id' => $bom, 'qty' => 20, 'source_warehouse_id' => $this->wh, 'target_warehouse_id' => $this->wh])->assertCreated()->json();
        $this->assertSame('2100.000000', collect($wo['materials'])->firstWhere('item_id', $this->i['DAGING'])['qty_required']);
        // Sebenar: daging guna 2200 g (lebih), hasil baik 19 + 1 reject
        $s = $this->postJson("/api/v1/manufacturing/work-orders/{$wo['id']}/complete", ['qty_good' => 19, 'qty_reject' => 1, 'consumed' => [$this->i['DAGING'] => 2200]])->assertOk()->json();
        // Bahan 2200×0.04 + 100×0.02 = 90; buruh 10; overhead 4 → 104 ÷ 19 = 5.473684
        $this->assertSame('90.00', Dec::round($s['material_cost']));
        $this->assertSame('5.4737', Dec::round($s['unit_cost'], 4));
        $this->assertSame('9.00', Dec::round($s['variance'])); // (5.473684 − 5.00) × 19
        $this->assertSame('19.00', $this->baki('PATI'));
        $this->postJson("/api/v1/manufacturing/work-orders/{$wo['id']}/complete", ['qty_good' => 1])->assertStatus(422);
    }

    public function test_bom_kitaran_ditolak_dan_versi_baru(): void
    {
        $this->postJson('/api/v1/manufacturing/boms', ['item_id' => $this->i['PATI'], 'type' => 'manufacture', 'lines' => [['item_id' => $this->i['DAGING'], 'qty' => 100]]])->assertCreated();
        $this->postJson('/api/v1/manufacturing/boms', ['item_id' => $this->i['DAGING'], 'type' => 'manufacture', 'lines' => [['item_id' => $this->i['PATI'], 'qty' => 1]]])->assertStatus(422);
        $this->postJson('/api/v1/manufacturing/boms', ['item_id' => $this->i['PATI'], 'type' => 'manufacture', 'lines' => [['item_id' => $this->i['PATI'], 'qty' => 1]]])->assertStatus(422);
        $v2 = $this->postJson('/api/v1/manufacturing/boms', ['item_id' => $this->i['PATI'], 'type' => 'manufacture', 'is_default' => true, 'lines' => [['item_id' => $this->i['DAGING'], 'qty' => 120]]])->json();
        $this->assertSame(2, $v2['version']);
        $semua = collect($this->getJson('/api/v1/manufacturing/boms?item_id='.$this->i['PATI'])->json());
        $this->assertSame(1, $semua->where('is_default', true)->count());
    }

    public function test_juruwang_boleh_tolak_jualan_tapi_tak_urus_bom(): void
    {
        $kw = $this->jemput($this->u, $this->t, 'kaunter@kedai-a.my', ['cashier']);
        $this->sebagai($kw, $this->t)->postJson('/api/v1/inventory/sale-deduct', ['item_id' => $this->i['ROTI'], 'qty' => 1, 'warehouse_id' => $this->wh, 'idempotency_key' => 'K1'])->assertOk();
        $this->postJson('/api/v1/manufacturing/boms', ['item_id' => $this->i['BURGER'], 'type' => 'kit', 'lines' => [['item_id' => $this->i['ROTI'], 'qty' => 1]]])->assertForbidden();
        $this->postJson('/api/v1/inventory/movements', ['type' => 'receipt', 'to_warehouse_id' => $this->wh, 'lines' => [['item_id' => $this->i['ROTI'], 'qty' => 1, 'unit_cost' => 1]]])->assertForbidden();
    }
}
