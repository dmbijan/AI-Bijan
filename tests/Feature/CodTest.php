<?php

namespace Tests\Feature;

use App\Modules\Cod\Models\CodCollection;
use App\Modules\Cod\Models\CodOrder;
use App\Modules\Cod\Models\CodSetting;
use App\Modules\Cod\Models\CodStatusHistory;
use App\Modules\Cod\Services\CodService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * COD Engine (BAB 84 / Idea #6).
 *
 * Jurang Malaysia: COD ialah kaedah pembayaran dominan PKS MY, tetapi
 * tiada pesaing MY yang menyenangkakannya. Modul ini urus kelayakan,
 * status, kutipan & reconciliation.
 */
class CodTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@cod.my', 'COD Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    private function pesanan(array $ganti = []): CodOrder
    {
        return CodOrder::create(array_merge([
            'cod_no' => 'COD-' . uniqid(),
            'customer_name' => 'Ahmad Ali',
            'customer_phone' => '+60123456789',
            'delivery_address' => 'No 12, Jalan Merdeka, 50000 Kuala Lumpur',
            'postcode' => '50000',
            'state' => 'Kuala Lumpur',
            'order_amount' => 120.00,
            'shipping_amount' => 10.00,
            'total_amount' => 130.00,
            'amount_to_collect' => 130.00,
            'status' => CodOrder::STATUS_PENDING,
        ], $ganti));
    }

    private function tetapanAktif(array $ganti = []): CodSetting
    {
        $setting = CodSetting::untukTenant($this->t->id);
        $setting->update(array_merge([
            'enabled' => true,
            'min_order_amount' => 50,
            'max_order_amount' => 3000,
        ], $ganti));

        return $setting->fresh();
    }

    // -------------------------------------------------------------------
    // Tetapan
    // -------------------------------------------------------------------

    public function test_tetapan_cod_dicipta_untuk_tenant(): void
    {
        $setting = CodSetting::untukTenant($this->t->id);

        $this->assertNotNull($setting);
        $this->assertFalse((bool) $setting->enabled);
        $this->assertSame($this->t->id, $setting->tenant_id);
    }

    public function test_tetapan_cod_unik_per_tenant(): void
    {
        $a = CodSetting::untukTenant($this->t->id);
        $b = CodSetting::untukTenant($this->t->id);

        $this->assertSame($a->id, $b->id);
    }

    // -------------------------------------------------------------------
    // Kelayakan
    // -------------------------------------------------------------------

    public function test_kelayakan_gagal_bila_cod_tidak_diaktifkan(): void
    {
        $s = app(CodService::class);
        $setting = CodSetting::untukTenant($this->t->id);

        $hasil = $s->semakKelayakan($setting, 200.00);

        $this->assertFalse($hasil['allowed']);
        $this->assertNotEmpty($hasil['reasons']);
    }

    public function test_kelayakan_lulus_aktif_dalam_had(): void
    {
        $s = app(CodService::class);
        $setting = $this->tetapanAktif();

        $hasil = $s->semakKelayakan($setting, 200.00);

        $this->assertTrue($hasil['allowed']);
        $this->assertEmpty($hasil['reasons']);
    }

    public function test_kelayakan_gagal_below_minimum(): void
    {
        $s = app(CodService::class);
        $setting = $this->tetapanAktif(['min_order_amount' => 100]);

        $hasil = $s->semakKelayakan($setting, 50.00);

        $this->assertFalse($hasil['allowed']);
    }

    public function test_kelayakan_gagal_above_maximum(): void
    {
        $s = app(CodService::class);
        $setting = $this->tetapanAktif(['max_order_amount' => 500]);

        $hasil = $s->semakKelayakan($setting, 900.00);

        $this->assertFalse($hasil['allowed']);
    }

    public function test_kelayakan_gagal_negeri_dikecualikan(): void
    {
        $s = app(CodService::class);
        $setting = $this->tetapanAktif(['excluded_regions' => ['Sabah']]);

        $hasil = $s->semakKelayakan($setting, 200.00, 'Sabah');

        $this->assertFalse($hasil['allowed']);
    }

    public function test_kelayakan_gagal_kurier_tak_dibenarkan(): void
    {
        $s = app(CodService::class);
        $setting = $this->tetapanAktif(['allowed_couriers' => ['jnt', 'poslaju']]);

        $hasil = $s->semakKelayakan($setting, 200.00, null, 'ninja');

        $this->assertFalse($hasil['allowed']);
    }

    public function test_kelayakan_kembalikan_caj_tambahan(): void
    {
        $s = app(CodService::class);
        $setting = $this->tetapanAktif(['surcharge' => 5.50]);

        $hasil = $s->semakKelayakan($setting, 200.00);

        $this->assertSame(5.50, $hasil['surcharge']);
    }

    // -------------------------------------------------------------------
    // Alamat
    // -------------------------------------------------------------------

    public function test_alamat_gagal_bila_terlalu_pendek(): void
    {
        $s = app(CodService::class);
        $hasil = $s->semakAlamat('Pendek');

        $this->assertFalse($hasil['verified']);
        $this->assertNotNull($hasil['issue']);
    }

    public function test_alamat_gagal_bila_tiada_nombor_jalan(): void
    {
        $s = app(CodService::class);
        $hasil = $s->semakAlamat('Jalan Merdeka tanpa nombor');

        $this->assertFalse($hasil['verified']);
    }

    public function test_alamat_gagal_bila_poskod_bukan_5_digit(): void
    {
        $s = app(CodService::class);
        $hasil = $s->semakAlamat('No 12, Jalan Merdeka, KL', '123');

        $this->assertFalse($hasil['verified']);
    }

    public function test_alamat_gagal_bila_telefon_bukan_malaysia(): void
    {
        $s = app(CodService::class);
        $hasil = $s->semakAlamat('No 12, Jalan Merdeka, KL', '50000', 'KL', '+12025550123');

        $this->assertFalse($hasil['verified']);
    }

    public function test_alamat_lulus(): void
    {
        $s = app(CodService::class);
        $hasil = $s->semakAlamat('No 12, Jalan Merdeka, 50000 KL', '50000', 'Kuala Lumpur', '+60123456789');

        $this->assertTrue($hasil['verified']);
        $this->assertTrue($hasil['serviceable']);
        $this->assertNull($hasil['issue']);
    }

    // -------------------------------------------------------------------
    // Nombor COD
    // -------------------------------------------------------------------

    public function test_kod_no_dijana_unik(): void
    {
        $s = app(CodService::class);
        $a = $s->janaCodNo($this->t->id);

        $this->pesanan(['cod_no' => $a]);
        $b = $s->janaCodNo($this->t->id);

        $this->assertNotSame($a, $b);
        $this->assertStringStartsWith('COD-', $a);
    }

    // -------------------------------------------------------------------
    // Status
    // -------------------------------------------------------------------

    public function test_pesanan_boleh_dihantar_hanya_dari_pending(): void
    {
        $p = $this->pesanan([
            'status' => CodOrder::STATUS_CONFIRMED,
            'address_verified' => true,
            'serviceable' => true,
        ]);

        $this->assertTrue($p->bolehDihantar());
    }

    public function test_pesanan_telah_dispatch_tidak_boleh_dihantar(): void
    {
        $p = $this->pesanan(['status' => CodOrder::STATUS_DISPATCHED]);

        $this->assertFalse($p->bolehDihantar());
    }

    public function test_tukar_status_merekod_sejarah(): void
    {
        $s = app(CodService::class);
        $p = $this->pesanan();

        $s->tukarStatus($p, CodOrder::STATUS_CONFIRMED);

        $this->assertSame(CodOrder::STATUS_CONFIRMED, $p->fresh()->status);

        $sejarah = CodStatusHistory::where('cod_order_id', $p->id)->count();
        $this->assertSame(1, $sejarah);
    }

    public function test_tukar_status_transiti_tidak_sah_tidak_diubah(): void
    {
        $s = app(CodService::class);
        $p = $this->pesanan(['status' => CodOrder::STATUS_DELIVERED]);

        $s->tukarStatus($p, CodOrder::STATUS_PENDING);

        $this->assertSame(CodOrder::STATUS_DELIVERED, $p->fresh()->status);
        $this->assertSame(0, CodStatusHistory::where('cod_order_id', $p->id)->count());
    }

    public function test_tukar_status_sama_tiada_sejarah_baru(): void
    {
        $s = app(CodService::class);
        $p = $this->pesanan(['status' => CodOrder::STATUS_CONFIRMED]);

        $s->tukarStatus($p, CodOrder::STATUS_CONFIRMED);

        $this->assertSame(0, CodStatusHistory::where('cod_order_id', $p->id)->count());
    }

    // -------------------------------------------------------------------
    // Kutipan
    // -------------------------------------------------------------------

    public function test_rekod_kutipan_mengemas_kini_kutipan(): void
    {
        $s = app(CodService::class);
        $p = $this->pesanan(['status' => CodOrder::STATUS_DELIVERED]);

        $s->rekodKutipan($p, 130.00);

        $this->assertSame(130.00, (float) $p->fresh()->amount_collected);
    }

    public function test_rekod_kutipan_penuh_tukar_status_collected(): void
    {
        $s = app(CodService::class);
        $p = $this->pesanan(['status' => CodOrder::STATUS_DELIVERED]);

        $s->rekodKutipan($p, 130.00);

        $this->assertSame(CodOrder::STATUS_COLLECTED, $p->fresh()->status);
    }

    public function test_rekod_kutipan_separuh_tidak_tukar_status(): void
    {
        $s = app(CodService::class);
        $p = $this->pesanan(['status' => CodOrder::STATUS_DELIVERED]);

        $s->rekodKutipan($p, 50.00);

        $this->assertSame(CodOrder::STATUS_DELIVERED, $p->fresh()->status);
        $this->assertSame(50.00, (float) $p->fresh()->amount_collected);
    }

    public function test_kutipan_harian_dikumpul(): void
    {
        CodCollection::create([
            'tenant_id' => $this->t->id,
            'collection_date' => now()->toDateString(),
            'courier' => 'jnt',
            'total_collected' => 1300.00,
            'total_expected' => 1500.00,
            'orders_collected' => 10,
            'orders_failed' => 2,
        ]);

        $kutipan = CodCollection::where('tenant_id', $this->t->id)->first();

        $this->assertNotNull($kutipan);
        $this->assertSame(1300.0, (float) $kutipan->total_collected);
        $this->assertSame(200.0, (float) $kutipan->total_expected - (float) $kutipan->total_collected);
    }

    // -------------------------------------------------------------------
    // Analisis & reconciliation
    // -------------------------------------------------------------------

    public function test_analisis_kegagalan_kumpulan_punca(): void
    {
        $s = app(CodService::class);

        $this->pesanan(['status' => CodOrder::STATUS_FAILED, 'failure_reason' => 'Alamat salah', 'courier' => 'jnt']);
        $this->pesanan(['status' => CodOrder::STATUS_FAILED, 'failure_reason' => 'Alamat salah', 'courier' => 'jnt']);
        $this->pesanan(['status' => CodOrder::STATUS_FAILED, 'failure_reason' => 'Tidak Bench', 'courier' => 'poslaju']);
        $this->pesanan(['status' => CodOrder::STATUS_COLLECTED]);

        $analisis = $s->analisisKegagalan($this->t->id);

        $this->assertSame(3, $analisis['failed']);
        $this->assertSame(4, $analisis['total']);
        $this->assertGreaterThan(0, $analisis['failure_rate']);
        $this->assertSame(2, $analisis['by_reason']['Alamat salah']);
        $this->assertSame(2, $analisis['by_courier']['jnt']);
    }

    public function test_reconcilesi_kira_jurang(): void
    {
        $s = app(CodService::class);

        $this->pesanan([
            'status' => CodOrder::STATUS_COLLECTED,
            'amount_to_collect' => 100.00,
            'amount_collected' => 100.00,
        ]);

        $hasil = $s->reconcilesi($this->t->id, now()->toDateString());

        $this->assertSame(1, $hasil['jumlah_orders']);
        $this->assertSame(100.00, $hasil['dijangka']);
        $this->assertSame(0.0, $hasil['jurang']);
        $this->assertSame(100.0, $hasil['kadar_kutipan']);
    }

    public function test_reconcilesi_baki_bila_tidak_dikutip(): void
    {
        $s = app(CodService::class);

        $this->pesanan([
            'status' => CodOrder::STATUS_DELIVERED,
            'amount_to_collect' => 100.00,
            'amount_collected' => 0,
        ]);

        $hasil = $s->reconcilesi($this->t->id, now()->toDateString());

        $this->assertSame(100.00, $hasil['jurang']);
        $this->assertSame(0.0, $hasil['kadar_kutipan']);
    }

    // -------------------------------------------------------------------
    // Tenant isolation
    // -------------------------------------------------------------------

    public function test_pesanan_tenant_lain_tidak_nampak(): void
    {
        $p = $this->pesanan();

        [$u2, $t2] = $this->daftar('lawan@cod.my', 'Lawan Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertNull(CodOrder::find($p->id));
    }

    // -------------------------------------------------------------------
    // API
    // -------------------------------------------------------------------

    public function test_api_senarai_pesanan_cod(): void
    {
        $this->pesanan();

        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/cod/orders')
            ->assertOk();

        $this->assertGreaterThanOrEqual(1, count($res->json('data')));
    }

    public function test_api_semak_kelayakan(): void
    {
        $this->tetapanAktif();

        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/cod/check', ['order_amount' => 250.00])
            ->assertOk();

        $this->assertTrue($res->json('data.allowed'));
    }

    public function test_api_semak_alamat(): void
    {
        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/cod/check-address', [
                'address' => 'No 12, Jalan Merdeka, 50000 KL',
                'postcode' => '50000',
                'state' => 'Kuala Lumpur',
                'phone' => '+60123456789',
            ])
            ->assertOk();

        $this->assertTrue($res->json('data.verified'));
    }

    public function test_api_cipta_pesanan_cod(): void
    {
        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/cod/orders', [
                'customer_name' => 'Siti Aminah',
                'customer_phone' => '+60123456789',
                'delivery_address' => 'No 5, Jalan Utama, 40000 Shah Alam',
                'postcode' => '40000',
                'state' => 'Selangor',
                'order_amount' => 150.00,
                'shipping_amount' => 8.00,
            ])
            ->assertCreated();

        $this->assertNotEmpty($res->json('data.cod_no'));
    }

    public function test_api_tukar_status_mencatat_sejarah(): void
    {
        $p = $this->pesanan();

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/cod/orders/{$p->id}/status", [
                'status' => CodOrder::STATUS_CONFIRMED,
            ])
            ->assertOk();

        $this->assertSame(CodOrder::STATUS_CONFIRMED, $p->fresh()->status);
    }

    public function test_api_rekod_kutipan_harian(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/cod/collections', [
                'collection_date' => now()->toDateString(),
                'courier' => 'jnt',
                'total_collected' => 1300.00,
                'total_expected' => 1500.00,
                'orders_collected' => 10,
                'orders_failed' => 2,
            ])
            ->assertCreated();

        $this->dalamTenant($this->t);
        $this->assertSame(1, CodCollection::where('tenant_id', $this->t->id)->count());
    }

    public function test_api_tetapan_kemas_kini(): void
    {
        $this->sebagai($this->u, $this->t)
            ->putJson('/api/v1/cod/settings', [
                'enabled' => true,
                'min_order_amount' => 80,
            ])
            ->assertOk();

        $this->dalamTenant($this->t);
        $setting = CodSetting::untukTenant($this->t->id);
        $this->assertTrue((bool) $setting->enabled);
        $this->assertSame(80.0, (float) $setting->min_order_amount);
    }

    public function test_api_analisis_kegagalan(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/cod/analysis')
            ->assertOk();
    }

    public function test_api_reconciliation(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/cod/reconcile')
            ->assertOk();
    }
}
