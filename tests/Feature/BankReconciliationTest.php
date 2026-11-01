<?php

namespace Tests\Feature;

use App\Modules\Billing\Models\PaymentLink;
use App\Modules\Finance\Models\BankStatementLine;
use App\Modules\Finance\Services\BankReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** FIN-017 Rekonsiliasi Bank + import penyata CSV (AUT-013). */
class BankReconciliationTest extends TestCase
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
        [$u, $t] = $this->daftar('kewangan@bunga.my', 'Kedai Bunga Sari');
        $http = $this->sebagai($u, $t);
        $akaun = $http->postJson('/api/v1/finance/bank-accounts', ['name' => 'Maybank Operasi', 'bank_name' => 'Maybank', 'account_last4' => '1234'])
            ->assertCreated()->json('data.id');
        return [$u, $t, $http, $akaun];
    }

    private function bayaran(int $tenantId, string $jenis, float $amaun, string $tarikh, string $ruj, ?string $rujBank = null): int
    {
        return DB::table('payments')->insertGetId([
            'tenant_id' => $tenantId, 'payment_type' => $jenis, 'payable_type' => 'ar', 'payable_id' => 1,
            'payment_reference' => $ruj, 'payment_date' => $tarikh, 'amount' => $amaun, 'currency' => 'MYR',
            'method' => 'wire_transfer', 'reference_number' => $rujBank, 'is_reconciled' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private const CSV_MAYBANK = <<<'CSV'
Account Number,5141-1234
Statement Period,01/10/2026 - 31/10/2026

Transaction Date,Transaction Description,Debit,Credit,Balance
03/10/2026,IBG CR KEDAI ABC REC-2026-00001,,"1,250.00","11,250.00"
05/10/2026,DUITNOW TRF JAMBANGAN,,120.00,"11,370.00"
07/10/2026,PEMBAYARAN PEMBEKAL BUNGA SEGAR,800.00,,"10,570.00"
31/10/2026,SERVICE CHARGE,5.00,,"10,565.00"
CSV;

    #[Test]
    public function import_csv_bank_malaysia_dengan_debit_kredit_dan_baris_maklumat_di_atas(): void
    {
        [, , $http, $akaun] = $this->sedia();

        $r = $http->postJson("/api/v1/finance/bank-accounts/{$akaun}/import", ['csv' => self::CSV_MAYBANK])->assertCreated();
        $this->assertSame(4, $r->json('data.imported'));
        $this->assertSame(0, $r->json('data.duplicates'));
        $this->assertSame([], $r->json('data.errors'));

        $jumlah = BankStatementLine::withoutGlobalScopes()->orderBy('txn_date')->pluck('amount')->map(fn ($a) => (float) $a)->all();
        $this->assertSame([1250.0, 120.0, -800.0, -5.0], $jumlah);

        $s = $http->getJson("/api/v1/finance/bank-accounts/{$akaun}/summary")->assertOk()->json('data');
        $this->assertEquals(1370.0, $s['money_in']);
        $this->assertEquals(-805.0, $s['money_out']);
        $this->assertEquals(10565.0, $s['closing_balance']);
    }

    #[Test]
    public function import_semula_fail_sama_tidak_mencipta_baris_berganda(): void
    {
        [, , $http, $akaun] = $this->sedia();
        $http->postJson("/api/v1/finance/bank-accounts/{$akaun}/import", ['csv' => self::CSV_MAYBANK])->assertCreated();
        $r = $http->postJson("/api/v1/finance/bank-accounts/{$akaun}/import", ['csv' => self::CSV_MAYBANK])->assertCreated();

        $this->assertSame(0, $r->json('data.imported'));
        $this->assertSame(4, $r->json('data.duplicates'));
        $this->assertSame(4, BankStatementLine::withoutGlobalScopes()->count());
    }

    #[Test]
    public function dua_transaksi_serupa_pada_hari_sama_kedua_duanya_diimport(): void
    {
        [, , $http, $akaun] = $this->sedia();
        $csv = "Date,Description,Amount\n2026-10-05,DUITNOW,50.00\n2026-10-05,DUITNOW,50.00\n";
        $this->assertSame(2, $http->postJson("/api/v1/finance/bank-accounts/{$akaun}/import", ['csv' => $csv])->json('data.imported'));
    }

    #[Test]
    public function padan_automatik_ikut_rujukan_dan_ikut_jumlah_tarikh(): void
    {
        [, $t, $http, $akaun] = $this->sedia();
        $p1 = $this->bayaran($t->id, 'receivable', 1250, '2026-10-03', 'REC-2026-00001');
        $p2 = $this->bayaran($t->id, 'payable', 800, '2026-10-06', 'PAY-2026-00009');

        $r = $http->postJson("/api/v1/finance/bank-accounts/{$akaun}/import", ['csv' => self::CSV_MAYBANK])->assertCreated();
        $this->assertSame(2, $r->json('data.auto_matched'));

        $l1 = BankStatementLine::withoutGlobalScopes()->where('amount', 1250)->first();
        $this->assertSame('matched', $l1->status);
        $this->assertSame('reference', $l1->match_rule);
        $this->assertSame($p1, (int) $l1->matched_id);
        $this->assertSame('amount_date', BankStatementLine::withoutGlobalScopes()->where('amount', -800)->first()->match_rule);
        $this->assertTrue((bool) DB::table('payments')->where('id', $p2)->value('is_reconciled'));

        // Caj bank & DuitNow tiada rekod: kekal belum padan.
        $this->assertSame(2, BankStatementLine::withoutGlobalScopes()->where('status', 'unmatched')->count());
    }

    #[Test]
    public function dua_calon_jumlah_sama_tanpa_rujukan_tidak_dipadankan_automatik(): void
    {
        [, $t, $http, $akaun] = $this->sedia();
        $this->bayaran($t->id, 'payable', 800, '2026-10-06', 'PAY-A');
        $this->bayaran($t->id, 'payable', 800, '2026-10-08', 'PAY-B');

        $http->postJson("/api/v1/finance/bank-accounts/{$akaun}/import", ['csv' => self::CSV_MAYBANK])->assertCreated();
        $l = BankStatementLine::withoutGlobalScopes()->where('amount', -800)->first();
        $this->assertSame('unmatched', $l->status);
        $this->assertCount(2, $http->getJson("/api/v1/finance/bank-lines/{$l->id}/candidates")->json('data'));
    }

    #[Test]
    public function padan_manual_nyahpadan_dan_abaikan(): void
    {
        [, $t, $http, $akaun] = $this->sedia();
        $http->postJson("/api/v1/finance/bank-accounts/{$akaun}/import", ['csv' => self::CSV_MAYBANK])->assertCreated();
        $this->dalamTenant($t);
        $pautan = PaymentLink::create(['provider' => 'chip', 'payable_type' => 'pos_sale', 'payable_id' => 1, 'provider_ref' => 'chip-x',
            'amount' => 120, 'status' => 'paid', 'paid_at' => '2026-10-05 10:00:00']);
        $l = BankStatementLine::withoutGlobalScopes()->where('amount', 120)->first();

        // Jumlah salah ditolak.
        $p = $this->bayaran($t->id, 'receivable', 99, '2026-10-05', 'REC-X');
        $http->postJson("/api/v1/finance/bank-lines/{$l->id}/match", ['type' => 'payment', 'id' => $p])->assertStatus(422);

        $http->postJson("/api/v1/finance/bank-lines/{$l->id}/match", ['type' => 'payment_link', 'id' => $pautan->id])
            ->assertOk()->assertJsonPath('data.status', 'matched')->assertJsonPath('data.match_rule', 'manual');
        $this->assertTrue((bool) PaymentLink::withoutGlobalScopes()->find($pautan->id)->is_reconciled);

        $http->postJson("/api/v1/finance/bank-lines/{$l->id}/unmatch")->assertOk()->assertJsonPath('data.status', 'unmatched');
        $this->assertFalse((bool) PaymentLink::withoutGlobalScopes()->find($pautan->id)->is_reconciled);

        $caj = BankStatementLine::withoutGlobalScopes()->where('amount', -5)->first();
        $http->postJson("/api/v1/finance/bank-lines/{$caj->id}/ignore")->assertOk()->assertJsonPath('data.status', 'ignored');
        $this->assertSame(1, $http->getJson("/api/v1/finance/bank-accounts/{$akaun}/summary")->json('data.ignored.count'));
    }

    #[Test]
    public function tenant_lain_tidak_nampak_atau_padan_baris_bank(): void
    {
        [, , $http, $akaun] = $this->sedia();
        $http->postJson("/api/v1/finance/bank-accounts/{$akaun}/import", ['csv' => self::CSV_MAYBANK])->assertCreated();
        $l = BankStatementLine::withoutGlobalScopes()->first();

        [$u2, $t2] = $this->daftar('lain@kedai.my', 'Kedai Lain');
        $lain = $this->sebagai($u2, $t2);
        $this->assertSame([], $lain->getJson('/api/v1/finance/bank-accounts')->json('data'));
        $lain->getJson("/api/v1/finance/bank-accounts/{$akaun}/lines")->assertNotFound();
        $lain->postJson("/api/v1/finance/bank-lines/{$l->id}/ignore")->assertNotFound();
    }

    #[Test]
    public function juruwang_tidak_boleh_akses_kewangan_termasuk_posting_jurnal(): void
    {
        [$u, $t, , $akaun] = $this->sedia();
        $kasir = $this->jemput($u, $t, 'kasir@bunga.my', ['cashier']);
        $h = $this->sebagai($kasir, $t);

        $h->getJson('/api/v1/finance/bank-accounts')->assertForbidden();
        $h->postJson("/api/v1/finance/bank-accounts/{$akaun}/import", ['csv' => self::CSV_MAYBANK])->assertForbidden();
        $h->getJson('/api/v1/finance/journal-entries')->assertForbidden();
        $h->postJson('/api/v1/finance/journal-entries', [])->assertForbidden();
        $h->postJson('/api/v1/finance/journal-entries/1/post')->assertForbidden();
        $h->getJson('/api/v1/finance/reports/profit-loss')->assertForbidden();
    }

    #[Test]
    public function format_nombor_dan_tarikh_bank(): void
    {
        $s = app(BankReconciliationService::class);
        $this->assertSame(1234.5, $s->nombor('RM1,234.50'));
        $this->assertSame(-12.0, $s->nombor('(12.00)'));
        $this->assertSame(-12.0, $s->nombor('12.00 DR'));
        $this->assertSame(12.0, $s->nombor('12.00CR'));
        $this->assertSame(-5.0, $s->nombor('5.00-'));
        $this->assertSame(1234.5, $s->nombor('1.234,50'));
        $this->assertNull($s->nombor(''));
        $this->assertSame('2026-10-05', $s->tarikh('05/10/2026')->toDateString());
        $this->assertSame('2026-10-05', $s->tarikh('5/10/2026')->toDateString());
        $this->assertSame('2026-10-05', $s->tarikh('05 Oct 2026')->toDateString());
        $this->assertSame('2026-10-05', $s->tarikh('2026-10-05')->toDateString());
        $this->assertNull($s->tarikh('32/13/2026'));
    }

    #[Test]
    public function csv_tanpa_tajuk_dikenali_ditolak_dengan_mesej_jelas(): void
    {
        [, , $http, $akaun] = $this->sedia();
        $http->postJson("/api/v1/finance/bank-accounts/{$akaun}/import", ['csv' => "a,b,c\n1,2,3"])
            ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'Tajuk lajur'));
    }
}
