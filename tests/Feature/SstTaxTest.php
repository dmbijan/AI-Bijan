<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** FIN-010 Cukai SST: kod bertarikh, pengiraan, tempoh 2 bulan, draf SST-02. */
class SstTaxTest extends TestCase
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
        [$u, $t] = $this->daftar('cukai@bunga.my', 'Kedai Bunga Sari');

        return [$u, $t, $this->sebagai($u, $t)];
    }

    private function jualan($http, array $baris): int
    {
        $reg = $http->postJson('/api/v1/pos/registers', ['name' => 'K'.uniqid()])->assertCreated();
        $id = $http->postJson('/api/v1/pos/sales/draft', ['register_id' => $reg->json('id') ?? $reg->json('data.id')])->assertCreated();
        $id = $id->json('id') ?? $id->json('data.id');
        foreach ($baris as $b) {
            $http->postJson("/api/v1/pos/sales/{$id}/items", $b + ['quantity' => 1])->assertSuccessful();
        }
        $jumlah = (float) DB::table('pos_sales')->where('id', $id)->value('total_amount');
        $http->postJson("/api/v1/pos/sales/{$id}/pay", ['tenders' => [['method' => 'card', 'amount' => $jumlah]]])->assertSuccessful();

        return $id;
    }

    #[Test]
    public function kod_lalai_disemai_dan_pengiraan_eksklusif_inklusif(): void
    {
        [, , $http] = $this->sedia();
        $kod = collect($http->getJson('/api/v1/finance/tax-codes')->assertOk()->json('data'))->pluck('code')->all();
        $this->assertEqualsCanonicalizing(['ST10', 'ST5', 'SV6', 'SV8', 'EX', 'NT'], $kod);

        $http->postJson('/api/v1/finance/tax/calculate', ['amount' => 100, 'code' => 'SV8'])->assertOk()
            ->assertJsonPath('data.tax', 8)->assertJsonPath('data.gross', 108);
        $r = $http->postJson('/api/v1/finance/tax/calculate', ['amount' => 108, 'code' => 'SV8', 'inclusive' => true])->assertOk()->json('data');
        $this->assertEquals(100.0, $r['taxable']);
        $this->assertEquals(8.0, $r['tax']);
        // Pembundaran ke sen: 33.33 × 6% = 1.9998 → 2.00
        $this->assertEquals(2.0, $http->postJson('/api/v1/finance/tax/calculate', ['amount' => 33.33, 'code' => 'SV6'])->json('data.tax'));
    }

    #[Test]
    public function kod_ikut_tarikh_berkuat_kuasa_dan_tempoh_baharu_menutup_tempoh_lama(): void
    {
        [, , $http] = $this->sedia();
        // SV8 bermula 1/3/2024: sebelum itu tidak sah.
        $http->postJson('/api/v1/finance/tax/calculate', ['amount' => 100, 'code' => 'SV8', 'date' => '2024-02-29'])->assertStatus(422);

        // Kadar baharu untuk ST5 dari 1/1/2027.
        $http->postJson('/api/v1/finance/tax-codes', ['code' => 'ST5', 'name' => 'Cukai jualan 5% (semakan)', 'type' => 'sales', 'rate' => 7, 'effective_from' => '2027-01-01'])->assertCreated();
        $this->assertEquals(5.0, $http->postJson('/api/v1/finance/tax/calculate', ['amount' => 100, 'code' => 'ST5', 'date' => '2026-12-31'])->json('data.tax'));
        $this->assertEquals(7.0, $http->postJson('/api/v1/finance/tax/calculate', ['amount' => 100, 'code' => 'ST5', 'date' => '2027-01-01'])->json('data.tax'));
        $lama = collect($http->getJson('/api/v1/finance/tax-codes')->json('data'))->first(fn ($k) => $k['code'] === 'ST5' && (float) $k['rate'] === 5.0);
        $this->assertStringStartsWith('2026-12-31', $lama['effective_to']);

        $http->postJson('/api/v1/finance/tax-codes', ['code' => 'EX2', 'name' => 'X', 'type' => 'exempt', 'rate' => 6, 'effective_from' => '2026-01-01'])->assertStatus(422);
    }

    #[Test]
    public function tempoh_dua_bulan_ikut_bulan_mula_kitaran_dan_tarikh_akhir_hantar(): void
    {
        [, , $http] = $this->sedia();
        $http->getJson('/api/v1/finance/tax/period?date=2026-10-15')->assertOk()
            ->assertJsonPath('data.from', '2026-09-01')->assertJsonPath('data.to', '2026-10-31')->assertJsonPath('data.due', '2026-11-30');

        $http->putJson('/api/v1/finance/tax-settings', ['period_start_month' => 2, 'sst_registration_no' => 'W10-1808-32000001'])->assertOk();
        $http->getJson('/api/v1/finance/tax/period?date=2026-10-15')->assertOk()
            ->assertJsonPath('data.from', '2026-10-01')->assertJsonPath('data.to', '2026-11-30')->assertJsonPath('data.due', '2026-12-31');
        $http->getJson('/api/v1/finance/tax/period?date=2027-01-20')->assertOk()
            ->assertJsonPath('data.from', '2026-12-01')->assertJsonPath('data.to', '2027-01-31');
    }

    #[Test]
    public function jualan_pos_dengan_kod_sst_masuk_draf_sst02_dan_baris_tanpa_kod_diberi_amaran(): void
    {
        [, $t, $http] = $this->sedia();
        $this->jualan($http, [
            ['product_name' => 'Set makan', 'unit_price' => 50, 'tax_code' => 'SV6'],
            ['product_name' => 'Hiasan meja', 'unit_price' => 100, 'tax_code' => 'ST10'],
            ['product_name' => 'Air', 'unit_price' => 3, 'tax_code' => 'EX'],
        ]);
        $this->jualan($http, [['product_name' => 'Lama', 'unit_price' => 20, 'tax_rate' => 6]]);
        $http->postJson('/api/v1/pos/sales/999999/items', ['product_name' => 'x', 'unit_price' => 1, 'quantity' => 1, 'tax_code' => 'TIADA']);

        // Invois dikeluarkan dengan SV6 dikira bersama POS; draf dikecualikan.
        foreach (['issued' => 200, 'draft' => 999] as $status => $amaun) {
            $inv = DB::table('invoices')->insertGetId(['tenant_id' => $t->id, 'invoice_number' => 'INV-'.$status, 'status' => $status,
                'issue_date' => now()->toDateString(), 'created_at' => now(), 'updated_at' => now()]);
            DB::table('invoice_items')->insert(['invoice_id' => $inv, 'product_name' => 'Servis', 'quantity' => 1, 'unit_price' => $amaun,
                'tax_percentage' => 6, 'tax_amount' => round($amaun * 0.06, 2), 'line_total' => round($amaun * 1.06, 2), 'tax_code' => 'SV6',
                'created_at' => now(), 'updated_at' => now()]);
        }

        $d = $http->getJson('/api/v1/finance/reports/sst-02?date='.now()->toDateString())->assertOk()->json('data');
        $ikut = collect($d['lines'])->keyBy('code');
        $this->assertEquals(250.0, $ikut['SV6']['taxable']);
        $this->assertEquals(15.0, $ikut['SV6']['tax']);
        $this->assertSame(1, $ikut['SV6']['pos_lines']);
        $this->assertSame(1, $ikut['SV6']['invoice_lines']);
        $this->assertEquals(10.0, $ikut['ST10']['tax']);
        $this->assertEquals(0.0, $ikut['EX']['tax']);
        $this->assertEquals(10.0, $d['sales_tax']);
        $this->assertEquals(15.0, $d['service_tax']);
        $this->assertEquals(25.0, $d['total_tax_payable']);
        $this->assertSame(1, $d['untagged']['lines']);
        $this->assertEquals(1.2, $d['untagged']['tax']);
        $this->assertCount(2, $d['warnings']); // tiada no. pendaftaran + baris tanpa kod
    }

    #[Test]
    public function kod_tidak_sah_pada_baris_pos_ditolak(): void
    {
        [, , $http] = $this->sedia();
        $reg = $http->postJson('/api/v1/pos/registers', ['name' => 'K1'])->assertCreated();
        $id = $http->postJson('/api/v1/pos/sales/draft', ['register_id' => $reg->json('id') ?? $reg->json('data.id')])->assertCreated();
        $id = $id->json('id') ?? $id->json('data.id');
        $http->postJson("/api/v1/pos/sales/{$id}/items", ['product_name' => 'x', 'unit_price' => 10, 'quantity' => 1, 'tax_code' => 'ZZ9'])
            ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'ZZ9'));
    }

    #[Test]
    public function juruwang_tidak_boleh_ubah_kod_atau_lihat_sst02_dan_tenant_terasing(): void
    {
        [$u, $t, $http] = $this->sedia();
        $http->getJson('/api/v1/finance/tax-codes')->assertOk();
        $kasir = $this->jemput($u, $t, 'kasir@bunga.my', ['cashier']);
        $k = $this->sebagai($kasir, $t);
        $k->postJson('/api/v1/finance/tax-codes', ['code' => 'X1', 'name' => 'x', 'type' => 'sales', 'rate' => 1, 'effective_from' => '2026-01-01'])->assertForbidden();
        $k->getJson('/api/v1/finance/reports/sst-02')->assertForbidden();

        [$u2, $t2] = $this->daftar('lain@kedai.my', 'Kedai Lain');
        $lain = $this->sebagai($u2, $t2);
        // Tenant lain dapat set lalai sendiri, bukan kod tenant pertama.
        $this->assertCount(6, $lain->getJson('/api/v1/finance/tax-codes')->json('data'));
    }
}
