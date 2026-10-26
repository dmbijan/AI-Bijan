<?php

namespace Tests\Feature;

use App\Modules\Invoicing\Models\CreditNote;
use App\Modules\Invoicing\Models\DebitNote;
use App\Modules\Invoicing\Models\Invoice;
use App\Modules\Invoicing\Models\InvoiceItem;
use App\Modules\Invoicing\Models\Quotation;
use App\Modules\Invoicing\Models\Receipt;
use App\Modules\Invoicing\Models\SalesOrder;
use App\Modules\Invoicing\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Pengasingan tenant + integriti nombor dokumen — modul INVOICING.
 *
 * Ditambah 27 Okt 2026 (P1-1b). Dua kelas risiko diuji:
 *
 *   A. Pengasingan bacaan/tulisan (skop tenant)
 *   B. ⚠️ Nombor dokumen: setiap model menjana nombor dengan `self::whereYear()`
 *      yang DITAPIS tenant, tetapi index DB ialah UNIQUE **GLOBAL**. Ini bermakna
 *      tenant kedua yang mencipta invois pertama pada tahun yang sama akan
 *      dapat "UNIQUE constraint failed" — **tidak boleh menerbitkan invois**.
 *      Sama seperti pepijat `JournalEntry` (P0-6), tetapi 6 kali ganda.
 */
class InvoicingTenantIsolationTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;
    private InvoiceService $s;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@inv.my', 'Invoicing Sdn Bhd');
        $this->dalamTenant($this->t);
        $this->s = app(InvoiceService::class);
    }

    /** Invois minimum yang sah untuk tenant aktif. */
    private function invois(array $ganti = []): Invoice
    {
        return Invoice::create(array_merge([
            'customer_name' => 'Pelanggan Ujian',
            'issue_date' => '2026-10-20',
            'due_date' => '2026-11-20',
            'subtotal' => 100,
            'tax_total' => 6,
            'grand_total' => 106,
            'status' => 'draft',
        ], $ganti));
    }

    // ===============================================================
    // A. PENGASINGAN BACA
    // ===============================================================

    public function test_tenant_lain_tidak_nampak_invois(): void
    {
        $inv = $this->invois(['customer_name' => 'Rahsia Tenant Satu']);

        [, $t2] = $this->daftar('t2@inv.my', 'Tenant Dua Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertSame(0, Invoice::count(), 'Invois tenant lain kelihatan.');
        $this->assertNull(Invoice::find($inv->id), 'find() menembusi skop tenant.');

        // Rekod tenant satu masih wujud
        $this->assertSame(
            1,
            Invoice::withoutGlobalScope('tenant')->where('tenant_id', $this->t->id)->count()
        );
    }

    public function test_tenant_lain_tidak_nampak_quotation(): void
    {
        Quotation::create([
            'customer_name' => 'Sebut Harga Satu',
            'issue_date' => '2026-10-20',
            'validity_days' => 30,
        ]);

        [, $t2] = $this->daftar('t3@inv.my', 'Tenant Tiga Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertSame(0, Quotation::count());
    }

    public function test_tenant_lain_tidak_nampak_sales_order(): void
    {
        SalesOrder::create([
            'customer_name' => 'Pesanan Satu',
            'order_date' => '2026-10-20',
        ]);

        [, $t2] = $this->daftar('t4@inv.my', 'Tenant Empat Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertSame(0, SalesOrder::count());
    }

    // ===============================================================
    // A. PENGASINGAN TULIS
    // ===============================================================

    public function test_tidak_boleh_cipta_invois_untuk_tenant_lain(): void
    {
        [, $t2] = $this->daftar('t5@inv.my', 'Tenant Lima Sdn Bhd');
        $this->dalamTenant($this->t);

        $this->expectException(\RuntimeException::class);
        $this->invois(['tenant_id' => $t2->id]);
    }

    public function test_tenant_id_invois_tidak_boleh_diubah(): void
    {
        $inv = $this->invois();

        [, $t2] = $this->daftar('t6@inv.my', 'Tenant Enam Sdn Bhd');
        $this->dalamTenant($this->t);

        $this->expectException(\RuntimeException::class);
        $inv->tenant_id = $t2->id;
        $inv->save();
    }

    public function test_invois_tidak_boleh_dicipta_tanpa_tenant(): void
    {
        app(\App\Support\Tenancy\TenantContext::class)->clear();

        $this->expectException(\RuntimeException::class);
        $this->invois();
    }

    // ===============================================================
    // B. NOMBOR DOKUMEN — PEPIJAT KRITIKAL
    // ===============================================================

    /**
     * Nombor dokumen mesti UNIK dalam tenant, tetapi boleh berulang merentas
     * tenant (siri perakaunan berasingan). Yang penting: tiada UNIQUE violation.
     */
    public function test_nombor_invois_unik_dalam_tenant(): void
    {
        $a = $this->invois();
        $b = $this->invois();

        $this->assertNotSame($a->invoice_number, $b->invoice_number,
            'Dua invois dalam tenant sama tidak boleh berkongsi nombor.');
    }

    public function test_nombor_invois_tenant_kedua_bermula_semula(): void
    {
        $inv1 = $this->invois();

        [, $t2] = $this->daftar('t7@inv.my', 'Tenant Tujuh Sdn Bhd');
        $this->dalamTenant($t2);

        // Tenant kedua mesti boleh terbit invois (dahulu: UNIQUE violation)
        $inv2 = $this->invois();

        $this->assertNotNull($inv2->invoice_number, 'Tenant kedua tidak dapat nombor invois.');
        $this->assertSame($this->t->id, $inv1->tenant_id);
        $this->assertSame($t2->id, $inv2->tenant_id);
    }

    public function test_nombor_quotation_boleh_ulang_merentas_tenant(): void
    {
        $q1 = Quotation::create(['customer_name' => 'A', 'issue_date' => '2026-10-20']);

        [, $t2] = $this->daftar('t8@inv.my', 'Tenant Lapan Sdn Bhd');
        $this->dalamTenant($t2);
        $q2 = Quotation::create(['customer_name' => 'B', 'issue_date' => '2026-10-20']);

        // Siri berasingan per tenant — boleh sama, yang penting tiada violation
        $this->assertNotNull($q1->quotation_number);
        $this->assertNotNull($q2->quotation_number);
    }

    public function test_nombor_sales_order_boleh_ulang_merentas_tenant(): void
    {
        $o1 = SalesOrder::create(['customer_name' => 'A', 'order_date' => '2026-10-20']);

        [, $t2] = $this->daftar('t9@inv.my', 'Tenant Sembilan Sdn Bhd');
        $this->dalamTenant($t2);
        $o2 = SalesOrder::create(['customer_name' => 'B', 'order_date' => '2026-10-20']);

        $this->assertNotNull($o1->order_number);
        $this->assertNotNull($o2->order_number);
    }

    public function test_nombor_credit_note_boleh_ulang_merentas_tenant(): void
    {
        $c1 = CreditNote::create([
            'customer_name' => 'A',
            'reason' => 'Pulangan',
            'original_amount' => 10,
            'credit_amount' => 10,
        ]);

        [, $t2] = $this->daftar('t10@inv.my', 'Tenant Sepuluh Sdn Bhd');
        $this->dalamTenant($t2);
        $c2 = CreditNote::create([
            'customer_name' => 'B',
            'reason' => 'Pulangan',
            'original_amount' => 10,
            'credit_amount' => 10,
        ]);

        $this->assertNotNull($c1->credit_note_number);
        $this->assertNotNull($c2->credit_note_number);
    }

    public function test_nombor_debit_note_boleh_ulang_merentas_tenant(): void
    {
        $d1 = DebitNote::create([
            'customer_name' => 'A',
            'reason' => 'Caj tambahan',
            'amount' => 10,
        ]);

        [, $t2] = $this->daftar('t11@inv.my', 'Tenant Sebelas Sdn Bhd');
        $this->dalamTenant($t2);
        $d2 = DebitNote::create([
            'customer_name' => 'B',
            'reason' => 'Caj tambahan',
            'amount' => 10,
        ]);

        $this->assertNotNull($d1->debit_note_number);
        $this->assertNotNull($d2->debit_note_number);
    }

    public function test_receipt_boleh_diterbitkan_oleh_dua_tenant(): void
    {
        $inv = $this->invois();
        $r1 = $this->s->recordPayment($inv, 50, ['method' => 'cash']);

        [, $t2] = $this->daftar('t12@inv.my', 'Tenant Dua Belas Sdn Bhd');
        $this->dalamTenant($t2);
        $inv2 = $this->invois();
        $r2 = $this->s->recordPayment($inv2, 50, ['method' => 'cash']);

        $this->assertNotNull($r1->receipt_number);
        $this->assertNotNull($r2->receipt_number, 'Tenant kedua tidak dapat terbit resit.');
    }

    // ===============================================================
    // B. JADUAL ANAK
    // ===============================================================

    /**
     * ⚠️ JADUAL ANAK TANPA SKOP — didokumenkan sebagai risiko terbuka.
     *
     * `InvoiceItem` tiada kolum `tenant_id` (betul ikut konvensyen) tetapi juga
     * TIADA skop `*_via_induk`. Ujian ini MENDOKUMENTASIKAN jurang itu.
     * Ia akan bertukar menjadi lulus apabila skop ditambah (P1-1c).
     *
     * Untuk keselamatan sekarang: akses item MESTI melalui relasi induk
     * (`$invoice->items`) yang sudah ditapis oleh skop `Invoice`.
     */
    public function test_invoice_item_tiada_skop_sendiri_dokumentasi(): void
    {
        $inv = $this->invois();

        InvoiceItem::create([
            'invoice_id' => $inv->id,
            'description' => 'Item Rahsia',
            'quantity' => 1,
            'unit_price' => 100,
            'line_total' => 100,
        ]);

        [, $t2] = $this->daftar('t13@inv.my', 'Tenant Tiga Belas Sdn Bhd');
        $this->dalamTenant($t2);

        // Melalui relasi induk: SELAMAT (induk ditapis)
        $melaluiInduk = InvoiceItem::whereIn(
            'invoice_id',
            Invoice::query()->select('id')
        )->count();
        $this->assertSame(0, $melaluiInduk, 'Akses melalui induk mesti selamat.');

        // Query terus: TERDEDAH (jurang didokumenkan)
        $terus = InvoiceItem::query()->where('invoice_id', $inv->id)->count();
        $this->assertSame(
            1,
            $terus,
            'Jika ini 0, skop *_via_induk sudah ditambah — kemas kini ujian ini (P1-1c).'
        );
    }

    // ===============================================================
    // C. API
    // ===============================================================

    public function test_api_invois_tenant_lain_pulang_404(): void
    {
        $inv = $this->invois();

        $u2 = $this->daftar('t14@inv.my', 'Tenant Empat Belas Sdn Bhd')[0];
        $t2 = \App\Models\Tenant::where('name', 'Tenant Empat Belas Sdn Bhd')->firstOrFail();

        $this->sebagai($u2, $t2);

        $this->getJson('/api/v1/invoicing/invoices/' . $inv->id)->assertNotFound();
    }
}
