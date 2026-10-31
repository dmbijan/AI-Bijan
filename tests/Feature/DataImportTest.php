<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Warehouse;
use App\Modules\CRM\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** TEC-018 / Fasa 2.1 Import Pintar: pratonton → sahkan → simpan. */
class DataImportTest extends TestCase
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
        [$u, $t] = $this->daftar('import@bunga.my', 'Kedai Bunga Sari');

        return [$u, $t, $this->sebagai($u, $t)];
    }

    #[Test]
    public function import_produk_dengan_tajuk_gaya_sistem_lain_pratonton_dahulu_kemudian_simpan(): void
    {
        [, $t, $http] = $this->sedia();
        $csv = "Item Code,Description,Selling Price,Cost,Item Group\n"
            ."ros-merah,Jambangan Ros Merah,\"1,250.00\",80,Bunga\n"
            ."LILY01,Lily Putih,45,20,Bunga\n"
            .",Tiada SKU,10,5,\n"
            ."LILY01,Lily Berganda,45,20,\n"
            ."KAD01,Kad Ucapan,abc,1,\n\n";

        $p = $http->postJson('/api/v1/imports/products', ['csv' => $csv])->assertCreated()->json('data');
        $this->assertSame(['sku' => 0, 'name' => 1, 'sell_price' => 2, 'standard_cost' => 3, 'category' => 4], $p['mapping']);
        $this->assertSame(5, $p['total_rows']);
        $this->assertSame(2, $p['valid_rows']);
        $this->assertSame(3, $p['error_rows']);
        $ralat = collect($p['rows'])->keyBy('row_no');
        $this->assertSame('ROS-MERAH', $ralat[2]['data']['sku']);
        $this->assertEquals(1250, $ralat[2]['data']['sell_price']);
        $this->assertStringContainsString('SKU kosong', $ralat[4]['errors'][0]);
        $this->assertStringContainsString('baris 3', $ralat[5]['errors'][0]);
        $this->assertStringContainsString('Harga jual', $ralat[6]['errors'][0]);

        $this->dalamTenant($t);
        $this->assertSame(0, Item::count()); // pratonton tidak menyentuh data sebenar

        $c = $http->postJson("/api/v1/imports/products/{$p['id']}/commit")->assertOk()->json('data');
        $this->assertSame(['created' => 2, 'updated' => 0], $c['result']);
        $this->dalamTenant($t);
        $this->assertEquals(1250, (float) Item::where('sku', 'ROS-MERAH')->value('sell_price'));

        $http->postJson("/api/v1/imports/products/{$p['id']}/commit")->assertStatus(422); // sekali sahaja

        // Import kedua: SKU sedia ada dikemas kini, bukan dicipta semula.
        $p2 = $http->postJson('/api/v1/imports/products', ['csv' => "SKU,Nama,Harga jual\nLILY01,Lily Putih Premium,55\n"])->json('data');
        $this->assertSame(1, $p2['summary']['update']);
        $http->postJson("/api/v1/imports/products/{$p2['id']}/commit")->assertOk()->assertJsonPath('data.result.updated', 1);
        $this->dalamTenant($t);
        $this->assertSame(2, Item::count());
        $this->assertSame('Lily Putih Premium', Item::where('sku', 'LILY01')->value('name'));
        $this->assertEquals(20, (float) Item::where('sku', 'LILY01')->value('standard_cost')); // medan tiada dalam fail tidak dikosongkan
    }

    #[Test]
    public function import_pelanggan_normal_telefon_pendua_dan_kod_unik_antara_syarikat(): void
    {
        [, $t, $http] = $this->sedia();
        $csv = "Nama;Telefon;Emel;SSM\n"
            ."Ali Bunga;012-345 6789;ALI@contoh.my;\n"
            ."Syarikat Mawar Sdn Bhd;+60 3-1234 5678;akaun@mawar.my;202301012345\n"
            ."Ali Lagi;0123456789;;\n"
            ."Tanpa Emel Sah;;bukan-emel;\n";
        $p = $http->postJson('/api/v1/imports/customers', ['csv' => $csv])->assertCreated()->json('data');
        $this->assertSame(2, $p['valid_rows']);
        $r = collect($p['rows'])->keyBy('row_no');
        $this->assertSame('60123456789', $r[2]['data']['contact_phone']);
        $this->assertSame('ali@contoh.my', $r[2]['data']['contact_email']);
        $this->assertSame('individual', $r[2]['data']['customer_type']);
        $this->assertSame('business', $r[3]['data']['customer_type']);
        $this->assertStringContainsString('baris 2', $r[4]['errors'][0]);
        $this->assertSame(['Emel tidak sah'], $r[5]['errors']);
        $http->postJson("/api/v1/imports/customers/{$p['id']}/commit")->assertOk()->assertJsonPath('data.result.created', 2);

        // Syarikat lain import pelanggan juga: kod pelanggan tidak bertembung (customer_code unik global).
        [$u2, $t2] = $this->daftar('lain@kedai.my', 'Kedai Lain');
        $lain = $this->sebagai($u2, $t2);
        $p2 = $lain->postJson('/api/v1/imports/customers', ['csv' => "Nama,Telefon\nBudi,0198887777\n"])->json('data');
        $lain->postJson("/api/v1/imports/customers/{$p2['id']}/commit")->assertOk();
        $kod = Customer::withoutGlobalScopes()->pluck('customer_code');
        $this->assertCount(3, $kod->unique());

        // Tenant lain tidak nampak import tenant pertama.
        $lain->getJson("/api/v1/imports/customers/{$p['id']}")->assertNotFound();
    }

    #[Test]
    public function stok_awal_perlu_sku_wujud_kos_dan_gudang_serta_kelulusan(): void
    {
        [$u, $t, $http] = $this->sedia();
        $this->dalamTenant($t);
        Item::create(['sku' => 'ROS', 'name' => 'Ros', 'uom' => 'tangkai', 'is_stock' => true]);
        Item::create(['sku' => 'LILY', 'name' => 'Lily', 'uom' => 'tangkai', 'is_stock' => true]);
        $gudang = Warehouse::create(['code' => 'KDI', 'name' => 'Kedai Utama', 'type' => 'outlet']);

        $csv = "SKU,Kuantiti,Kos seunit,Gudang\nros,100,1.50,KDI\nLILY,40,2.00,\nTIADA,5,1,\nROS,0,1,KDI\nROS,10,,KDI\n";
        $p = $http->postJson('/api/v1/imports/opening-stock', ['csv' => $csv])->assertCreated()->json('data');
        $this->assertSame(2, $p['valid_rows']);
        $ralat = collect($p['rows'])->where('action', 'skip')->pluck('errors')->flatten()->implode(' | ');
        $this->assertStringContainsString('SKU tidak wujud', $ralat);
        $this->assertStringContainsString('Kuantiti mesti', $ralat);
        $this->assertStringContainsString('Kos seunit diperlukan', $ralat);

        $staf = $this->jemput($u, $t, 'stok@bunga.my', ['staff']);
        $this->sebagai($staf, $t)->postJson("/api/v1/imports/opening-stock/{$p['id']}/commit")->assertForbidden();
        $http = $this->sebagai($u, $t);

        $c = $http->postJson("/api/v1/imports/opening-stock/{$p['id']}/commit")->assertOk()->json('data.result');
        $this->assertCount(1, $c['movements']);
        $this->assertEquals(230.0, $c['value']); // 100×1.50 + 40×2.00
        $this->assertEquals(100.0, (float) DB::table('stock_balances')->where('warehouse_id', $gudang->id)
            ->where('item_id', Item::withoutGlobalScopes()->where('sku', 'ROS')->value('id'))->sum('qty'));

        // Stok awal kali kedua untuk barang yang sudah berstok ditolak.
        $p2 = $http->postJson('/api/v1/imports/opening-stock', ['csv' => "SKU,Kuantiti,Kos seunit\nROS,5,1\n"])->json('data');
        $this->assertSame(0, $p2['valid_rows']);
        $this->assertStringContainsString('sudah ada stok', $p2['rows'][0]['errors'][0]);
    }

    #[Test]
    public function lajur_wajib_tiada_padanan_manual_templat_dan_kebenaran(): void
    {
        [$u, $t, $http] = $this->sedia();
        $http->postJson('/api/v1/imports/products', ['csv' => "Code X,Nama Barang Saya\nA1,Ros\n"])
            ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'sku'));
        $http->postJson('/api/v1/imports/products', ['csv' => "Code X,Nama Barang Saya\nA1,Ros\n", 'mapping' => ['sku' => 0, 'name' => 1]])
            ->assertCreated()->assertJsonPath('data.valid_rows', 1);
        $http->postJson('/api/v1/imports/products', ['csv' => "a,b\n1,2\n", 'mapping' => ['sku' => 5, 'name' => 1]])->assertStatus(422);

        $tpl = $http->get('/api/v1/imports/customers/template')->assertOk();
        $this->assertStringStartsWith('Nama,Telefon,Emel', $tpl->getContent());
        // Templat sendiri mesti dikenali sepenuhnya oleh padanan automatik.
        $p = $http->postJson('/api/v1/imports/customers', ['csv' => $tpl->getContent()."Ali,0123456789,,,,,,,,,,,\n"])->assertCreated()->json('data');
        $this->assertCount(13, $p['mapping']);

        $kasir = $this->jemput($u, $t, 'kasir@bunga.my', ['cashier']);
        $this->sebagai($kasir, $t)->postJson('/api/v1/imports/customers', ['csv' => "Nama\nAli\n"])->assertForbidden();
    }

    #[Test]
    public function pelanggan_pertama_dua_syarikat_melalui_crm_tidak_bertembung_kod(): void
    {
        foreach (['a@satu.my' => 'Satu', 'b@dua.my' => 'Dua'] as $emel => $nama) {
            [$u, $t] = $this->daftar($emel, 'Kedai '.$nama);
            $this->sebagai($u, $t)->postJson('/api/v1/crm/customers', ['name' => 'Pelanggan '.$nama, 'customer_type' => 'individual'])->assertSuccessful();
        }
        $this->assertCount(2, Customer::withoutGlobalScopes()->pluck('customer_code')->unique());
    }
}
