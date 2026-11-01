<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Penyata Untung Rugi mesti betul tanda: hasil positif, untung = hasil - kos - belanja. */
class ProfitLossReportTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    #[Test]
    public function untung_rugi_dikira_dengan_tanda_yang_betul(): void
    {
        $this->setUpAkses();
        [$u, $t] = $this->daftar('akaun@bunga.my', 'Kedai Bunga Sari');
        $http = $this->sebagai($u, $t);
        $akaun = fn (string $kod, string $jenis, ?string $sub = null) => $http->postJson('/api/v1/finance/chart-of-accounts',
            array_filter(['code' => $kod, 'name' => $kod, 'type' => $jenis, 'subtype' => $sub]))->assertCreated()->json('id') ?? null;

        $bank = $akaun('T1000', 'asset', 'bank');
        $hasil = $akaun('T4000', 'revenue', 'operating_revenue');
        $kos = $akaun('T5000', 'cost_of_goods');
        $belanja = $akaun('T6000', 'expense', 'operating_expense');
        $cukai = $akaun('T7000', 'expense', 'tax_expense');

        $jurnal = function (array $baris) use ($http) {
            $id = $http->postJson('/api/v1/finance/journal-entries', ['entry_date' => '2026-10-15', 'lines' => $baris])->assertCreated()->json('id');
            $http->postJson("/api/v1/finance/journal-entries/{$id}/post")->assertSuccessful();
        };
        $jurnal([['account_id' => $bank, 'debit' => 1000, 'credit' => 0], ['account_id' => $hasil, 'debit' => 0, 'credit' => 1000]]);
        $jurnal([['account_id' => $kos, 'debit' => 400, 'credit' => 0], ['account_id' => $bank, 'debit' => 0, 'credit' => 400]]);
        $jurnal([['account_id' => $belanja, 'debit' => 300, 'credit' => 0], ['account_id' => $bank, 'debit' => 0, 'credit' => 300]]);
        $jurnal([['account_id' => $cukai, 'debit' => 50, 'credit' => 0], ['account_id' => $bank, 'debit' => 0, 'credit' => 50]]);

        $r = $http->getJson('/api/v1/finance/reports/profit-loss?date_from=2026-10-01&date_to=2026-10-31')->assertOk()->json();
        $s = $r['sections'];
        $this->assertEquals(1000.0, $s['revenue']['total']);
        $this->assertEquals(1000.0, $s['revenue']['accounts'][0]['amount']);
        $this->assertEquals(400.0, $s['cost_of_goods']['total']);
        $this->assertEquals(600.0, $s['gross_profit']['amount']);
        $this->assertEquals(300.0, $s['operating_expenses']['total']);
        $this->assertEquals(300.0, $s['net_profit_before_tax']['amount']);
        $this->assertEquals(50.0, $s['tax_expense']['total']);
        $this->assertEquals(250.0, $s['net_profit_after_tax']['amount']);

        // Kunci kira-kira mesti seimbang: modal 5000 + pinjaman 2000 + hutang pembekal 100.
        $modal = $akaun('T3000', 'equity');
        $pinjam = $akaun('T2500', 'liability', 'non_current_liability');
        $ap = $akaun('T2100', 'liability', 'accounts_payable');
        $stok = $akaun('T1300', 'asset', 'inventory');
        $mesin = $akaun('T1500', 'asset', 'non_current_asset');
        $jurnal([['account_id' => $bank, 'debit' => 5000, 'credit' => 0], ['account_id' => $modal, 'debit' => 0, 'credit' => 5000]]);
        $jurnal([['account_id' => $mesin, 'debit' => 2000, 'credit' => 0], ['account_id' => $pinjam, 'debit' => 0, 'credit' => 2000]]);
        $jurnal([['account_id' => $stok, 'debit' => 100, 'credit' => 0], ['account_id' => $ap, 'debit' => 0, 'credit' => 100]]);

        $kk = $http->getJson('/api/v1/finance/reports/balance-sheet?as_of_date=2026-10-31')->assertOk()->json();
        $b = $kk['sections'];
        $tot = $kk['totals'];
        // Aset: bank 1000-400-300-50+5000 = 5250 + stok 100 (semasa), mesin 2000 (bukan semasa) = 7350
        $this->assertEquals(5350.0, $b['assets']['subsections']['current_assets']['total']);
        $this->assertEquals(2000.0, $b['assets']['subsections']['non_current_assets']['total']);
        $this->assertEquals(100.0, $b['liabilities']['subsections']['current_liabilities']['total']);
        $this->assertEquals(2000.0, $b['liabilities']['subsections']['non_current_liabilities']['total']);
        $this->assertEquals(5000.0, $b['equity']['total']);
        $this->assertEquals(250.0, $b['retained_earnings']['amount']);
        $this->assertEquals(7350.0, $tot['total_assets']);
        $this->assertEquals(0.0, $tot['difference']);
    }
}
