<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Modules\POS\Models\CashShift;
use App\Modules\POS\Models\POSDineArea;
use App\Modules\POS\Models\POSDineOrder;
use App\Modules\POS\Models\POSDineTable;
use App\Modules\POS\Models\POSModifierGroup;
use App\Modules\POS\Models\POSModifierOption;
use App\Modules\POS\Models\POSRegister;
use App\Modules\POS\Models\POSSale;
use App\Modules\POS\Models\POSSaleItem;
use App\Modules\POS\Models\POSSaleTender;
use App\Modules\POS\Services\CashShiftService;
use App\Modules\POS\Services\DineOrderService;
use App\Modules\POS\Services\DiscountApprovalService;
use App\Modules\POS\Services\KitchenService;
use App\Modules\POS\Services\ModifierService;
use App\Modules\POS\Services\SalePaymentService;
use App\Modules\POS\Services\TngSoundboxService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Ciri B-POS dipindahkan — bahagian operasi (T-76, T-77, T-80, T-82, T-83).
 */
class BposOperationTest extends TestCase
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
        [$this->u, $this->t] = $this->daftar('pemilik@poso.my', 'POS Operasi Sdn Bhd');
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
            'sku' => 'OP-' . $n,
            'name' => $nama,
            'uom' => 'unit',
            'sell_price' => $harga,
            'is_active' => true,
        ]);
    }

    private function meja(): POSDineTable
    {
        static $n = 0;
        $n++;

        $area = POSDineArea::create(['name' => 'Dewan ' . $n, 'is_active' => true]);

        return POSDineTable::create([
            'dine_area_id' => $area->id,
            'name' => 'Meja ' . $n,
            'seats' => 4,
        ]);
    }

    private function syifTerbuka(float $float = 200): CashShift
    {
        return CashShift::create([
            'register_id' => $this->daftar->id,
            'user_id' => $this->u->id,
            'opened_by' => $this->u->id,
            'status' => 'open',
            'opening_cash' => $float,
            'shift_date' => now()->toDateString(),
            'opened_at' => now(),
        ]);
    }

    // ===============================================================
    // T-77 — MODIFIER / ADD-ON
    // ===============================================================

    private function kumpulanModifier(int $min = 1, int $max = 1, float $harga = 2.00): POSModifierGroup
    {
        $g = POSModifierGroup::create([
            'name' => 'Saiz',
            'min_select' => $min,
            'max_select' => $max,
            'is_active' => true,
        ]);

        POSModifierOption::create([
            'modifier_group_id' => $g->id,
            'name' => 'Besar',
            'price' => $harga,
            'is_active' => true,
        ]);

        return $g;
    }

    private function lampir(POSModifierGroup $g, Item $item, int $susun = 0): void
    {
        \Illuminate\Support\Facades\DB::table('pos_product_modifiers')->insert([
            'tenant_id' => $this->t->id,
            'item_id' => $item->id,
            'modifier_group_id' => $g->id,
            'sort_order' => $susun,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_modifier_selection_deduplicates_ids_and_preserves_snapshot_contract(): void
    {
        $item = $this->item();
        $group = $this->kumpulanModifier(min: 2, max: 2, harga: 2.50);
        $this->lampir($group, $item);
        $first = $group->options()->first();
        $second = POSModifierOption::create([
            'modifier_group_id' => $group->id,
            'name' => 'Extra',
            'price' => 1.25,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $svc = app(ModifierService::class);
        $result = $svc->resolve($item, [(string) $second->id, 0, $first->id, $second->id]);
        $this->assertSame($svc->resolve($item, [$first->id, $second->id]), $result);
        $this->assertSame(3.75, $result['total']);
        $this->assertSame(sha1($first->id . ',' . $second->id), $result['key']);
        $this->assertSame([
            ['option_id' => $second->id, 'group' => 'Saiz', 'name' => 'Extra', 'price' => 1.25],
            ['option_id' => $first->id, 'group' => 'Saiz', 'name' => 'Besar', 'price' => 2.50],
        ], $result['modifiers']);
    }

    public function test_modifier_diambil_dengan_harga_snapshot(): void
    {
        $item = $this->item();
        $g = $this->kumpulanModifier(min: 0, max: 2, harga: 2.50);
        $this->lampir($g, $item);

        $pilihan = $g->options()->first();

        $hasil = app(ModifierService::class)->resolve($item, [$pilihan->id]);

        $this->assertCount(1, $hasil['modifiers']);
        $this->assertSame('Besar', $hasil['modifiers'][0]['name']);
        $this->assertSame(2.50, $hasil['modifiers'][0]['price']);
        $this->assertSame(2.50, $hasil['total']);
        $this->assertNotNull($hasil['key']);
    }

    public function test_modifier_wajib_tidak_dipilih_ditolak(): void
    {
        $item = $this->item();
        $g = $this->kumpulanModifier(min: 1, max: 1);
        $this->lampir($g, $item);

        $this->expectException(ValidationException::class);
        app(ModifierService::class)->resolve($item, []);
    }

    public function test_modifier_melebihi_max_ditolak(): void
    {
        $item = $this->item();
        $g = $this->kumpulanModifier(min: 0, max: 1);
        $this->lampir($g, $item);

        $kedua = POSModifierOption::create([
            'modifier_group_id' => $g->id,
            'name' => 'Sederhana',
            'price' => 0,
            'is_active' => true,
        ]);

        $this->expectException(ValidationException::class);
        app(ModifierService::class)->resolve($item, [
            $g->options()->first()->id,
            $kedua->id,
        ]);
    }

    public function test_modifier_bukan_milik_item_ditolak(): void
    {
        $item = $this->item();
        $lain = $this->item('Teh Tarik');
        $g = $this->kumpulanModifier();
        $this->lampir($g, $lain);

        $this->expectException(ValidationException::class);
        app(ModifierService::class)->resolve($item, [$g->options()->first()->id]);
    }

    // ===============================================================
    // T-76 — DINE-IN
    // ===============================================================

    public function test_pesanan_dine_in_dicipta_dan_meja_diduduki(): void
    {
        $meja = $this->meja();
        $item = $this->item('Mee Goreng', 8.00);

        $order = app(DineOrderService::class)->cipta($this->t->id, $meja, [
            'items' => [['item_id' => $item->id, 'quantity' => 2]],
        ]);

        $this->assertSame(POSDineOrder::STATUS_SUBMITTED, $order->status);
        $this->assertSame('16.00', $order->subtotal);
        $this->assertSame(2, (int) $order->item_count);
        $this->assertSame('occupied', $meja->fresh()->status);
    }

    public function test_terima_pesanan_mencipta_jualan_dengan_caj_perkhidmatan(): void
    {
        config(['pos.service_charge.rate' => 10, 'pos.service_charge.scope' => 'dine_in']);

        $meja = $this->meja();
        $item = $this->item('Mee Goreng', 50.00);

        $order = app(DineOrderService::class)->cipta($this->t->id, $meja, [
            'items' => [['item_id' => $item->id, 'quantity' => 1]],
        ]);

        $sale = app(DineOrderService::class)->terima($order, $this->t->id, $this->u->id);

        $this->assertSame('draft', $sale->status);
        $this->assertSame('dine_in', $sale->order_type);
        $this->assertSame('50.00', $sale->subtotal);
        $this->assertSame('5.00', $sale->service_charge_amount);
        $this->assertSame('55.00', $sale->total_amount);
        $this->assertSame(1, $sale->items()->count());

        $this->assertSame(POSDineOrder::STATUS_ACCEPTED, $order->fresh()->status);
        $this->assertSame($sale->id, $order->fresh()->sale_id);
    }

    public function test_terima_dua_kali_ditolak(): void
    {
        $meja = $this->meja();
        $item = $this->item('Teh', 3.00);

        $order = app(DineOrderService::class)->cipta($this->t->id, $meja, [
            'items' => [['item_id' => $item->id, 'quantity' => 1]],
        ]);

        app(DineOrderService::class)->terima($order, $this->t->id, $this->u->id);

        $this->expectException(ValidationException::class);
        app(DineOrderService::class)->terima($order->fresh(), $this->t->id, $this->u->id);
    }

    public function test_tolak_pesanan_melepaskan_meja(): void
    {
        $meja = $this->meja();
        $item = $this->item();

        $order = app(DineOrderService::class)->cipta($this->t->id, $meja, [
            'items' => [['item_id' => $item->id, 'quantity' => 1]],
        ]);

        app(DineOrderService::class)->tolak($order, 'Dapur tutup');

        $this->assertSame(POSDineOrder::STATUS_REJECTED, $order->fresh()->status);
        $this->assertSame('free', $meja->fresh()->status);
    }

    // ===============================================================
    // T-76 — KITCHEN DISPLAY
    // ===============================================================

    public function test_kitchen_diaktifkan_menandakan_tiket_baharu(): void
    {
        config(['pos.kitchen_display.enabled' => true]);

        $meja = $this->meja();
        $item = $this->item();

        $order = app(DineOrderService::class)->cipta($this->t->id, $meja, [
            'items' => [['item_id' => $item->id, 'quantity' => 1]],
        ]);

        $sale = app(DineOrderService::class)->terima($order, $this->t->id, $this->u->id);

        $this->assertSame(KitchenService::STATUS_NEW, $sale->fresh()->kitchen_status);
        $this->assertNotNull($sale->fresh()->kitchen_queued_at);
    }

    public function test_kitchen_matikan_tidak_menandakan_tiket(): void
    {
        config(['pos.kitchen_display.enabled' => false]);

        $meja = $this->meja();
        $item = $this->item();

        $order = app(DineOrderService::class)->cipta($this->t->id, $meja, [
            'items' => [['item_id' => $item->id, 'quantity' => 1]],
        ]);

        $sale = app(DineOrderService::class)->terima($order, $this->t->id, $this->u->id);

        $this->assertNull($sale->fresh()->kitchen_status);
    }

    public function test_kitchen_aliran_dan_mundur_mengosongkan_cap_masa(): void
    {
        config(['pos.kitchen_display.enabled' => true]);

        $meja = $this->meja();
        $item = $this->item();
        $order = app(DineOrderService::class)->cipta($this->t->id, $meja, [
            'items' => [['item_id' => $item->id, 'quantity' => 1]],
        ]);
        $sale = app(DineOrderService::class)->terima($order, $this->t->id, $this->u->id);

        $k = app(KitchenService::class);

        $k->moveTo($sale, KitchenService::STATUS_PREPARING);
        $this->assertNotNull($sale->fresh()->kitchen_started_at);

        $k->moveTo($sale, KitchenService::STATUS_READY);
        $this->assertNotNull($sale->fresh()->kitchen_ready_at);

        // Mundur → cap masa lewat dikosongkan
        $k->moveTo($sale, KitchenService::STATUS_PREPARING);
        $this->assertNull($sale->fresh()->kitchen_ready_at);

        $this->assertSame(KitchenService::STATUS_READY, $k->next(KitchenService::STATUS_PREPARING));
        $this->assertNull($k->next(KitchenService::STATUS_SERVED));
    }

    public function test_kitchen_tiket_tanpa_tanda_ditolak(): void
    {
        $sale = POSSale::create([
            'tenant_id' => $this->t->id,
            'register_id' => $this->daftar->id,
            'user_id' => $this->u->id,
            'sale_no' => 'SALE-TEST-000001',
            'status' => 'completed',
            'subtotal' => 10,
            'total_amount' => 10,
            'amount_paid' => 10,
        ]);

        $this->expectException(ValidationException::class);
        app(KitchenService::class)->moveTo($sale, KitchenService::STATUS_PREPARING);
    }

    // ===============================================================
    // T-80 — PERGERAKAN TUNAI + RINGKASAN SYIF
    // ===============================================================

    public function test_pergerakan_tunai_mengubah_expected_cash(): void
    {
        $syif = $this->syifTerbuka(200);
        $svc = app(CashShiftService::class);

        $this->assertSame(200.0, $svc->calculateExpectedCash($syif->fresh('cashMovements')));

        $svc->recordCashMovement($syif->id, $this->t->id, 'drop', 50, 'Setor bank', $this->u->id);

        $this->assertSame(150.0, $svc->calculateExpectedCash($syif->fresh('cashMovements')));
    }

    public function test_pickup_menambah_tunai_laci(): void
    {
        $syif = $this->syifTerbuka(100);
        $svc = app(CashShiftService::class);

        $svc->recordCashMovement($syif->id, $this->t->id, 'pickup', 20, 'Baki pelanggan', $this->u->id);

        $this->assertSame(120.0, $svc->calculateExpectedCash($syif->fresh('cashMovements')));
    }

    public function test_pergerakan_pada_syif_tertutup_ditolak(): void
    {
        $syif = $this->syifTerbuka(100);
        $syif->update(['status' => 'closed']);

        $this->expectException(ValidationException::class);
        app(CashShiftService::class)->recordCashMovement($syif->id, $this->t->id, 'drop', 10);
    }

    public function test_jenis_pergerakan_tidak_sah_ditolak(): void
    {
        $syif = $this->syifTerbuka(100);

        $this->expectException(ValidationException::class);
        app(CashShiftService::class)->recordCashMovement($syif->id, $this->t->id, 'curi', 10);
    }

    public function test_expected_cash_mengira_tender_tunai_dan_baki(): void
    {
        $syif = $this->syifTerbuka(100);

        $sale = POSSale::create([
            'tenant_id' => $this->t->id,
            'register_id' => $this->daftar->id,
            'cash_shift_id' => $syif->id,
            'user_id' => $this->u->id,
            'sale_no' => POSSale::generateSaleNo(),
            'status' => 'draft',
            'subtotal' => 42.50,
            'total_amount' => 42.50,
            'amount_paid' => 0,
            'change_amount' => 0,
            'order_type' => 'takeaway',
        ]);

        POSSaleItem::create([
            'sale_id' => $sale->id,
            'product_name' => 'Item',
            'quantity' => 1,
            'unit_price' => 42.50,
            'line_total' => 42.50,
        ]);

        app(SalePaymentService::class)->bayar($sale, [
            ['method' => 'cash', 'amount' => 42.50, 'cash_received' => 50],
        ]);

        // Laci: float 100 + diterima 50 − baki 7.50 = 142.50
        $this->assertSame(
            142.50,
            app(CashShiftService::class)->calculateExpectedCash($syif->fresh('cashMovements'))
        );
    }

    public function test_tutup_syif_menyimpan_ringkasan(): void
    {
        $syif = $this->syifTerbuka(100);
        $svc = app(CashShiftService::class);

        $svc->recordCashMovement($syif->id, $this->t->id, 'expense', 10, 'Beli ais', $this->u->id);

        $expected = $svc->calculateExpectedCash($syif->fresh('cashMovements'));

        $ditutup = $svc->closeShiftDenganRingkasan($syif->id, $this->t->id, $expected, $this->u->id, 'Tutup biasa');

        $this->assertSame('closed', $ditutup->status);
        $this->assertSame('0.00', $ditutup->cash_discrepancy);
        $this->assertSame('Tutup biasa', $ditutup->close_notes);
        $this->assertNotNull($ditutup->actual_cash);
    }

    // ===============================================================
    // T-82 — TNG SOUNDBOX
    // ===============================================================

    private function jualanTngPending(float $jumlah): POSSale
    {
        $sale = POSSale::create([
            'tenant_id' => $this->t->id,
            'register_id' => $this->daftar->id,
            'user_id' => $this->u->id,
            'sale_no' => POSSale::generateSaleNo(),
            'status' => 'draft',
            'subtotal' => $jumlah,
            'total_amount' => $jumlah,
            'amount_paid' => 0,
            'change_amount' => 0,
            'order_type' => 'takeaway',
        ]);

        POSSaleItem::create([
            'sale_id' => $sale->id,
            'product_name' => 'Item',
            'quantity' => 1,
            'unit_price' => $jumlah,
            'line_total' => $jumlah,
        ]);

        return app(SalePaymentService::class)->bayar($sale, [
            ['method' => 'tng', 'amount' => $jumlah],
        ]);
    }

    public function test_notifikasi_tng_melunaskan_jualan_sepadan(): void
    {
        config(['pos.tng_soundbox.enabled' => true]);

        $sale = $this->jualanTngPending(25.00);
        $this->assertSame('pending', $sale->payment_status);

        $hasil = app(TngSoundboxService::class)->rekod($this->t->id, 25.00, 'TNG-REF-1', 'Bayaran diterima');

        $this->assertTrue($hasil['matched']);
        $this->assertSame($sale->id, $hasil['sale_id']);
        $this->assertSame('paid', $sale->fresh()->payment_status);
    }

    public function test_notifikasi_tng_tidak_sepadan_jadi_unclaimed(): void
    {
        config(['pos.tng_soundbox.enabled' => true]);

        $hasil = app(TngSoundboxService::class)->rekod($this->t->id, 99.00, 'TNG-REF-2', 'Tiada padanan');

        $this->assertFalse($hasil['matched']);
        $this->assertDatabaseHas('pos_tng_notifications', [
            'tenant_id' => $this->t->id,
            'status' => 'unclaimed',
        ]);
    }

    public function test_notifikasi_tng_berulang_tidak_direkod_dua_kali(): void
    {
        config(['pos.tng_soundbox.enabled' => true]);

        $sale = $this->jualanTngPending(30.00);
        $svc = app(TngSoundboxService::class);

        $pertama = $svc->rekod($this->t->id, 30.00, 'TNG-DUP', 'Notifikasi');
        $kedua = $svc->rekod($this->t->id, 30.00, 'TNG-DUP', 'Notifikasi ulang');

        $this->assertTrue($pertama['matched']);
        $this->assertTrue($kedua['duplicate']);
        $this->assertSame(1, \App\Modules\POS\Models\POSTngNotification::count());
        $this->assertSame('paid', $sale->fresh()->payment_status);
    }

    public function test_padanan_manual_tng(): void
    {
        config(['pos.tng_soundbox.enabled' => true]);

        $sale = $this->jualanTngPending(15.00);
        $svc = app(TngSoundboxService::class);

        $lain = $svc->rekod($this->t->id, 77.00, 'TNG-X', 'Tiada padanan');
        $this->assertFalse($lain['matched']);

        $terkumpul = \App\Modules\POS\Models\POSTngNotification::findOrFail($lain['notification_id']);

        $svc->matchManual($terkumpul, $sale, $this->u->id, $this->t->id);

        $this->assertSame('matched', $terkumpul->fresh()->status);
        $this->assertSame($this->u->id, $terkumpul->fresh()->matched_by);
    }

    // ===============================================================
    // T-83 — KELULUSAN DISKAUN
    // ===============================================================

    public function test_diskaun_kecil_tidak_perlu_kelulusan(): void
    {
        config(['pos.discount_approval.requires_approval_above' => 20]);

        $this->assertFalse(app(DiscountApprovalService::class)->perluKelulusan(10));
        $this->assertTrue(app(DiscountApprovalService::class)->perluKelulusan(25));
    }

    public function test_diskaun_melebihi_had_mutlak_ditolak(): void
    {
        config(['pos.discount_approval.max_percent' => 50]);

        $this->expectException(ValidationException::class);
        app(DiscountApprovalService::class)->minta($this->t->id, null, 75, 75, $this->u->id);
    }

    private function jualanDiskaun(POSSale $sale): POSSale
    {
        POSSaleItem::create([
            'sale_id' => $sale->id,
            'product_name' => 'Item',
            'quantity' => 1,
            'unit_price' => 100,
            'line_total' => 100,
        ]);

        return $sale;
    }

    public function test_luluskan_diskaun_menanda_jualan(): void
    {
        config([
            'pos.discount_approval.requires_approval_above' => 20,
            'pos.discount_approval.max_percent' => 50,
        ]);

        $sale = $this->jualanDiskaun(POSSale::create([
            'tenant_id' => $this->t->id,
            'register_id' => $this->daftar->id,
            'user_id' => $this->u->id,
            'sale_no' => POSSale::generateSaleNo(),
            'status' => 'draft',
            'subtotal' => 100,
            'discount_amount' => 30,
            'total_amount' => 70,
            'amount_paid' => 0,
            'change_amount' => 0,
            'order_type' => 'takeaway',
        ]));

        $svc = app(DiscountApprovalService::class);
        $permohonan = $svc->minta($this->t->id, $sale->id, 30, 30, $this->u->id, 'Pelanggan tetap');

        $pengurus = \App\Models\User::create([
            'name' => 'Pengurus',
            'email' => 'pengurus@poso.my',
            'password' => bcrypt('RahsiaKuat123'),
        ]);

        $svc->luluskan($permohonan, $pengurus->id);

        $this->assertNotNull($sale->fresh()->discount_approved_at);
        $this->assertSame($pengurus->id, (int) $sale->fresh()->discount_approved_by);
    }

    public function test_tidak_boleh_luluskan_diskaun_sendiri(): void
    {
        config(['pos.discount_approval.requires_approval_above' => 20]);

        $svc = app(DiscountApprovalService::class);
        $permohonan = $svc->minta($this->t->id, null, 30, 30, $this->u->id);

        $this->expectException(ValidationException::class);
        $svc->luluskan($permohonan, $this->u->id);
    }

    public function test_bayar_tanpa_kelulusan_diskaun_besar_ditolak(): void
    {
        config([
            'pos.service_charge.rate' => 0,
            'pos.discount_approval.requires_approval_above' => 20,
            'pos.discount_approval.max_percent' => 50,
        ]);

        $sale = $this->jualanDiskaun(POSSale::create([
            'tenant_id' => $this->t->id,
            'register_id' => $this->daftar->id,
            'user_id' => $this->u->id,
            'sale_no' => POSSale::generateSaleNo(),
            'status' => 'draft',
            'subtotal' => 100,
            'discount_amount' => 30,
            'total_amount' => 70,
            'amount_paid' => 0,
            'change_amount' => 0,
            'order_type' => 'takeaway',
        ]));

        $this->expectException(ValidationException::class);
        app(SalePaymentService::class)->bayar($sale, [
            ['method' => 'cash', 'amount' => 70],
        ]);
    }

    public function test_bayar_dengan_kelulusan_diskaun_berjaya(): void
    {
        config([
            'pos.service_charge.rate' => 0,
            'pos.discount_approval.requires_approval_above' => 20,
            'pos.discount_approval.max_percent' => 50,
        ]);

        $sale = $this->jualanDiskaun(POSSale::create([
            'tenant_id' => $this->t->id,
            'register_id' => $this->daftar->id,
            'user_id' => $this->u->id,
            'sale_no' => POSSale::generateSaleNo(),
            'status' => 'draft',
            'subtotal' => 100,
            'discount_amount' => 30,
            'total_amount' => 70,
            'amount_paid' => 0,
            'change_amount' => 0,
            'order_type' => 'takeaway',
            'discount_approved_by' => $this->u->id,
            'discount_approved_at' => now(),
        ]));

        $sale = app(SalePaymentService::class)->bayar($sale, [
            ['method' => 'cash', 'amount' => 70],
        ]);

        $this->assertSame('completed', $sale->status);
        $this->assertSame('paid', $sale->payment_status);
    }
}
