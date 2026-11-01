<?php

namespace Tests\Feature;

use App\Modules\Finance\Models\BankStatementLine;
use App\Modules\Finance\Models\CashBoxEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** FIN-018 Pengurusan Tunai: petty cash, peti besi, kiraan denominasi, deposit bank. */
class CashManagementTest extends TestCase
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
        [$u, $t] = $this->daftar('tunai@bunga.my', 'Kedai Bunga Sari');
        $http = $this->sebagai($u, $t);
        $petty = $http->postJson('/api/v1/finance/cash-boxes', ['name' => 'Petty Cash Kedai', 'type' => 'petty', 'float_amount' => 300])
            ->assertCreated()->json('data.id');

        return [$u, $t, $http, $petty];
    }

    #[Test]
    public function petty_cash_tambah_float_belanja_dan_laporan_ikut_kategori(): void
    {
        [, , $http, $petty] = $this->sedia();

        $http->postJson("/api/v1/finance/cash-boxes/{$petty}/top-up")->assertCreated()->assertJsonPath('data.amount', '300.00');
        $http->postJson("/api/v1/finance/cash-boxes/{$petty}/entries", ['type' => 'out', 'amount' => 12.5, 'category' => 'Pengangkutan', 'reference' => 'R-01'])->assertCreated();
        $http->postJson("/api/v1/finance/cash-boxes/{$petty}/entries", ['type' => 'out', 'amount' => 30, 'category' => 'Alat tulis'])->assertCreated();
        $http->postJson("/api/v1/finance/cash-boxes/{$petty}/entries", ['type' => 'out', 'amount' => 7.5, 'category' => 'Pengangkutan'])->assertCreated();

        $baki = collect($http->getJson('/api/v1/finance/cash-boxes')->json('data'))->firstWhere('id', $petty)['balance'];
        $this->assertEquals(250.0, $baki);

        // Tambah float hanya isi semula kekurangan.
        $http->postJson("/api/v1/finance/cash-boxes/{$petty}/top-up")->assertCreated()->assertJsonPath('data.amount', '50.00');
        $http->postJson("/api/v1/finance/cash-boxes/{$petty}/top-up")->assertStatus(422);

        $hari = now()->toDateString();
        $lap = $http->getJson("/api/v1/finance/cash-boxes/{$petty}/report?from={$hari}&to={$hari}")->assertOk()->json('data');
        $this->assertEquals(0.0, $lap['opening']);
        $this->assertEquals(50.0, $lap['out']);
        $this->assertEquals(300.0, $lap['closing']);
        $this->assertEquals(['Alat tulis' => 30.0, 'Pengangkutan' => 20.0], $lap['out_by_category']);
    }

    #[Test]
    public function tidak_boleh_keluar_melebihi_baki_atau_amaun_tidak_sah(): void
    {
        [, , $http, $petty] = $this->sedia();
        $http->postJson("/api/v1/finance/cash-boxes/{$petty}/entries", ['type' => 'in', 'amount' => 20])->assertCreated();
        $http->postJson("/api/v1/finance/cash-boxes/{$petty}/entries", ['type' => 'out', 'amount' => 20.01])
            ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'tidak mencukupi'));
        $http->postJson("/api/v1/finance/cash-boxes/{$petty}/entries", ['type' => 'out', 'amount' => 1.005])->assertStatus(422);
        $http->postJson("/api/v1/finance/cash-boxes/{$petty}/entries", ['type' => 'adjust', 'amount' => 5])->assertStatus(422);
    }

    #[Test]
    public function kiraan_denominasi_varians_dan_pelarasan_perlu_kebenaran_lulus(): void
    {
        [$u, $t, $http, $petty] = $this->sedia();
        $http->postJson("/api/v1/finance/cash-boxes/{$petty}/top-up")->assertCreated();

        // 2×RM100 + 1×RM50 + 4×RM10 + 3×50sen + 5×10sen = 292.00 (kurang RM8)
        $k = $http->postJson('/api/v1/finance/cash-counts', ['cash_box_id' => $petty,
            'denominations' => ['100' => 2, '50' => 1, '10' => 4, '0.5' => 3, '0.10' => 5, '20' => 0]])->assertCreated()->json('data');
        $this->assertEquals(292.0, (float) $k['counted_total']);
        $this->assertEquals(300.0, (float) $k['expected_total']);
        $this->assertEquals(-8.0, (float) $k['variance']);
        $this->assertSame(['100' => 2, '50' => 1, '10' => 4, '0.50' => 3, '0.10' => 5], $k['denominations']);

        $http->postJson('/api/v1/finance/cash-counts', ['cash_box_id' => $petty, 'denominations' => ['3' => 1]])->assertStatus(422);
        $http->postJson('/api/v1/finance/cash-counts', ['cash_box_id' => $petty, 'denominations' => ['10' => -1]])->assertStatus(422);

        // Pelarasan perlu finance.approve; staf biasa tidak boleh.
        $staf = $this->jemput($u, $t, 'staf@bunga.my', ['staff']);
        $this->sebagai($staf, $t)->postJson("/api/v1/finance/cash-counts/{$k['id']}/adjust")->assertForbidden();
        $http = $this->sebagai($u, $t);

        $http->postJson("/api/v1/finance/cash-counts/{$k['id']}/adjust")->assertCreated()->assertJsonPath('data.amount', '-8.00')->assertJsonPath('data.type', 'adjust');
        $baki = collect($http->getJson('/api/v1/finance/cash-boxes')->json('data'))->firstWhere('id', $petty)['balance'];
        $this->assertEquals(292.0, $baki);
        $http->postJson("/api/v1/finance/cash-counts/{$k['id']}/adjust")->assertStatus(422);
    }

    #[Test]
    public function drop_laci_pos_ke_peti_besi_dihadkan_kepada_jumlah_drop_dan_kiraan_syif(): void
    {
        [, , $http] = $this->sedia();
        $peti = $http->postJson('/api/v1/finance/cash-boxes', ['name' => 'Peti Besi', 'type' => 'safe'])->assertCreated()->json('data.id');
        $reg = $http->postJson('/api/v1/pos/registers', ['name' => 'Kaunter 1'])->assertCreated();
        $regId = $reg->json('id') ?? $reg->json('data.id');
        $syif = $http->postJson('/api/v1/pos/shifts/open', ['register_id' => $regId, 'opening_cash' => 200])->assertCreated()->json('id');
        $http->postJson("/api/v1/pos/shifts/{$syif}/cash", ['type' => 'drop', 'amount' => 150, 'reason' => 'Ke peti besi'])->assertSuccessful();

        $http->postJson("/api/v1/finance/cash-boxes/{$peti}/receive-drop", ['cash_shift_id' => $syif, 'amount' => 100])->assertCreated();
        $http->postJson("/api/v1/finance/cash-boxes/{$peti}/receive-drop", ['cash_shift_id' => $syif, 'amount' => 50.01])->assertStatus(422);
        $http->postJson("/api/v1/finance/cash-boxes/{$peti}/receive-drop", ['cash_shift_id' => $syif, 'amount' => 50])->assertCreated();

        // Laci: 200 float - 150 drop = 50 dijangka; dikira 1×RM50.
        $k = $http->postJson('/api/v1/finance/cash-counts', ['cash_shift_id' => $syif, 'denominations' => ['50' => 1]])->assertCreated()->json('data');
        $this->assertEquals(50.0, (float) $k['expected_total']);
        $this->assertEquals(0.0, (float) $k['variance']);
        $http->postJson("/api/v1/finance/cash-counts/{$k['id']}/adjust")->assertStatus(422);
    }

    #[Test]
    public function deposit_tunai_dipadankan_automatik_dalam_rekonsiliasi_bank(): void
    {
        [, , $http] = $this->sedia();
        $peti = $http->postJson('/api/v1/finance/cash-boxes', ['name' => 'Peti Besi', 'type' => 'safe'])->assertCreated()->json('data.id');
        $bank = $http->postJson('/api/v1/finance/bank-accounts', ['name' => 'Maybank'])->assertCreated()->json('data.id');
        $http->postJson("/api/v1/finance/cash-boxes/{$peti}/entries", ['type' => 'in', 'amount' => 1000])->assertCreated();

        $hari = now()->format('d/m/Y');
        $http->postJson("/api/v1/finance/cash-boxes/{$peti}/deposit", ['bank_account_id' => $bank, 'amount' => 1200])->assertStatus(422);
        $dep = $http->postJson("/api/v1/finance/cash-boxes/{$peti}/deposit", ['bank_account_id' => $bank, 'amount' => 850, 'reference' => 'SLIP-7788'])
            ->assertCreated()->json('data.id');

        $csv = "Date,Description,Debit,Credit\n{$hari},CASH DEPOSIT SLIP-7788,,850.00\n";
        $http->postJson("/api/v1/finance/bank-accounts/{$bank}/import", ['csv' => $csv])->assertCreated()->assertJsonPath('data.auto_matched', 1);

        $l = BankStatementLine::withoutGlobalScopes()->first();
        $this->assertSame('cash_deposit', $l->matched_type);
        $this->assertSame($dep, (int) $l->matched_id);
        $this->assertTrue((bool) CashBoxEntry::withoutGlobalScopes()->find($dep)->is_reconciled);

        $http->postJson("/api/v1/finance/bank-lines/{$l->id}/unmatch")->assertOk();
        $this->assertFalse((bool) CashBoxEntry::withoutGlobalScopes()->find($dep)->is_reconciled);
    }

    #[Test]
    public function tenant_lain_dan_juruwang_tidak_boleh_akses_peti_tunai(): void
    {
        [$u, $t, $http, $petty] = $this->sedia();
        [$u2, $t2] = $this->daftar('lain@kedai.my', 'Kedai Lain');
        $lain = $this->sebagai($u2, $t2);
        $this->assertSame([], $lain->getJson('/api/v1/finance/cash-boxes')->json('data'));
        $lain->postJson("/api/v1/finance/cash-boxes/{$petty}/entries", ['type' => 'in', 'amount' => 5])->assertNotFound();

        $kasir = $this->jemput($u, $t, 'kasir@bunga.my', ['cashier']);
        $this->sebagai($kasir, $t)->postJson("/api/v1/finance/cash-boxes/{$petty}/entries", ['type' => 'out', 'amount' => 5])->assertForbidden();
    }
}
