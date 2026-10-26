<?php

namespace Tests\Feature;

use App\Modules\Expense\Models\Expense;
use App\Modules\Expense\Models\ExpenseCategory;
use App\Modules\Expense\Models\ExpenseClaim;
use App\Modules\Expense\Models\ExpenseClaimApproval;
use App\Modules\Expense\Models\ExpenseClaimItem;
use App\Modules\Expense\Models\ExpenseItem;
use App\Modules\Expense\Models\ExpenseReceipt;
use App\Modules\Expense\Services\ExpenseService;
use App\Modules\Finance\Models\ChartOfAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * B-Expense P1 — Pengurusan Perbelanjaan (BAB 89).
 *
 * Liputan: kategori, perbelanjaan, baris, resit + pendua, cadang kategori,
 * tuntutan + kelulusan, safety (tidak lulus sendiri), post ke jurnal, ringkasan,
 * pengasingan tenant dan API.
 */
class ExpenseTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;
    private ExpenseService $s;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@expense.my', 'Expense Sdn Bhd');
        $this->dalamTenant($this->t);
        $this->s = app(ExpenseService::class);
    }

    // ===============================================================
    // KATEGORI
    // ===============================================================

    public function test_cipta_kategori(): void
    {
        $k = $this->s->ciptaKategori($this->t->id, [
            'code' => 'TEST',
            'name' => 'Kategori Ujian',
            'receipt_threshold' => 25,
        ]);

        $this->assertSame('Kategori Ujian', $k->name);
        $this->assertTrue($k->requires_receipt);
        $this->assertTrue($k->requires_approval);
        $this->assertSame('25.00', number_format((float) $k->receipt_threshold, 2, '.', ''));
        $this->assertTrue($k->is_active);
    }

    public function test_jana_kategori_lalai_idempoten(): void
    {
        $pertama = $this->s->janaKategoriLalai($this->t->id);
        $kedua = $this->s->janaKategoriLalai($this->t->id);

        $this->assertGreaterThan(0, $pertama);
        $this->assertSame(0, $kedua);
        $this->assertSame($pertama, ExpenseCategory::where('tenant_id', $this->t->id)->count());
    }

    public function test_kategori_lalai_mempunyai_kod_unik(): void
    {
        $this->s->janaKategoriLalai($this->t->id);

        $kod = ExpenseCategory::where('tenant_id', $this->t->id)->pluck('code')->all();

        $this->assertSame(count($kod), count(array_unique($kod)));
        $this->assertContains('TRAVEL', $kod);
        $this->assertContains('MEALS', $kod);
    }

    public function test_senarai_kategori_hanya_aktif(): void
    {
        $this->s->ciptaKategori($this->t->id, ['code' => 'A1', 'name' => 'Aktif']);
        $this->s->ciptaKategori($this->t->id, ['code' => 'A2', 'name' => 'Mati', 'is_active' => false]);

        $senarai = $this->s->senaraiKategori($this->t->id);

        $this->assertCount(1, $senarai);
        $this->assertSame('Aktif', $senarai[0]['name']);
    }

    public function test_kategori_resit_wajib_ikut_ambang(): void
    {
        $k = $this->s->ciptaKategori($this->t->id, [
            'code' => 'R',
            'name' => 'Resit',
            'receipt_threshold' => 50,
        ]);

        $this->assertFalse($k->resitWajib(49.99));
        $this->assertTrue($k->resitWajib(50.00));
        $this->assertTrue($k->resitWajib(100.00));
    }

    public function test_kategori_tanpa_resit_tiada_wajib(): void
    {
        $k = $this->s->ciptaKategori($this->t->id, [
            'code' => 'NR',
            'name' => 'Tiada Resit',
            'requires_receipt' => false,
        ]);

        $this->assertFalse($k->resitWajib(9999));
    }

    // ===============================================================
    // PERBELANJAAN
    // ===============================================================

    public function test_cipta_perbelanjaan_dengan_baris(): void
    {
        $k = $this->s->ciptaKategori($this->t->id, ['code' => 'TRAVEL', 'name' => 'Travel']);

        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_category_id' => $k->id,
            'vendor_name' => 'Petronas',
            'expense_date' => '2026-10-20',
            'items' => [
                ['description' => 'Minyak', 'quantity' => 2, 'unit_price' => 100.00],
                ['description' => 'Tolak', 'quantity' => 1, 'unit_price' => 20.00],
            ],
        ]);

        $this->assertSame(Expense::STATUS_DRAFT, $p->status);
        $this->assertStringStartsWith('EXP-' . now()->format('Y') . '-', $p->expense_no);
        $this->assertSame('MYR', $p->currency);
        $this->assertSame(2, $p->items()->count());

        $this->assertSame('220.00', number_format((float) $p->subtotal, 2, '.', ''));
        $this->assertSame('220.00', number_format((float) $p->total_amount, 2, '.', ''));
        $this->assertSame('220.00', number_format((float) $p->amount_myr, 2, '.', ''));
    }

    public function test_baris_kira_jumlah_dengan_cukai(): void
    {
        $k = $this->s->ciptaKategori($this->t->id, ['code' => 'X', 'name' => 'X']);

        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_category_id' => $k->id,
            'expense_date' => '2026-10-20',
            'items' => [
                ['description' => 'Barang', 'quantity' => 1, 'unit_price' => 100.00, 'tax_amount' => 6.00],
            ],
        ]);

        $this->assertSame('106.00', number_format((float) $p->total_amount, 2, '.', ''));
        $this->assertSame('6.00', number_format((float) $p->tax_amount, 2, '.', ''));
    }

    public function test_perbelanjaan_multi_mata_wang(): void
    {
        $k = $this->s->ciptaKategori($this->t->id, ['code' => 'X', 'name' => 'X']);

        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_category_id' => $k->id,
            'expense_date' => '2026-10-20',
            'currency' => 'SGD',
            'exchange_rate' => 3.5,
            'items' => [['description' => 'Item', 'quantity' => 1, 'unit_price' => 100.00]],
        ]);

        $this->assertSame('SGD', $p->currency);
        $this->assertSame('350.00', number_format((float) $p->amount_myr, 2, '.', ''));
    }

    public function test_had_baris_perbelanjaan(): void
    {
        $k = $this->s->ciptaKategori($this->t->id, ['code' => 'X', 'name' => 'X']);

        $items = [];
        for ($i = 0; $i < 201; $i++) {
            $items[] = ['description' => "Item {$i}", 'quantity' => 1, 'unit_price' => 1];
        }

        $this->expectException(\RuntimeException::class);
        $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_category_id' => $k->id,
            'expense_date' => '2026-10-20',
            'items' => $items,
        ]);
    }

    public function test_hantar_perbelanjaan_tanpa_resit_ditolak(): void
    {
        $k = $this->s->ciptaKategori($this->t->id, [
            'code' => 'R',
            'name' => 'Perlu Resit',
            'receipt_threshold' => 50,
        ]);

        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_category_id' => $k->id,
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'Besar', 'quantity' => 1, 'unit_price' => 100]],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->s->hantarUntukKelulusan($this->t->id, $p->id);
    }

    public function test_hantar_perbelanjaan_dengan_resit_berjaya(): void
    {
        $k = $this->s->ciptaKategori($this->t->id, [
            'code' => 'R',
            'name' => 'Perlu Resit',
            'receipt_threshold' => 50,
        ]);

        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_category_id' => $k->id,
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'Besar', 'quantity' => 1, 'unit_price' => 100]],
        ]);

        $this->s->muatResit($this->t->id, [
            'file_path' => 'resit/abc.jpg',
            'file_hash' => 'hash123',
        ], $p->id);

        $p = $this->s->hantarUntukKelulusan($this->t->id, $p->id);

        $this->assertSame(Expense::STATUS_SUBMITTED, $p->status);
    }

    public function test_perbelanjaan_kecil_tanpa_resit_boleh_hantar(): void
    {
        $k = $this->s->ciptaKategori($this->t->id, [
            'code' => 'R',
            'name' => 'Perlu Resit',
            'receipt_threshold' => 50,
        ]);

        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_category_id' => $k->id,
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'Kecil', 'quantity' => 1, 'unit_price' => 20]],
        ]);

        $p = $this->s->hantarUntukKelulusan($this->t->id, $p->id);

        $this->assertSame(Expense::STATUS_SUBMITTED, $p->status);
    }

    public function test_peralihan_status_tidak_sah_ditolak(): void
    {
        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10]],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->s->luluskan($this->t->id, $p->id, 999, null);
    }

    public function test_luluskan_perbelanjaan(): void
    {
        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10]],
        ]);

        $p = $this->s->hantarUntukKelulusan($this->t->id, $p->id);
        $p = $this->s->luluskan($this->t->id, $p->id, 555, 'OK');

        $this->assertSame(Expense::STATUS_APPROVED, $p->status);
        $this->assertSame(555, $p->approved_by_user_id);
        $this->assertNotNull($p->approved_at);
    }

    public function test_staff_tidak_boleh_luluskan_sendiri(): void
    {
        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'created_by_user_id' => 777,
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10]],
        ]);

        $p = $this->s->hantarUntukKelulusan($this->t->id, $p->id);

        $this->expectException(\RuntimeException::class);
        $this->s->luluskan($this->t->id, $p->id, 777, null);
    }

    public function test_tolak_perbelanjaan(): void
    {
        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10]],
        ]);

        $p = $this->s->hantarUntukKelulusan($this->t->id, $p->id);
        $p = $this->s->tolak($this->t->id, $p->id, 555, 'Tak munasabah');

        $this->assertSame(Expense::STATUS_REJECTED, $p->status);
        $this->assertSame('Tak munasabah', $p->internal_notes);
    }

    // ===============================================================
    // RESIT
    // ===============================================================

    public function test_muat_resit(): void
    {
        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10]],
        ]);

        $r = $this->s->muatResit($this->t->id, [
            'file_path' => 'resit/1.jpg',
            'file_hash' => 'hashabc',
            'file_mime' => 'image/jpeg',
            'file_size' => 12345,
        ], $p->id);

        $this->assertSame('resit/1.jpg', $r->file_path);
        $this->assertFalse($r->is_verified);
        $this->assertTrue($r->is_primary);
        $this->assertFalse($r->is_duplicate_suspected);
        $this->assertSame(1, $p->receipts()->count());
    }

    public function test_had_resit_per_perbelanjaan(): void
    {
        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10]],
        ]);

        for ($i = 1; $i <= ExpenseService::MAX_RECEIPTS_PER_EXPENSE; $i++) {
            $this->s->muatResit($this->t->id, [
                'file_path' => "resit/{$i}.jpg",
                'file_hash' => "hash{$i}",
            ], $p->id);
        }

        $this->expectException(\RuntimeException::class);
        $this->s->muatResit($this->t->id, [
            'file_path' => 'resit/terlalu.jpg',
            'file_hash' => 'hash999',
        ], $p->id);
    }

    public function test_sahkan_resit(): void
    {
        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10]],
        ]);

        $r = $this->s->muatResit($this->t->id, [
            'file_path' => 'resit/1.jpg',
            'file_hash' => 'hashabc',
        ], $p->id);

        $r = $this->s->sahkanResit($this->t->id, $r->id, 999);

        $this->assertTrue($r->is_verified);
        $this->assertSame(999, $r->verified_by_user_id);
        $this->assertNotNull($r->verified_at);
        $this->assertTrue($r->disahkan());
    }

    public function test_resit_pendua_dikesan(): void
    {
        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10]],
        ]);

        $r1 = $this->s->muatResit($this->t->id, [
            'file_path' => 'resit/1.jpg',
            'file_hash' => 'sama',
            'merchant_name' => 'Petronas',
            'receipt_total' => 100,
        ], $p->id);

        $r2 = $this->s->muatResit($this->t->id, [
            'file_path' => 'resit/2.jpg',
            'file_hash' => 'sama',
            'merchant_name' => 'Petronas',
            'receipt_total' => 100,
        ], $p->id);

        $this->assertTrue($r2->is_duplicate_suspected);
        $this->assertSame($r1->id, (int) $r2->duplicate_of_receipt_id);

        $ditemui = $this->s->cariPendua($this->t->id, $r2->id);

        $this->assertGreaterThanOrEqual(1, count($ditemui));
    }

    public function test_cadang_kategori_dari_merchant(): void
    {
        $this->s->ciptaKategori($this->t->id, [
            'code' => 'TRAVEL',
            'name' => 'Travel',
            'merchant_keywords' => ['petronas', 'shell'],
        ]);
        $this->s->ciptaKategori($this->t->id, [
            'code' => 'MEALS',
            'name' => 'Meals',
            'merchant_keywords' => ['mcdonald'],
        ]);

        $cadangan = $this->s->cadangKategori($this->t->id, 'PETRONAS SDN BHD');

        $this->assertNotNull($cadangan['category_id']);
        $this->assertGreaterThan(0.5, $cadangan['confidence']);
        $this->assertSame('TRAVEL', $cadangan['candidates'][0]['code']);
    }

    public function test_cadang_kategori_tiada_padan_kosong(): void
    {
        $this->s->ciptaKategori($this->t->id, [
            'code' => 'TRAVEL',
            'name' => 'Travel',
            'merchant_keywords' => ['petronas'],
        ]);

        $cadangan = $this->s->cadangKategori($this->t->id, 'Kedai Unknown');

        $this->assertNull($cadangan['category_id']);
        $this->assertSame(0.0, $cadangan['confidence']);
        $this->assertSame([], $cadangan['candidates']);
    }

    // ===============================================================
    // TUNTUTAN
    // ===============================================================

    public function test_cipta_tuntutan_dengan_baris(): void
    {
        $tuntutan = $this->s->ciptaTuntutan($this->t->id, 111, [
            'title' => 'Tuntutan Perjalanan',
            'claim_date' => '2026-10-20',
            'items' => [
                ['item_date' => '2026-10-18', 'description' => 'Minyak', 'unit_price' => 80],
                ['item_date' => '2026-10-19', 'description' => 'Toll', 'unit_price' => 20],
            ],
        ]);

        $this->assertSame(ExpenseClaim::STATUS_DRAFT, $tuntutan->status);
        $this->assertSame(111, $tuntutan->claimant_user_id);
        $this->assertStringStartsWith('CLM-' . now()->format('Y') . '-', $tuntutan->claim_no);
        $this->assertSame(2, $tuntutan->items()->count());
        $this->assertSame('100.00', number_format((float) $tuntutan->total_amount, 2, '.', ''));
    }

    public function test_mileage_dikira_otomatis(): void
    {
        $tuntutan = $this->s->ciptaTuntutan($this->t->id, 111, [
            'title' => 'Mileage',
            'claim_date' => '2026-10-20',
            'mileage_distance_km' => 50,
        ]);

        $tuntutan = $tuntutan->fresh();

        $this->assertSame('30.00', number_format((float) $tuntutan->mileage_amount, 2, '.', ''));
        $this->assertSame('30.00', number_format((float) $tuntutan->total_amount, 2, '.', ''));
    }

    public function test_per_diem_dikira_otomatis(): void
    {
        $tuntutan = $this->s->ciptaTuntutan($this->t->id, 111, [
            'title' => 'Per Diem',
            'claim_date' => '2026-10-20',
            'per_diem_days' => 3,
            'per_diem_rate' => 60,
        ]);

        $tuntutan = $tuntutan->fresh();

        $this->assertSame('180.00', number_format((float) $tuntutan->per_diem_amount, 2, '.', ''));
        $this->assertSame('180.00', number_format((float) $tuntutan->total_amount, 2, '.', ''));
    }

    public function test_hantar_tuntutan_kosong_ditolak(): void
    {
        $tuntutan = $this->s->ciptaTuntutan($this->t->id, 111, [
            'title' => 'Kosong',
            'claim_date' => '2026-10-20',
        ]);

        $this->expectException(\RuntimeException::class);
        $this->s->hantarTuntutan($this->t->id, $tuntutan->id, 111);
    }

    public function test_hantar_tuntutan_oleh_pemohon(): void
    {
        $tuntutan = $this->s->ciptaTuntutan($this->t->id, 111, [
            'title' => 'Perjalanan',
            'claim_date' => '2026-10-20',
            'items' => [['item_date' => '2026-10-18', 'description' => 'X', 'unit_price' => 50]],
        ]);

        $tuntutan = $this->s->hantarTuntutan($this->t->id, $tuntutan->id, 111);

        $this->assertSame(ExpenseClaim::STATUS_SUBMITTED, $tuntutan->status);
        $this->assertNotNull($tuntutan->submitted_at);
        $this->assertSame(1, $tuntutan->approvals()->count());
        $this->assertSame(ExpenseClaimApproval::ACTION_SUBMIT, $tuntutan->approvals()->first()->action);
    }

    public function test_hantar_tuntutan_oleh_orang_lain_ditolak(): void
    {
        $tuntutan = $this->s->ciptaTuntutan($this->t->id, 111, [
            'title' => 'Perjalanan',
            'claim_date' => '2026-10-20',
            'items' => [['item_date' => '2026-10-18', 'description' => 'X', 'unit_price' => 50]],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->s->hantarTuntutan($this->t->id, $tuntutan->id, 222);
    }

    public function test_luluskan_tuntutan_dan_jejak_kelulusan(): void
    {
        $tuntutan = $this->s->ciptaTuntutan($this->t->id, 111, [
            'title' => 'Perjalanan',
            'claim_date' => '2026-10-20',
            'items' => [['item_date' => '2026-10-18', 'description' => 'X', 'unit_price' => 50]],
        ]);

        $tuntutan = $this->s->hantarTuntutan($this->t->id, $tuntutan->id, 111);
        $tuntutan = $this->s->luluskanTuntutan($this->t->id, $tuntutan->id, 222, 'OK');

        $this->assertSame(ExpenseClaim::STATUS_APPROVED, $tuntutan->status);
        $this->assertSame(222, $tuntutan->approver_user_id);
        $this->assertNotNull($tuntutan->approved_at);
        $this->assertSame(2, $tuntutan->fresh()->approvals()->count());

        $kelulusan = $tuntutan->fresh()->approvals()
            ->where('action', ExpenseClaimApproval::ACTION_APPROVE)
            ->first();
        $this->assertNotNull($kelulusan);
        $this->assertSame(ExpenseClaimApproval::ACTION_APPROVE, $kelulusan->action);
        $this->assertSame('OK', $kelulusan->comment);    }

    public function test_pemohon_tidak_boleh_luluskan_tuntutan_sendiri(): void
    {
        $tuntutan = $this->s->ciptaTuntutan($this->t->id, 111, [
            'title' => 'Perjalanan',
            'claim_date' => '2026-10-20',
            'items' => [['item_date' => '2026-10-18', 'description' => 'X', 'unit_price' => 50]],
        ]);

        $tuntutan = $this->s->hantarTuntutan($this->t->id, $tuntutan->id, 111);

        $this->expectException(\RuntimeException::class);
        $this->s->luluskanTuntutan($this->t->id, $tuntutan->id, 111, null);
    }

    public function test_tolak_tuntutan(): void
    {
        $tuntutan = $this->s->ciptaTuntutan($this->t->id, 111, [
            'title' => 'Perjalanan',
            'claim_date' => '2026-10-20',
            'items' => [['item_date' => '2026-10-18', 'description' => 'X', 'unit_price' => 50]],
        ]);

        $tuntutan = $this->s->hantarTuntutan($this->t->id, $tuntutan->id, 111);
        $tuntutan = $this->s->tolakTuntutan($this->t->id, $tuntutan->id, 222, 'Tak perlu');

        $this->assertSame(ExpenseClaim::STATUS_REJECTED, $tuntutan->status);
        $this->assertSame('Tak perlu', $tuntutan->rejection_reason);
        $this->assertNotNull($tuntutan->rejected_at);
    }

    public function test_tuntutan_dibayar(): void
    {
        $tuntutan = $this->s->ciptaTuntutan($this->t->id, 111, [
            'title' => 'Perjalanan',
            'claim_date' => '2026-10-20',
            'items' => [['item_date' => '2026-10-18', 'description' => 'X', 'unit_price' => 50]],
        ]);

        $tuntutan = $this->s->hantarTuntutan($this->t->id, $tuntutan->id, 111);
        $tuntutan = $this->s->luluskanTuntutan($this->t->id, $tuntutan->id, 222, null);
        $tuntutan = $this->s->tandakanTuntutanDibayar($this->t->id, $tuntutan->id, 222, 50);

        $this->assertSame(ExpenseClaim::STATUS_REIMBURSED, $tuntutan->status);
        $this->assertSame('50.00', number_format((float) $tuntutan->reimbursed_amount, 2, '.', ''));
        $this->assertNotNull($tuntutan->reimbursed_at);
    }

    // ===============================================================
    // POST KE JURNAL
    // ===============================================================

    public function test_post_perbelanjaan_ke_jurnal(): void
    {
        $akaun = ChartOfAccount::create([
            'tenant_id' => $this->t->id,
            'code' => '61000',
            'name' => 'Travel Expense',
            'type' => 'expense',
            'subtype' => 'operating_expense',
            'normal_balance' => 'debit',
            'is_active' => true,
            'is_system' => false,
        ]);

        $bank = ChartOfAccount::create([
            'tenant_id' => $this->t->id,
            'code' => '10100',
            'name' => 'Bank',
            'type' => 'asset',
            'subtype' => 'bank',
            'normal_balance' => 'debit',
            'is_active' => true,
            'is_system' => false,
        ]);

        $k = $this->s->ciptaKategori($this->t->id, [
            'code' => 'TRAVEL',
            'name' => 'Travel',
            'chart_of_account_id' => $akaun->id,
            'requires_receipt' => false,
        ]);

        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_category_id' => $k->id,
            'vendor_name' => 'Petronas',
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'Minyak', 'quantity' => 1, 'unit_price' => 100]],
        ]);

        $p = $this->s->hantarUntukKelulusan($this->t->id, $p->id);
        $p = $this->s->luluskan($this->t->id, $p->id, 555, null);
        $p = $this->s->postKeJurnal($this->t->id, $p->id, $bank->id);

        $this->assertSame(Expense::STATUS_POSTED, $p->status);
        $this->assertTrue($p->berkesan());

        $custom = (array) ($p->custom_fields ?? []);
        $this->assertArrayHasKey('journal_entry_id', $custom);
        $this->assertArrayHasKey('journal_entry_number', $custom);
    }

    public function test_post_tanpa_coa_ditolak(): void
    {
        $k = $this->s->ciptaKategori($this->t->id, ['code' => 'X', 'name' => 'Tanpa COA']);

        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_category_id' => $k->id,
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10]],
        ]);

        $p = $this->s->hantarUntukKelulusan($this->t->id, $p->id);
        $p = $this->s->luluskan($this->t->id, $p->id, 555, null);

        $this->expectException(\RuntimeException::class);
        $this->s->postKeJurnal($this->t->id, $p->id, 1);
    }

    public function test_tandakan_dibayar(): void
    {
        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 100]],
        ]);

        $p = $this->s->hantarUntukKelulusan($this->t->id, $p->id);
        $p = $this->s->luluskan($this->t->id, $p->id, 555, null);

        // Paksa ke posted untuk ujian bayaran
        $p->update(['status' => Expense::STATUS_POSTED]);

        $p = $this->s->tandakanDibayar($this->t->id, $p->id, 60);

        $this->assertSame(Expense::PAYMENT_PARTIAL, $p->payment_status);
        $this->assertSame('60.00', number_format((float) $p->paid_amount, 2, '.', ''));
        $this->assertSame('40.00', number_format($p->baki(), 2, '.', ''));

        $p = $this->s->tandakanDibayar($this->t->id, $p->id, 40);

        $this->assertSame(Expense::PAYMENT_PAID, $p->payment_status);
        $this->assertSame(Expense::STATUS_PAID, $p->status);
    }

    // ===============================================================
    // RINGKASAN
    // ===============================================================

    public function test_ringkasan_kosong(): void
    {
        $r = $this->s->ringkasan($this->t->id);

        $this->assertSame(0.0, $r['jumlah_perbelanjaan']);
        $this->assertSame(0, $r['bilangan_perbelanjaan']);
        $this->assertSame(0, $r['tuntutan_menunggu']);
        $this->assertArrayHasKey('mengikut_kategori', $r);
    }

    public function test_ringkasan_kira_kategori(): void
    {
        $k = $this->s->ciptaKategori($this->t->id, ['code' => 'TRAVEL', 'name' => 'Travel', 'requires_receipt' => false]);

        $p = $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_category_id' => $k->id,
            'expense_date' => now()->toDateString(),
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 100]],
        ]);

        $p = $this->s->hantarUntukKelulusan($this->t->id, $p->id);
        $this->s->luluskan($this->t->id, $p->id, 555, null);

        $r = $this->s->ringkasan($this->t->id);

        $this->assertSame('100.00', number_format($r['jumlah_perbelanjaan'], 2, '.', ''));
        $this->assertCount(1, $r['mengikut_kategori']);
        $this->assertSame('Travel', $r['mengikut_kategori'][0]['nama']);
    }

    public function test_ringkasan_kira_tuntutan_menunggu(): void
    {
        $tuntutan = $this->s->ciptaTuntutan($this->t->id, 111, [
            'title' => 'X',
            'claim_date' => now()->toDateString(),
            'items' => [['item_date' => now()->toDateString(), 'description' => 'X', 'unit_price' => 50]],
        ]);

        $this->s->hantarTuntutan($this->t->id, $tuntutan->id, 111);

        $r = $this->s->ringkasan($this->t->id);

        $this->assertSame(1, $r['tuntutan_menunggu']);
    }

    // ===============================================================
    // PENGASINGAN TENANT
    // ===============================================================

    public function test_pengasingan_tenant_perbelanjaan(): void
    {
        [, $t2] = $this->daftar('t2@expense.my', 'Tenant Dua Sdn Bhd');

        $this->s->ciptaPerbelanjaan($this->t->id, [
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'X', 'quantity' => 1, 'unit_price' => 10]],
        ]);

        $this->dalamTenant($t2);
        $this->s->ciptaPerbelanjaan($t2->id, [
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'Y', 'quantity' => 1, 'unit_price' => 20]],
        ]);

        $this->assertSame(1, Expense::withoutGlobalScopes()->where('tenant_id', $this->t->id)->count());
        $this->assertSame(1, Expense::withoutGlobalScopes()->where('tenant_id', $t2->id)->count());
    }

    public function test_pengasingan_tenant_kategori(): void
    {
        [, $t2] = $this->daftar('t3@expense.my', 'Tenant Tiga Sdn Bhd');

        $this->s->ciptaKategori($this->t->id, ['code' => 'SAME', 'name' => 'A']);

        $this->dalamTenant($t2);
        $this->s->ciptaKategori($t2->id, ['code' => 'SAME', 'name' => 'B']);

        $this->assertSame(1, ExpenseCategory::withoutGlobalScopes()->where('tenant_id', $this->t->id)->count());
        $this->assertSame(1, ExpenseCategory::withoutGlobalScopes()->where('tenant_id', $t2->id)->count());
    }

    // ===============================================================
    // API
    // ===============================================================

    public function test_api_ringkasan(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/expenses/summary')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['jumlah_perbelanjaan', 'bilangan_perbelanjaan', 'mengikut_kategori']]);
    }

    public function test_api_kategori(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/expenses/categories')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_api_cipta_perbelanjaan(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->postJson('/api/v1/expenses', [
            'expense_date' => '2026-10-20',
            'vendor_name' => 'Petronas',
            'items' => [
                ['description' => 'Minyak', 'quantity' => 1, 'unit_price' => 100],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.vendor_name', 'Petronas')
            ->assertJsonPath('data.status', Expense::STATUS_DRAFT);
    }

    public function test_api_hantar_tanpa_resit_pulang_422(): void
    {
        $k = $this->s->ciptaKategori($this->t->id, [
            'code' => 'R',
            'name' => 'Perlu Resit',
            'receipt_threshold' => 50,
        ]);

        $this->sebagai($this->u, $this->t);

        $res = $this->postJson('/api/v1/expenses', [
            'expense_category_id' => $k->id,
            'expense_date' => '2026-10-20',
            'items' => [['description' => 'Besar', 'quantity' => 1, 'unit_price' => 100]],
        ])->assertCreated();

        $id = $res->json('data.id');

        $this->postJson("/api/v1/expenses/{$id}/submit")
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_api_pending_approvals(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/expenses/pending-approvals')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['perbelanjaan', 'tuntutan']]);
    }

    public function test_api_cipta_tuntutan(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->postJson('/api/v1/expenses/claims', [
            'title' => 'Tuntutan API',
            'claim_date' => '2026-10-20',
            'items' => [
                ['item_date' => '2026-10-18', 'description' => 'Minyak', 'unit_price' => 80],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', ExpenseClaim::STATUS_DRAFT);
    }

    public function test_api_cadang_kategori(): void
    {
        $this->s->ciptaKategori($this->t->id, [
            'code' => 'TRAVEL',
            'name' => 'Travel',
            'merchant_keywords' => ['petronas'],
        ]);

        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/expenses/suggest-category?merchant=Petronas')
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
