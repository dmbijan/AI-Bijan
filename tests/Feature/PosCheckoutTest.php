<?php

namespace Tests\Feature;

use App\Modules\POS\Models\POSSale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/** Checkout satu jalan (kasa + segerak luar talian), DuitNow, dan pengasingan tenant pada laluan POS. */
class PosCheckoutTest extends TestCase
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
        $reg = $this->sebagai($u, $t)->postJson('/api/v1/pos/registers', ['name' => 'Kaunter 1', 'code' => 'K1'])->assertCreated();

        return [$u, $t, $reg->json('id') ?? $reg->json('data.id')];
    }

    private function payload(int $registerId, array $tambah = []): array
    {
        return array_merge([
            'register_id' => $registerId,
            'items' => [
                ['product_name' => 'Jambangan Ros', 'quantity' => 2, 'unit_price' => 25],
                ['product_name' => 'Kad Ucapan', 'quantity' => 1, 'unit_price' => 5],
            ],
            'tenders' => [['method' => 'cash', 'amount' => 55, 'cash_received' => 60]],
        ], $tambah);
    }

    #[Test]
    public function checkout_satu_panggilan_simpan_jualan_lengkap(): void
    {
        [$u, $t, $reg] = $this->sedia();

        $r = $this->sebagai($u, $t)->postJson('/api/v1/pos/sales/checkout', $this->payload($reg))->assertCreated();

        $this->assertSame('completed', $r->json('data.status'));
        $this->assertEquals(55, (float) $r->json('data.total_amount'));
        $this->assertCount(2, $r->json('data.items'));
    }

    #[Test]
    public function segerak_berulang_dengan_uuid_sama_tidak_jadi_pendua(): void
    {
        [$u, $t, $reg] = $this->sedia();
        $http = $this->sebagai($u, $t);
        $uuid = '7f1c2b8e-1d2a-4c3b-9e8f-0a1b2c3d4e5f';

        $a = $http->postJson('/api/v1/pos/sales/checkout', $this->payload($reg, ['client_uuid' => $uuid]))->assertCreated();
        $b = $http->postJson('/api/v1/pos/sales/checkout', $this->payload($reg, ['client_uuid' => $uuid]))->assertOk();

        $this->assertSame($a->json('data.id'), $b->json('data.id'));
        $this->assertSame(1, POSSale::withoutGlobalScopes()->where('tenant_id', $t->id)->count());
    }

    #[Test]
    public function masa_jualan_luar_talian_dikekalkan(): void
    {
        [$u, $t, $reg] = $this->sedia();
        $masa = now()->subHours(3)->startOfMinute();

        $r = $this->sebagai($u, $t)->postJson('/api/v1/pos/sales/checkout', $this->payload($reg, ['sold_at' => $masa->toIso8601String()]))->assertCreated();

        $this->assertTrue($masa->equalTo(\Illuminate\Support\Carbon::parse($r->json('data.completed_at'))));
    }

    #[Test]
    public function masa_jualan_masa_depan_ditolak(): void
    {
        [$u, $t, $reg] = $this->sedia();

        $this->sebagai($u, $t)->postJson('/api/v1/pos/sales/checkout', $this->payload($reg, ['sold_at' => now()->addDay()->toIso8601String()]))->assertStatus(422);
    }

    #[Test]
    public function bayaran_duitnow_dengan_rujukan_disimpan(): void
    {
        [$u, $t, $reg] = $this->sedia();

        $r = $this->sebagai($u, $t)->postJson('/api/v1/pos/sales/checkout', $this->payload($reg, [
            'tenders' => [['method' => 'duitnow', 'amount' => 55, 'reference' => 'DN20261031001']],
        ]))->assertCreated();

        $this->assertSame('duitnow', $r->json('data.tenders.0.method'));
        $this->assertSame('DN20261031001', $r->json('data.tenders.0.reference'));
    }

    #[Test]
    public function tenant_id_dalam_body_tidak_boleh_tukar_syarikat(): void
    {
        [$u, $t, $reg] = $this->sedia();
        [, $lain] = $this->daftar('lain@kedai.my', 'Kedai Lain');

        $r = $this->sebagai($u, $t)->postJson('/api/v1/pos/sales/checkout', $this->payload($reg, ['tenant_id' => $lain->id]))->assertCreated();

        $this->assertSame($t->id, (int) $r->json('data.tenant_id'));
        $this->assertSame(0, POSSale::withoutGlobalScopes()->where('tenant_id', $lain->id)->count());
    }

    #[Test]
    public function ringkasan_hari_ini_kira_jualan_hari_ini(): void
    {
        [$u, $t, $reg] = $this->sedia();
        $http = $this->sebagai($u, $t);
        $http->postJson('/api/v1/pos/sales/checkout', $this->payload($reg))->assertCreated();

        $hari = now()->format('Y-m-d');
        $r = $http->getJson("/api/v1/pos/sales/summary?start_date={$hari}&end_date={$hari}")->assertOk();

        $this->assertSame(1, $r->json('total_sales'));
        $this->assertEquals(55, (float) $r->json('total_revenue'));
    }

    #[Test]
    public function laluan_modul_pos_perlu_log_masuk_dan_berfungsi_untuk_pemilik(): void
    {
        [$u, $t] = $this->sedia();

        $this->sebagai($u, $t)->getJson('/api/v1/pos/dine/areas')->assertOk();
        $this->sebagai($u, $t)->getJson('/api/v1/pos/kitchen')->assertOk();

        $this->flushHeaders();
        $this->flushSession();
        auth()->forgetGuards();
        $this->setUpAkses();
        $this->getJson('/api/v1/pos/dine/areas')->assertUnauthorized();
    }
}
