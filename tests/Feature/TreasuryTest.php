<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** FIN-006 Treasury: kedudukan tunai & ramalan aliran tunai. */
class TreasuryTest extends TestCase
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
        [$u, $t] = $this->daftar('cfo@bunga.my', 'Kedai Bunga Sari');
        $http = $this->sebagai($u, $t);
        $bank = $http->postJson('/api/v1/finance/bank-accounts', ['name' => 'Maybank'])->assertCreated()->json('data.id');
        $hari = now()->format('d/m/Y');
        $http->postJson("/api/v1/finance/bank-accounts/{$bank}/import", ['csv' => "Date,Description,Amount,Balance\n{$hari},JUALAN,500.00,5000.00\n"])->assertCreated();
        $peti = $http->postJson('/api/v1/finance/cash-boxes', ['name' => 'Petty', 'type' => 'petty', 'float_amount' => 300])->assertCreated()->json('data.id');
        $http->postJson("/api/v1/finance/cash-boxes/{$peti}/top-up")->assertCreated();

        return [$u, $t, $http];
    }

    private function invois(int $tenant, string $no, float $baki, string $matang, string $status = 'issued'): void
    {
        DB::table('invoices')->insert(['tenant_id' => $tenant, 'invoice_number' => $no, 'status' => $status, 'issue_date' => now()->subDays(10)->toDateString(),
            'due_date' => $matang, 'grand_total' => $baki, 'amount_outstanding' => $baki, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function bil(int $tenant, string $no, float $baki, string $matang, string $status = 'pending'): void
    {
        DB::table('accounts_payable')->insert(['tenant_id' => $tenant, 'invoice_number' => $no, 'invoice_date' => now()->subDays(20)->toDateString(),
            'due_date' => $matang, 'original_amount' => $baki, 'outstanding_amount' => $baki, 'status' => $status, 'created_at' => now(), 'updated_at' => now()]);
    }

    #[Test]
    public function kedudukan_tunai_bank_dan_peti(): void
    {
        [, , $http] = $this->sedia();
        $d = $http->getJson('/api/v1/finance/treasury/position')->assertOk()->json('data');
        $this->assertEquals(5000.0, $d['bank_total']);
        $this->assertEquals(300.0, $d['cash_total']);
        $this->assertEquals(5300.0, $d['total']);
        $this->assertSame([], $d['warnings']);
    }

    #[Test]
    public function ramalan_masuk_keluar_ikut_tarikh_matang_dan_titik_terendah(): void
    {
        [, $t, $http] = $this->sedia();
        $h = fn (int $n) => now()->addDays($n)->toDateString();
        $this->invois($t->id, 'INV-1', 1200, $h(5));
        $this->invois($t->id, 'INV-LEWAT', 800, $h(-3), 'overdue');
        $this->invois($t->id, 'INV-DRAF', 9999, $h(2), 'draft');
        $this->invois($t->id, 'INV-JAUH', 700, $h(45));
        $this->bil($t->id, 'BIL-1', 6000, $h(2));
        $this->bil($t->id, 'BIL-LEWAT', 100, $h(-1), 'overdue');
        $this->bil($t->id, 'BIL-SIAP', 50, $h(3), 'fully_paid');

        $d = $http->getJson('/api/v1/finance/treasury/forecast?days=30&threshold=1000')->assertOk()->json('data');
        $this->assertEquals(5300.0, $d['opening']);
        $this->assertEquals(1200.0, $d['total_in']);              // lewat & draf & >30 hari tidak dikira
        $this->assertEquals(6100.0, $d['total_out']);             // BIL-1 + bil lewat (hari ini)
        $this->assertEquals(400.0, $d['closing']);
        $this->assertEquals(-800.0, $d['lowest']['balance']);     // 5300 - 100 - 6000
        $this->assertSame($h(2), $d['lowest']['date']);
        $this->assertEquals(800.0, $d['overdue_receivables']);
        $this->assertEquals(100.0, $d['overdue_payables']);
        $this->assertContains($h(2), $d['days_below_threshold']);
        $this->assertCount(30, $d['days']);

        $d2 = $http->getJson('/api/v1/finance/treasury/forecast?days=30&include_overdue=1')->json('data');
        $this->assertEquals(2000.0, $d2['total_in']);
        $this->assertEquals(1200.0, $d2['closing']);
    }

    #[Test]
    public function amaran_bila_akaun_bank_tiada_penyata_dan_akses_dihadkan(): void
    {
        [$u, $t, $http] = $this->sedia();
        $http->postJson('/api/v1/finance/bank-accounts', ['name' => 'CIMB Baharu'])->assertCreated();
        $this->assertCount(1, $http->getJson('/api/v1/finance/treasury/position')->json('data.warnings'));

        $kasir = $this->jemput($u, $t, 'kasir@bunga.my', ['cashier']);
        $this->sebagai($kasir, $t)->getJson('/api/v1/finance/treasury/forecast')->assertForbidden();
    }
}
