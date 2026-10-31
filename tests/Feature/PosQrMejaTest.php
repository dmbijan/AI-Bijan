<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Modules\POS\Models\POSDineOrder;
use App\Modules\POS\Models\POSDineTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** QR meja: pelanggan pesan sendiri tanpa log masuk → staf terima → jualan draf. */
class PosQrMejaTest extends TestCase
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

        $http = $this->sebagai($u, $t);
        $area = $http->postJson('/api/v1/pos/dine/areas', ['name' => 'Dalam'])->assertCreated()->json('data.id');
        $meja = $http->postJson('/api/v1/pos/dine/tables', ['name' => 'Meja 5', 'dine_area_id' => $area])->assertCreated();

        return [$u, $t, $meja->json('data.qr_token'), $kopi, $roti];
    }

    private function sebagaiPelanggan(): void
    {
        $this->flushHeaders();
        $this->flushSession();
        auth()->forgetGuards();
        $this->setUpAkses();
    }

    #[Test]
    public function meja_baharu_dapat_token_qr_dan_pelanggan_lihat_menu(): void
    {
        [, , $token] = $this->sedia();
        $this->assertMatchesRegularExpression('/^[a-z0-9]{24}$/', $token);
        $this->sebagaiPelanggan();

        $this->getJson("/api/v1/public/meja/{$token}")->assertOk()
            ->assertJsonPath('meja', 'Meja 5')
            ->assertJsonPath('syarikat', 'Kafe Kopi')
            ->assertJsonCount(2, 'menu');
    }

    #[Test]
    public function pesanan_guna_harga_katalog_bukan_harga_pelanggan(): void
    {
        [, , $token, $kopi, $roti] = $this->sedia();
        $this->sebagaiPelanggan();

        $r = $this->postJson("/api/v1/public/meja/{$token}/pesanan", [
            'customer_name' => 'Ali',
            'items' => [
                ['item_id' => $kopi->id, 'quantity' => 2, 'notes' => 'kurang manis', 'unit_price' => 0.01],
                ['item_id' => $roti->id, 'quantity' => 1],
            ],
        ])->assertCreated();

        $this->assertEquals(11, (float) $r->json('jumlah'));
        $this->assertSame('submitted', $r->json('status'));

        $this->getJson("/api/v1/public/meja/{$token}/pesanan/".$r->json('token'))->assertOk()
            ->assertJsonPath('items.0.nota', 'kurang manis');
    }

    #[Test]
    public function staf_terima_pesanan_jadi_jualan_draf(): void
    {
        [$u, $t, $token, $kopi] = $this->sedia();
        $this->sebagaiPelanggan();
        $this->postJson("/api/v1/public/meja/{$token}/pesanan", ['items' => [['item_id' => $kopi->id, 'quantity' => 2]]])->assertCreated();

        $http = $this->sebagai($u, $t);
        $baru = $http->getJson('/api/v1/pos/dine/orders?status=submitted')->assertOk()->json();
        $this->assertCount(1, $baru);
        $this->assertSame('Meja 5', $baru[0]['table']['name']);

        $r = $http->postJson("/api/v1/pos/dine/orders/{$baru[0]['id']}/accept", [])->assertOk();
        $this->assertNotEmpty($r->json('sale_no'));
        $this->assertGreaterThanOrEqual(7, (float) $r->json('total_amount'));
    }

    #[Test]
    public function menu_syarikat_lain_tidak_boleh_dipesan(): void
    {
        [, , $token] = $this->sedia();
        [, $lain] = $this->daftar('lain@kedai.my', 'Kedai Lain');
        $this->dalamTenant($lain);
        $asing = Item::create(['sku' => 'X1', 'name' => 'Barang Lain', 'uom' => 'unit', 'sell_price' => 99]);
        $this->sebagaiPelanggan();

        $this->postJson("/api/v1/public/meja/{$token}/pesanan", ['items' => [['item_id' => $asing->id, 'quantity' => 1]]])->assertStatus(422);
        $this->assertSame(0, POSDineOrder::withoutGlobalScopes()->count());
    }

    #[Test]
    public function nama_meja_pendua_ditolak_dengan_mesej(): void
    {
        [$u, $t] = $this->sedia();
        $http = $this->sebagai($u, $t);
        $area = $http->getJson('/api/v1/pos/dine/areas')->json('data.0.id');

        $http->postJson('/api/v1/pos/dine/tables', ['name' => 'Meja 5', 'dine_area_id' => $area])->assertStatus(422)->assertJsonValidationErrors('name');
    }

    #[Test]
    public function token_baharu_matikan_qr_lama(): void
    {
        [$u, $t, $token] = $this->sedia();
        $id = POSDineTable::withoutGlobalScopes()->where('qr_token', $token)->value('id');

        $this->sebagai($u, $t)->postJson("/api/v1/pos/dine/tables/{$id}/qr")->assertOk();
        $this->sebagaiPelanggan();

        $this->getJson("/api/v1/public/meja/{$token}")->assertNotFound();
    }
}
