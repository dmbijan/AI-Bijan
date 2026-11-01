<?php

namespace Tests\Feature;

use App\Models\ApprovalRequest;
use App\Models\Item;
use App\Models\Warehouse;
use App\Modules\Inventory\StockEngine;
use App\Modules\Invoicing\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Fasa 1.1 — asas Agentic AI: alat sebenar, kebenaran, Peti Kelulusan, pengesahan hasil, tiada "berjaya" palsu. */
class AgenticAsasTest extends TestCase
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
        [$u, $t] = $this->daftar('kedai@bunga.my', 'Kedai Bunga Sari');
        $this->dalamTenant($t);
        $ros = Item::create(['sku' => 'ROS', 'name' => 'Jambangan Ros', 'uom' => 'unit', 'sell_price' => 25, 'reorder_level' => 5, 'is_stock' => true]);
        $wh = Warehouse::create(['code' => 'KDI', 'name' => 'Kedai', 'type' => 'outlet']);
        app(StockEngine::class)->receive($ros->id, $wh->id, null, 3, 10, 'adjustment', 1, 'Stok awal');

        return [$u, $t, $ros];
    }

    private function jalan($http, string $tool, array $params = [])
    {
        return $http->postJson('/api/v1/ai-agents/tools/run', ['tool' => $tool, 'agent' => 'ejen_kaunter', 'params' => $params]);
    }

    #[Test]
    public function senarai_alat_menunjukkan_risiko_dan_kebenaran(): void
    {
        [$u, $t] = $this->sedia();

        $alat = collect($this->sebagai($u, $t)->getJson('/api/v1/ai-agents/tools')->assertOk()->json('data'))->keyBy('name');

        $this->assertSame('tinggi', $alat['produk.ubah_harga']['risk']);
        $this->assertSame('rendah', $alat['pos.ringkasan_hari']['risk']);
        $this->assertSame('inventory.manage', $alat['produk.ubah_harga']['permission']);
    }

    #[Test]
    public function alat_baca_memulangkan_data_sebenar(): void
    {
        [$u, $t] = $this->sedia();
        $http = $this->sebagai($u, $t);
        $reg = $http->postJson('/api/v1/pos/registers', ['name' => 'Kaunter 1', 'code' => 'K1'])->assertCreated();
        $http->postJson('/api/v1/pos/sales/checkout', [
            'register_id' => $reg->json('id') ?? $reg->json('data.id'),
            'items' => [['product_name' => 'Kad', 'quantity' => 2, 'unit_price' => 5]],
            'tenders' => [['method' => 'cash', 'amount' => 10]],
        ])->assertCreated();

        $this->jalan($http, 'pos.ringkasan_hari')->assertOk()->assertJsonPath('hasil.bilangan_jualan', 1)->assertJsonPath('hasil.jumlah', 10);
        $this->jalan($http, 'stok.rendah')->assertOk()->assertJsonPath('hasil.senarai.0.sku', 'ROS')->assertJsonPath('hasil.senarai.0.baki', 3);
    }

    #[Test]
    public function laporan_tutup_kedai_dalam_bm(): void
    {
        [$u, $t] = $this->sedia();
        $http = $this->sebagai($u, $t);
        $reg = $http->postJson('/api/v1/pos/registers', ['name' => 'Kaunter 1', 'code' => 'K1'])->assertCreated();
        $http->postJson('/api/v1/pos/sales/checkout', [
            'register_id' => $reg->json('id') ?? $reg->json('data.id'),
            'items' => [['product_name' => 'Jambangan Ros', 'quantity' => 2, 'unit_price' => 25], ['product_name' => 'Kad', 'quantity' => 1, 'unit_price' => 5]],
            'tenders' => [['method' => 'duitnow', 'amount' => 55]],
        ])->assertCreated();

        $r = $this->jalan($http, 'pos.laporan_tutup_kedai')->assertOk();

        $teks = $r->json('hasil.teks');
        $this->assertStringContainsString('Jualan: RM 55.00 (1 transaksi', $teks);
        $this->assertStringContainsString('duitnow: RM 55.00', $teks);
        $this->assertStringContainsString('Terlaris: Jambangan Ros ×2', $teks);
        $this->assertSame('Jambangan Ros', $r->json('hasil.terlaris.0.nama'));
    }

    #[Test]
    public function ejen_penyediaan_siapkan_kedai_dari_teks_menu(): void
    {
        [$u, $t] = $this->daftar('kafe@baru.my', 'Kafe Baru');
        $http = $this->sebagai($u, $t);
        $menu = "1. Nasi Lemak Ayam 8.50\n- Teh Tarik RM3\nKopi O, 2.80, 50\n7Up 3.00\nTajuk tanpa harga";

        $r = $this->jalan($http, 'penyediaan.sediakan_kedai', ['nama_kedai' => 'Kafe Baru', 'menu' => $menu, 'kategori' => 'Makanan'])->assertOk();
        $this->assertSame(4, $r->json('hasil.produk_dicipta'));

        // Sekali lagi: tiada pendua
        $this->assertSame(4, $this->jalan($http, 'penyediaan.sediakan_kedai', ['nama_kedai' => 'Kafe Baru', 'menu' => $menu])->json('hasil.produk_sedia_ada'));

        $this->dalamTenant($t);
        $this->assertEquals(8.5, (float) Item::where('name', 'Nasi Lemak Ayam')->value('sell_price'));
        $this->assertTrue(Item::where('name', '7Up')->exists());
        $kopi = Item::where('name', 'Kopi O')->first();
        $this->assertTrue($kopi->is_stock);
        $this->assertEquals(50, (float) app(StockEngine::class)->onHand($kopi->id, Warehouse::where('type', 'outlet')->value('id')));
        $this->assertNotNull(\App\Modules\POS\Models\POSRegister::query()->value('warehouse_id'), 'Kaunter dipaut ke gudang.');

        // Terus boleh jual
        $reg = \App\Modules\POS\Models\POSRegister::query()->value('id');
        $http->postJson('/api/v1/pos/sales/checkout', [
            'register_id' => $reg, 'items' => [['product_id' => $kopi->id, 'product_name' => 'Kopi O', 'quantity' => 2, 'unit_price' => 2.8]],
            'tenders' => [['method' => 'cash', 'amount' => 5.6]],
        ])->assertCreated();
        $this->dalamTenant($t);
        $this->assertEquals(48, (float) app(StockEngine::class)->onHand($kopi->id, Warehouse::where('type', 'outlet')->value('id')));
    }

    #[Test]
    public function invois_lewat_bayar_disenaraikan(): void
    {
        [$u, $t] = $this->sedia();
        Invoice::create(['tenant_id' => $t->id, 'customer_name' => 'Pak Ali', 'issue_date' => now()->subDays(40)->toDateString(),
            'due_date' => now()->subDays(10)->toDateString(), 'subtotal' => 100, 'tax_total' => 0, 'grand_total' => 100,
            'amount_paid' => 0, 'status' => 'issued', 'currency' => 'MYR']);

        $r = $this->jalan($this->sebagai($u, $t), 'invois.lewat_bayar')->assertOk();
        $this->assertSame(1, $r->json('hasil.bilangan'));
        $this->assertEquals(100, $r->json('hasil.jumlah_tertunggak'));
    }

    #[Test]
    public function ubah_harga_menunggu_kelulusan_dan_tidak_berubah_dahulu(): void
    {
        [$u, $t, $ros] = $this->sedia();

        $r = $this->jalan($this->sebagai($u, $t), 'produk.ubah_harga', ['sku' => 'ROS', 'harga_baharu' => 30])->assertStatus(202);

        $this->assertSame('menunggu_kelulusan', $r->json('status'));
        $this->assertFalse($r->json('berjaya'));
        $this->assertStringContainsString('RM 25.00 kepada RM 30.00', $r->json('mesej'));
        $this->assertEquals(25, (float) $ros->fresh()->sell_price);
    }

    #[Test]
    public function diluluskan_baru_dilaksanakan_dan_disahkan(): void
    {
        [$u, $t, $ros] = $this->sedia();
        $http = $this->sebagai($u, $t);
        $id = $this->jalan($http, 'produk.ubah_harga', ['sku' => 'ROS', 'harga_baharu' => 30])->json('approval_id');

        $http->postJson("/api/v1/approvals/{$id}/decide", ['decision' => 'approved'])->assertOk();

        $this->dalamTenant($t);
        $this->assertEquals(30, (float) $ros->fresh()->sell_price);
        $hasil = ApprovalRequest::find($id)->payload['hasil'];
        $this->assertSame('berjaya', $hasil['status']);
        $this->assertEquals(25, $hasil['hasil']['harga_lama']);
    }

    #[Test]
    public function ditolak_tiada_perubahan(): void
    {
        [$u, $t, $ros] = $this->sedia();
        $http = $this->sebagai($u, $t);
        $id = $this->jalan($http, 'produk.ubah_harga', ['sku' => 'ROS', 'harga_baharu' => 1])->json('approval_id');

        $http->postJson("/api/v1/approvals/{$id}/decide", ['decision' => 'rejected', 'reason' => 'Terlalu murah'])->assertOk();

        $this->dalamTenant($t);
        $this->assertEquals(25, (float) $ros->fresh()->sell_price);
        $this->assertArrayNotHasKey('hasil', ApprovalRequest::find($id)->payload);
    }

    #[Test]
    public function ejen_tidak_lebih_berkuasa_daripada_pengguna(): void
    {
        [$u, $t, $ros] = $this->sedia();
        $kewangan = $this->jemput($u, $t, 'akaun@bunga.my', ['finance_manager']); // tiada inventory.manage

        $this->jalan($this->sebagai($kewangan, $t), 'produk.ubah_harga', ['sku' => 'ROS', 'harga_baharu' => 1])->assertForbidden();

        $this->dalamTenant($t);
        $this->assertSame(0, ApprovalRequest::where('type', 'ai.tool')->count());
        $this->assertEquals(25, (float) $ros->fresh()->sell_price);
    }

    #[Test]
    public function input_tidak_sah_ditolak(): void
    {
        [$u, $t] = $this->sedia();

        $this->jalan($this->sebagai($u, $t), 'produk.ubah_harga', ['sku' => 'ROS', 'harga_baharu' => -5])->assertStatus(422);
        $this->jalan($this->sebagai($u, $t), 'tiada.alat')->assertNotFound();
    }

    #[Test]
    public function alat_lama_stub_tidak_lagi_mengaku_berjaya(): void
    {
        [$u, $t] = $this->sedia();

        $r = $this->sebagai($u, $t)->postJson('/api/v1/ai-agents/execute/tool', [
            'agent_key' => 'exec_assistant', 'tool_name' => 'email.send', 'params' => ['to' => 'x@y.my', 'subject' => 'Ujian', 'body' => 'Hai'],
        ]);

        // Alat lama (stub) tidak boleh lapor berjaya: sama ada ditolak awal atau gagal not_implemented.
        $this->assertFalse((bool) ($r->json('success') ?? $r->json('data.success')), 'email.send stub tak boleh lapor berjaya.');
        $this->assertStringNotContainsString('email_sent', $r->getContent());
    }
}
