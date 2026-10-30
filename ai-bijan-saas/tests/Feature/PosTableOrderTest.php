<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Modules\POS\Models\POSTable;
use App\Modules\POS\Models\POSTableOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

class PosTableOrderTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
    }

    private function sedia(): array
    {
        [$u, $t] = $this->daftar('kafe@kopi.my', 'Kafe Kopi');
        $this->dalamTenant($t);
        $kopi = Item::create(['sku' => 'K1', 'name' => 'Kopi O', 'uom' => 'cawan', 'sell_price' => 3.5]);
        $roti = Item::create(['sku' => 'R1', 'name' => 'Roti Bakar', 'uom' => 'pinggan', 'sell_price' => 4]);
        $meja = $this->sebagai($u, $t)->postJson('/api/v1/pos/tables', ['name' => 'Meja 5'])->assertCreated();

        return [$u, $t, $meja->json('token'), $kopi, $roti];
    }

    #[Test]
    public function pelanggan_lihat_menu_tanpa_log_masuk(): void
    {
        [, , $token] = $this->sedia();
        $this->flushSession();
        auth()->forgetGuards();

        $this->getJson("/api/v1/public/meja/{$token}")->assertOk()
            ->assertJsonPath('meja', 'Meja 5')
            ->assertJsonPath('syarikat', 'Kafe Kopi')
            ->assertJsonCount(2, 'menu');
    }

    #[Test]
    public function pesanan_guna_harga_pelayan_bukan_harga_pelanggan(): void
    {
        [, , $token, $kopi, $roti] = $this->sedia();

        $r = $this->postJson("/api/v1/public/meja/{$token}/pesanan", [
            'customer_name' => 'Ali',
            'items' => [
                ['item_id' => $kopi->id, 'quantity' => 2, 'note' => 'kurang manis', 'unit_price' => 0.01],
                ['item_id' => $roti->id, 'quantity' => 1],
            ],
        ])->assertCreated();

        $this->assertEquals(11, (float) $r->json('jumlah'));
        $this->assertSame('baru', $r->json('status'));

        $this->getJson("/api/v1/public/meja/{$token}/pesanan/".$r->json('id'))->assertOk()->assertJsonPath('items.0.nota', 'kurang manis');
    }

    #[Test]
    public function dapur_nampak_pesanan_dan_kemas_status_hingga_tutup(): void
    {
        [$u, $t, $token, $kopi] = $this->sedia();
        $this->postJson("/api/v1/public/meja/{$token}/pesanan", ['items' => [['item_id' => $kopi->id, 'quantity' => 1]]])->assertCreated();

        $http = $this->sebagai($u, $t);
        $senarai = $http->getJson('/api/v1/pos/table-orders')->assertOk();
        $this->assertCount(1, $senarai->json());
        $this->assertSame('Meja 5', $senarai->json('0.table.name'));
        $id = $senarai->json('0.id');

        $http->postJson("/api/v1/pos/table-orders/{$id}/status", ['status' => 'dimasak'])->assertOk()->assertJsonPath('status', 'dimasak');
        $http->postJson("/api/v1/pos/table-orders/{$id}/close", [])->assertOk()->assertJsonPath('status', 'selesai');
        $this->assertCount(0, $http->getJson('/api/v1/pos/table-orders')->json());
        $http->postJson("/api/v1/pos/table-orders/{$id}/status", ['status' => 'baru'])->assertStatus(422);
    }

    #[Test]
    public function menu_syarikat_lain_tidak_boleh_dipesan(): void
    {
        [, , $token] = $this->sedia();
        [, $lain] = $this->daftar('lain@kedai.my', 'Kedai Lain');
        $this->dalamTenant($lain);
        $asing = Item::create(['sku' => 'X1', 'name' => 'Barang Lain', 'uom' => 'unit', 'sell_price' => 99]);

        // Pelanggan sebenar: tiada sesi, tiada header X-Tenant.
        $this->flushHeaders();
        $this->flushSession();
        auth()->forgetGuards();
        $this->setUpAkses();

        $this->postJson("/api/v1/public/meja/{$token}/pesanan", ['items' => [['item_id' => $asing->id, 'quantity' => 1]]])->assertStatus(422);
        $this->assertSame(0, POSTableOrder::withoutGlobalScopes()->count());
    }

    #[Test]
    public function token_baharu_matikan_qr_lama(): void
    {
        [$u, $t, $token] = $this->sedia();
        $id = POSTable::withoutGlobalScopes()->where('token', $token)->value('id');

        $this->sebagai($u, $t)->postJson("/api/v1/pos/tables/{$id}/token")->assertOk();

        $this->getJson("/api/v1/public/meja/{$token}")->assertNotFound();
    }
}
