<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** FIN-005 Bajet vs sebenar. */
class BudgetTest extends TestCase
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
        [$u, $t] = $this->daftar('fpa@bunga.my', 'Kedai Bunga Sari');
        $http = $this->sebagai($u, $t);
        $akaun = fn (string $kod, string $jenis, ?string $sub = null) => $http->postJson('/api/v1/finance/chart-of-accounts',
            array_filter(['code' => $kod, 'name' => $kod, 'type' => $jenis, 'subtype' => $sub]))->assertCreated()->json('id');
        $a = ['bank' => $akaun('B1000', 'asset', 'bank'), 'hasil' => $akaun('B4000', 'revenue'), 'kos' => $akaun('B5000', 'cost_of_goods'),
            'sewa' => $akaun('B6100', 'expense'), 'iklan' => $akaun('B6200', 'expense')];
        $jurnal = function (string $tarikh, int $dr, int $cr, float $amaun) use ($http) {
            $id = $http->postJson('/api/v1/finance/journal-entries', ['entry_date' => $tarikh, 'lines' => [
                ['account_id' => $dr, 'debit' => $amaun, 'credit' => 0], ['account_id' => $cr, 'debit' => 0, 'credit' => $amaun]]])->assertCreated()->json('id');
            $http->postJson("/api/v1/finance/journal-entries/{$id}/post")->assertSuccessful();
        };

        return [$u, $t, $http, $a, $jurnal];
    }

    #[Test]
    public function bajet_tahunan_dibahagi_bulanan_dan_dibanding_dengan_sebenar(): void
    {
        [, , $http, $a, $jurnal] = $this->sedia();
        $b = $http->postJson('/api/v1/finance/budgets', ['name' => 'Bajet 2026', 'fiscal_year' => 2026])->assertCreated()->json('data.id');
        $http->putJson("/api/v1/finance/budgets/{$b}/lines", ['lines' => [
            ['account_id' => $a['hasil'], 'annual' => 120000],
            ['account_id' => $a['kos'], 'annual' => 48000],
            ['account_id' => $a['sewa'], 'annual' => 1000], // 83.33 × 11 + 83.37 Disember
            ['account_id' => $a['iklan'], 'months' => [500, 500, 500, 500, 500, 500, 500, 500, 500, 2000, 500, 500]],
        ]])->assertOk();

        // Oktober sebenar: hasil 9000 (bajet 10000), kos 4000 (4000), sewa 83.33 (83.33), iklan 2600 (2000, +30%).
        $jurnal('2026-10-05', $a['bank'], $a['hasil'], 9000);
        $jurnal('2026-10-06', $a['kos'], $a['bank'], 4000);
        $jurnal('2026-10-07', $a['sewa'], $a['bank'], 83.33);
        $jurnal('2026-10-08', $a['iklan'], $a['bank'], 2600);
        $jurnal('2026-09-30', $a['bank'], $a['hasil'], 5555); // luar julat

        $d = $http->getJson("/api/v1/finance/budgets/{$b}/report?from_month=10&to_month=10")->assertOk()->json('data');
        $ikut = collect($d['lines'])->keyBy('code');
        $this->assertSame('B4000', $d['lines'][0]['code']); // hasil di atas
        $this->assertEquals(10000.0, $ikut['B4000']['budget']);
        $this->assertEquals(9000.0, $ikut['B4000']['actual']);
        $this->assertEquals(-10.0, $ikut['B4000']['variance_pct']);
        $this->assertFalse($ikut['B4000']['favourable']);
        $this->assertFalse($ikut['B4000']['alert']); // tepat 10% tidak melebihi ambang
        $this->assertEquals(2000.0, $ikut['B6200']['budget']);
        $this->assertEquals(30.0, $ikut['B6200']['variance_pct']);
        $this->assertTrue($ikut['B6200']['alert']);
        $this->assertTrue($ikut['B6100']['favourable']);
        $this->assertSame(1, $d['alerts']);
        $this->assertEquals(10000 - 4000 - 83.33 - 2000, $d['totals']['profit']['budget']);
        $this->assertEquals(9000 - 4000 - 83.33 - 2600, $d['totals']['profit']['actual']);

        $tahun = $http->getJson("/api/v1/finance/budgets/{$b}/report")->json('data');
        $this->assertEquals(1000.0, collect($tahun['lines'])->firstWhere('code', 'B6100')['budget']);
        $this->assertEquals(14555.0, collect($tahun['lines'])->firstWhere('code', 'B4000')['actual']);
    }

    #[Test]
    public function bajet_lulus_dikunci_dan_kelulusan_perlu_kebenaran(): void
    {
        [$u, $t, $http, $a] = $this->sedia();
        $b = $http->postJson('/api/v1/finance/budgets', ['name' => 'Bajet 2027', 'fiscal_year' => 2027])->assertCreated()->json('data.id');
        $http->postJson('/api/v1/finance/budgets', ['name' => 'Bajet 2027', 'fiscal_year' => 2027])->assertStatus(422);
        $http->postJson("/api/v1/finance/budgets/{$b}/approve")->assertStatus(422); // kosong
        $http->putJson("/api/v1/finance/budgets/{$b}/lines", ['lines' => [['account_id' => $a['sewa'], 'months' => [1, 2]]]])->assertStatus(422);
        $http->putJson("/api/v1/finance/budgets/{$b}/lines", ['lines' => [['account_id' => $a['sewa'], 'annual' => 1200]]])->assertOk();

        $staf = $this->jemput($u, $t, 'staf@bunga.my', ['staff']);
        $this->sebagai($staf, $t)->postJson("/api/v1/finance/budgets/{$b}/approve")->assertForbidden();
        $http = $this->sebagai($u, $t);

        $http->postJson("/api/v1/finance/budgets/{$b}/approve")->assertOk()->assertJsonPath('data.status', 'approved');
        $http->putJson("/api/v1/finance/budgets/{$b}/lines", ['lines' => [['account_id' => $a['sewa'], 'annual' => 9999]]])
            ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'diluluskan'));

        [$u2, $t2] = $this->daftar('lain@kedai.my', 'Kedai Lain');
        $this->sebagai($u2, $t2)->getJson("/api/v1/finance/budgets/{$b}/report")->assertNotFound();
    }
}
