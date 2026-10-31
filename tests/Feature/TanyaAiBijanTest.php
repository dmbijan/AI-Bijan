<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Fasa 1.7 — "Tanya AI Bijan": soalan BM dijawab daripada data sebenar melalui alat ejen. */
class TanyaAiBijanTest extends TestCase
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
        [$u, $t] = $this->daftar('kedai@bunga.my', 'Kedai Bunga');
        $http = $this->sebagai($u, $t);
        $http->postJson('/api/v1/ai-agents/tools/run', ['tool' => 'penyediaan.sediakan_kedai', 'params' => ['nama_kedai' => 'Kedai Bunga', 'menu' => "Ros 25, 0\nTulip 38"]])->assertOk();

        return [$u, $t, $http];
    }

    private function tanya($http, string $s)
    {
        return $http->postJson('/api/v1/ai-agents/tanya', ['soalan' => $s])->assertOk();
    }

    #[Test]
    public function jualan_hari_ini(): void
    {
        [, , $http] = $this->sedia();
        $this->assertStringContainsString('Belum ada jualan', $this->tanya($http, 'Jualan hari ni?')->json('jawapan'));

        $reg = \App\Modules\POS\Models\POSRegister::withoutGlobalScopes()->value('id');
        $http->postJson('/api/v1/pos/sales/checkout', ['register_id' => $reg, 'items' => [['product_name' => 'Tulip', 'quantity' => 1, 'unit_price' => 38]],
            'tenders' => [['method' => 'cash', 'amount' => 38]]])->assertCreated();

        $this->assertStringContainsString('RM 38.00 daripada 1 transaksi', $this->tanya($http, 'berapa jualan hari ni')->json('jawapan'));
    }

    #[Test]
    public function stok_dan_hutang_dan_tutup_kedai(): void
    {
        [, , $http] = $this->sedia();

        $this->assertStringContainsString('Ros (baki 0)', $this->tanya($http, 'stok apa nak habis?')->json('jawapan'));
        $this->assertStringContainsString('Tiada invois lewat bayar', $this->tanya($http, 'siapa belum bayar?')->json('jawapan'));
        $this->assertStringContainsString('*Tutup kedai', $this->tanya($http, 'laporan tutup kedai')->json('jawapan'));
    }

    #[Test]
    public function soalan_tidak_difahami_dijawab_jujur(): void
    {
        [, , $http] = $this->sedia();

        $r = $this->tanya($http, 'cuaca esok macam mana');
        $this->assertFalse($r->json('faham'));
        $this->assertStringContainsString('belum faham', $r->json('jawapan'));
    }
}
