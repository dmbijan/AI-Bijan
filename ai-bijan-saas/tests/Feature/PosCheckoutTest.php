<?php

namespace Tests\Feature;

use App\Modules\POS\Models\POSRegister;
use App\Modules\POS\Models\POSSale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\AksesTenant;
use Tests\TestCase;

class PosCheckoutTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
    }

    private function payload(int $registerId, array $tambah = []): array
    {
        return array_merge([
            'register_id' => $registerId,
            'items' => [
                ['product_name' => 'Jambangan Ros', 'quantity' => 2, 'unit_price' => 25],
                ['product_name' => 'Kad Ucapan', 'quantity' => 1, 'unit_price' => 5],
            ],
            'payments' => [['method' => 'cash', 'amount' => 60]],
        ], $tambah);
    }

    private function sedia(): array
    {
        [$u, $t] = $this->daftar('kedai@bunga.my', 'Kedai Bunga Sari');
        $this->dalamTenant($t);
        $reg = POSRegister::create(['tenant_id' => $t->id, 'name' => 'Kaunter 1', 'code' => 'K1', 'is_active' => true, 'created_by' => $u->id]);

        return [$u, $t, $reg];
    }

    #[Test]
    public function checkout_satu_panggilan_simpan_jualan_lengkap(): void
    {
        [$u, $t, $reg] = $this->sedia();

        $r = $this->sebagai($u, $t)->postJson('/api/v1/pos/sales/checkout', $this->payload($reg->id))->assertCreated();

        $this->assertSame('completed', $r->json('status'));
        $this->assertEquals(55, (float) $r->json('total_amount'));
        $this->assertEquals(5, (float) $r->json('change_amount'));
        $this->assertCount(2, $r->json('items'));
    }

    #[Test]
    public function segerak_berulang_dengan_kunci_sama_tidak_jadi_pendua(): void
    {
        [$u, $t, $reg] = $this->sedia();
        $http = $this->sebagai($u, $t)->withHeader('Idempotency-Key', 'luar-talian-abc-123');

        $a = $http->postJson('/api/v1/pos/sales/checkout', $this->payload($reg->id))->assertCreated();
        $b = $http->postJson('/api/v1/pos/sales/checkout', $this->payload($reg->id));

        $this->assertSame($a->json('id'), $b->json('id'));
        $this->assertSame(1, POSSale::withoutGlobalScopes()->where('tenant_id', $t->id)->count());
    }

    #[Test]
    public function masa_jualan_luar_talian_dikekalkan(): void
    {
        [$u, $t, $reg] = $this->sedia();
        $masa = now()->subHours(3)->startOfMinute();

        $r = $this->sebagai($u, $t)->postJson('/api/v1/pos/sales/checkout', $this->payload($reg->id, ['sold_at' => $masa->toIso8601String()]))->assertCreated();

        $this->assertTrue($masa->equalTo(Carbon::parse($r->json('completed_at'))));
    }

    #[Test]
    public function tenant_id_dalam_body_tidak_boleh_tukar_syarikat(): void
    {
        [$u, $t, $reg] = $this->sedia();
        [, $lain] = $this->daftar('lain@kedai.my', 'Kedai Lain');

        $r = $this->sebagai($u, $t)->postJson('/api/v1/pos/sales/checkout', $this->payload($reg->id, ['tenant_id' => $lain->id]))->assertCreated();

        $this->assertSame($t->id, (int) $r->json('tenant_id'));
        $this->assertSame(0, POSSale::withoutGlobalScopes()->where('tenant_id', $lain->id)->count());
    }

    #[Test]
    public function ringkasan_hari_ini_kira_jualan_hari_ini(): void
    {
        [$u, $t, $reg] = $this->sedia();
        $http = $this->sebagai($u, $t);
        $http->postJson('/api/v1/pos/sales/checkout', $this->payload($reg->id))->assertCreated();

        $hari = now()->format('Y-m-d');
        $r = $http->getJson("/api/v1/pos/sales/summary?start_date={$hari}&end_date={$hari}")->assertOk();

        $this->assertSame(1, $r->json('total_sales'));
        $this->assertEquals(55, (float) $r->json('total_revenue'));
    }

    #[Test]
    public function senarai_kaunter_boleh_dibaca(): void
    {
        [$u, $t] = $this->sedia();

        $this->sebagai($u, $t)->getJson('/api/v1/pos/registers')->assertOk()->assertJsonPath('0.name', 'Kaunter 1');
    }

    #[Test]
    public function bayaran_duitnow_dengan_rujukan_disimpan(): void
    {
        [$u, $t, $reg] = $this->sedia();

        $r = $this->sebagai($u, $t)->postJson('/api/v1/pos/sales/checkout', $this->payload($reg->id, [
            'payments' => [['method' => 'duitnow', 'amount' => 55, 'reference' => 'DN20261030123']],
        ]))->assertCreated();

        $this->assertSame('duitnow', $r->json('payments.0.payment_method'));
        $this->assertSame('DN20261030123', $r->json('payments.0.reference_number'));
    }

    #[Test]
    public function masa_jualan_masa_depan_ditolak(): void
    {
        [$u, $t, $reg] = $this->sedia();

        $this->sebagai($u, $t)->postJson('/api/v1/pos/sales/checkout', $this->payload($reg->id, ['sold_at' => now()->addDay()->toIso8601String()]))
            ->assertStatus(422);
    }
}
