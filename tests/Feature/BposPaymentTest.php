<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Modules\POS\Models\POSRegister;
use App\Modules\POS\Models\POSSale;
use App\Modules\POS\Models\POSSaleItem;
use App\Modules\POS\Models\POSSaleTender;
use App\Modules\POS\Services\SalePaymentService;
use App\Modules\POS\Services\ServiceChargeService;
use App\Modules\POS\Services\TenderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Ciri B-POS yang dipindahkan ke modul POS (BAB 88 / T-76 → T-83).
 *
 * Sumber: `b-pos-system` (MIT). Setiap ujian memetakan satu TASK supaya
 * "siap" boleh disemak satu per satu.
 */
class BposPaymentTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;
    private POSRegister $daftar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@posb.my', 'POS B Sdn Bhd');
        $this->dalamTenant($this->t);

        $this->daftar = POSRegister::create([
            'name' => 'Daftar Utama',
            'code' => 'REG-001',
            'created_by' => $this->u->id,
        ]);
    }

    private function item(string $nama = 'Nasi Lemak', float $harga = 10.00): Item
    {
        static $n = 0;
        $n++;

        return Item::create([
            'sku' => 'ITM-' . $n,
            'name' => $nama,
            'uom' => 'unit',
            'sell_price' => $harga,
            'is_active' => true,
        ]);
    }

    /** Jualan draf dengan satu baris. */
    private function jualanDraf(float $harga = 10.00, string $jenis = 'dine_in'): POSSale
    {
        $sale = POSSale::create([
            'tenant_id' => $this->t->id,
            'register_id' => $this->daftar->id,
            'user_id' => $this->u->id,
            'sale_no' => POSSale::generateSaleNo(),
            'status' => 'draft',
            'subtotal' => $harga,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $harga,
            'amount_paid' => 0,
            'change_amount' => 0,
            'order_type' => $jenis,
        ]);

        POSSaleItem::create([
            'sale_id' => $sale->id,
            'product_name' => 'Nasi Lemak',
            'quantity' => 1,
            'unit_price' => $harga,
            'line_total' => $harga,
        ]);

        return $sale->fresh();
    }

    // ===============================================================
    // T-79 — MULTI-TENDER SPLIT PAYMENT
    // ===============================================================

    public function test_tender_tunai_mengira_baki(): void
    {
        $normal = app(TenderService::class)->normalize([
            ['method' => 'cash', 'amount' => 42.50, 'cash_received' => 50],
        ], 42.50);

        $this->assertCount(1, $normal);
        $this->assertSame(50.0, $normal[0]['cash_received']);
        $this->assertSame(7.50, $normal[0]['change_amount']);
        $this->assertSame(POSSaleTender::STATUS_PAID, $normal[0]['payment_status']);
    }

    public function test_tender_berpisah_tunai_dan_kad(): void
    {
        $normal = app(TenderService::class)->normalize([
            ['method' => 'cash', 'amount' => 30, 'cash_received' => 30],
            ['method' => 'card', 'amount' => 20],
        ], 50);

        $this->assertCount(2, $normal);
        $this->assertSame(50.0, array_sum(array_column($normal, 'amount')));
    }

    public function test_jumlah_tender_mesti_sama_dengan_jualan(): void
    {
        $this->expectException(ValidationException::class);

        app(TenderService::class)->normalize([
            ['method' => 'cash', 'amount' => 40],
        ], 50);
    }

    public function test_tunai_kurang_daripada_amaun_ditolak(): void
    {
        $this->expectException(ValidationException::class);

        app(TenderService::class)->normalize([
            ['method' => 'cash', 'amount' => 50, 'cash_received' => 40],
        ], 50);
    }

    public function test_lebih_dua_tender_ditolak(): void
    {
        $this->expectException(ValidationException::class);

        app(TenderService::class)->normalize([
            ['method' => 'cash', 'amount' => 10],
            ['method' => 'card', 'amount' => 20],
            ['method' => 'ewallet', 'amount' => 20],
        ], 50);
    }

    public function test_lebih_satu_bukan_tunai_ditolak(): void
    {
        $this->expectException(ValidationException::class);

        app(TenderService::class)->normalize([
            ['method' => 'card', 'amount' => 30],
            ['method' => 'ewallet', 'amount' => 20],
        ], 50);
    }

    public function test_kaedah_tidak_sah_ditolak(): void
    {
        $this->expectException(ValidationException::class);

        app(TenderService::class)->normalize([
            ['method' => 'bitcoin', 'amount' => 50],
        ], 50);
    }

    public function test_status_gabungan_pending_bila_ada_tng(): void
    {
        $status = app(TenderService::class)->statusGabungan([
            ['payment_status' => 'paid'],
            ['payment_status' => 'pending'],
        ]);

        $this->assertSame('pending', $status);
    }

    // ===============================================================
    // T-78 — SERVICE CHARGE
    // ===============================================================

    public function test_caj_perkhidmatan_dikenakan_pada_dine_in(): void
    {
        config(['pos.service_charge.rate' => 10, 'pos.service_charge.scope' => 'dine_in']);

        $hasil = app(ServiceChargeService::class)->kira(100, 'dine_in');

        $this->assertSame(10.0, $hasil['amount']);
        $this->assertSame(10.0, $hasil['rate']);
    }

    public function test_caj_perkhidmatan_tidak_dikenakan_pada_bungkus(): void
    {
        config(['pos.service_charge.rate' => 10, 'pos.service_charge.scope' => 'dine_in']);

        $hasil = app(ServiceChargeService::class)->kira(100, 'takeaway');

        $this->assertSame(0.0, $hasil['amount']);
        $this->assertNull($hasil['rate']);
    }

    public function test_caj_perkhidmatan_skop_semua(): void
    {
        config(['pos.service_charge.rate' => 5, 'pos.service_charge.scope' => 'all']);

        $this->assertSame(5.0, app(ServiceChargeService::class)->kira(100, 'takeaway')['amount']);
    }

    public function test_caj_perkhidmatan_kadar_sifar_off(): void
    {
        config(['pos.service_charge.rate' => 0]);

        $this->assertSame(0.0, app(ServiceChargeService::class)->kira(100, 'dine_in')['amount']);
    }

    public function test_bayar_mengira_caj_perkhidmatan_dalam_jumlah(): void
    {
        config(['pos.service_charge.rate' => 10, 'pos.service_charge.scope' => 'dine_in']);

        $sale = $this->jualanDraf(100, 'dine_in');

        $sale = app(SalePaymentService::class)->bayar($sale, [
            ['method' => 'cash', 'amount' => 110],
        ]);

        $this->assertSame('110.00', $sale->total_amount);
        $this->assertSame('10.00', $sale->service_charge_amount);
        $this->assertSame('paid', $sale->payment_status);
    }
}
