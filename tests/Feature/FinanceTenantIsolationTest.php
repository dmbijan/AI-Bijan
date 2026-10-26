<?php

namespace Tests\Feature;

use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Models\JournalEntry;
use App\Modules\Finance\Models\JournalEntryLine;
use App\Modules\Finance\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Pengasingan tenant — modul KEWANGAN (Finance).
 *
 * ⚠️ Modul ini memiliki jurnal seluruh sistem. Kebocoran di sini bermakna
 * satu tenant boleh membaca/mengubah kewangan tenant lain — risiko paling
 * kritikal dalam sistem.
 *
 * Ditambah 26 Okt 2026 (P0-4) kerana `Finance` hanya ada 2 rujukan ujian
 * sebelum ini walaupun ia pemilik `journal_entries` + `chart_of_accounts`.
 */
class FinanceTenantIsolationTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;
    private JournalService $s;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@finance.my', 'Finance Sdn Bhd');
        $this->dalamTenant($this->t);
        $this->s = app(JournalService::class);
    }

    /** Cipta COA untuk tenant aktif. */
    private function akaun(string $code, string $nama, string $jenis = 'asset'): ChartOfAccount
    {
        return ChartOfAccount::create([
            'tenant_id' => $this->t->id,
            'code' => $code,
            'name' => $nama,
            'type' => $jenis,
            'is_active' => true,
            'allow_posting' => true,
        ]);
    }

    /** Cipta jurnal seimbang untuk tenant aktif. */
    private function jurnal(string $kod = '1000'): JournalEntry
    {
        $debit = $this->akaun($kod, 'Tunai');
        $kredit = $this->akaun($kod . '1', 'Hasil', 'revenue');

        return $this->s->createJournalEntry([
            'tenant_id' => $this->t->id,
            'entry_date' => '2026-10-20',
            'description' => 'Jurnal ujian',
            'lines' => [
                ['account_id' => $debit->id, 'debit' => 100, 'credit' => 0],
                ['account_id' => $kredit->id, 'debit' => 0, 'credit' => 100],
            ],
        ]);
    }

    // ===============================================================
    // PENGASINGAN — BACA
    // ===============================================================

    public function test_tenant_lain_tidak_nampak_carta_akaun(): void
    {
        $this->akaun('1000', 'Tunai Tenant Satu');

        // ⚠️ daftar() buat permintaan HTTP → konteks dikosongkan. Set semula.
        [, $t2] = $this->daftar('t2@finance.my', 'Tenant Dua Sdn Bhd');
        $this->dalamTenant($t2);

        // Konteks t2 hanya nampak akaun sendiri (tiada)
        $this->assertSame(0, ChartOfAccount::count(), 'Tenant dua nampak COA tenant satu!');

        // Semak rekod tenant satu wujud (buang skop tenant)
        $this->assertSame(
            1,
            ChartOfAccount::withoutGlobalScope('tenant')
                ->where('tenant_id', $this->t->id)
                ->count(),
            'Rekod tenant satu hilang — cipta gagal.'
        );
    }

    public function test_tenant_lain_tidak_nampak_jurnal(): void
    {
        $entry = $this->jurnal();

        [, $t2] = $this->daftar('t3@finance.my', 'Tenant Tiga Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertSame(0, JournalEntry::count(), 'Global scope gagal menapis jurnal.');
        $this->assertNull(JournalEntry::find($entry->id), 'find() menembusi skop tenant.');

        // Rekod tenant satu masih wujud
        $this->assertSame(
            1,
            JournalEntry::withoutGlobalScope('tenant')->where('tenant_id', $this->t->id)->count()
        );
    }

    public function test_tenant_lain_tidak_nampak_baris_jurnal(): void
    {
        $entry = $this->jurnal();

        $bilangan = JournalEntryLine::count();
        $this->assertSame(2, $bilangan, 'Jurnal sepatutnya ada 2 baris dalam tenant sendiri.');

        [, $t2] = $this->daftar('t4@finance.my', 'Tenant Empat Sdn Bhd');
        $this->dalamTenant($t2);

        // ⚠️ Model ini guna skop 'tenant_via_induk' — bukan 'tenant'
        $this->assertSame(
            0,
            JournalEntryLine::count(),
            'Baris jurnal tenant lain boleh dibaca — kebocoran kewangan.'
        );
    }

    // ===============================================================
    // PENGASINGAN — TULIS
    // ===============================================================

    public function test_dua_tenant_boleh_guna_kod_akaun_sama(): void
    {
        $this->akaun('1000', 'Tunai Satu');

        [, $t2] = $this->daftar('t5@finance.my', 'Tenant Lima Sdn Bhd');
        $this->dalamTenant($t2);

        // Buang skop global supaya boleh cipta untuk tenant lain dalam ujian
        ChartOfAccount::withoutGlobalScope('tenant')->create([
            'tenant_id' => $t2->id,
            'code' => '1000',
            'name' => 'Tunai Dua',
            'type' => 'asset',
            'is_active' => true,
            'allow_posting' => true,
        ]);

        $this->assertSame(
            1,
            ChartOfAccount::withoutGlobalScope('tenant')->where('tenant_id', $this->t->id)->where('code', '1000')->count()
        );
        $this->assertSame(
            1,
            ChartOfAccount::withoutGlobalScope('tenant')->where('tenant_id', $t2->id)->where('code', '1000')->count()
        );
    }

    public function test_tenant_id_tidak_boleh_diubah(): void
    {
        $akaun = $this->akaun('2000', 'Belanja');

        [, $t2] = $this->daftar('t6@finance.my', 'Tenant Enam Sdn Bhd');
        $this->dalamTenant($this->t);   // kembali ke konteks asal

        $this->expectException(\RuntimeException::class);
        $akaun->tenant_id = $t2->id;
        $akaun->save();
    }

    public function test_rekod_tidak_boleh_dicipta_tanpa_tenant_aktif(): void
    {
        app(\App\Support\Tenancy\TenantContext::class)->clear();

        $this->expectException(\RuntimeException::class);
        ChartOfAccount::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->t->id,
            'code' => '9999',
            'name' => 'Tiada Tenant',
            'type' => 'asset',
        ]);
    }

    public function test_tenant_id_tidak_sepadan_ditolak(): void
    {
        [, $t2] = $this->daftar('t7@finance.my', 'Tenant Tujuh Sdn Bhd');
        $this->dalamTenant($this->t);   // konteks = t1

        $this->expectException(\RuntimeException::class);
        ChartOfAccount::create([
            'tenant_id' => $t2->id,   // ≠ konteks
            'code' => '8888',
            'name' => 'Salah Tenant',
            'type' => 'asset',
        ]);
    }

    // ===============================================================
    // JURNAL — INTEGRITI
    // ===============================================================

    public function test_jurnal_seimbang_dicipta(): void
    {
        $entry = $this->jurnal();

        $this->assertSame('draft', $entry->status);
        $this->assertSame($this->t->id, $entry->tenant_id);

        $debit = (float) JournalEntryLine::withoutGlobalScopes()
            ->where('journal_entry_id', $entry->id)->sum('debit');
        $kredit = (float) JournalEntryLine::withoutGlobalScopes()
            ->where('journal_entry_id', $entry->id)->sum('credit');

        $this->assertSame(100.0, $debit);
        $this->assertSame(100.0, $kredit);
        $this->assertSame($debit, $kredit, 'Jurnal tidak seimbang.');
    }

    public function test_jurnal_tidak_seimbang_ditolak(): void
    {
        $debit = $this->akaun('3000', 'Tunai');
        $kredit = $this->akaun('3001', 'Hasil', 'revenue');

        $this->expectException(\Throwable::class);
        $this->s->createJournalEntry([
            'tenant_id' => $this->t->id,
            'entry_date' => '2026-10-20',
            'lines' => [
                ['account_id' => $debit->id, 'debit' => 100, 'credit' => 0],
                ['account_id' => $kredit->id, 'debit' => 0, 'credit' => 50],  // tidak seimbang
            ],
        ]);
    }

    public function test_nombor_jurnal_unik(): void
    {
        $e1 = $this->jurnal('4000');
        $e2 = $this->jurnal('5000');

        $this->assertNotSame(
            $e1->entry_number,
            $e2->entry_number,
            'Nombor jurnal bertindih dalam tenant yang sama — risiko kekeliruan audit.'
        );
    }

    // ===============================================================
    // API — SKOP TENANT
    // ===============================================================

    public function test_api_carta_akaun_hanya_tenant_sendiri(): void
    {
        $this->akaun('5000', 'Akaun Tenant Satu');

        $u2 = $this->daftar('t9@finance.my', 'Tenant Sembilan Sdn Bhd')[0];
        $t2 = \App\Models\Tenant::where('name', 'Tenant Sembilan Sdn Bhd')->firstOrFail();

        $this->sebagai($u2, $t2);

        // Tenant dua tiada COA sendiri — mesti pulang senarai kosong
        $respons = $this->getJson('/api/v1/finance/chart-of-accounts')->assertOk();

        $data = $respons->json('data') ?? $respons->json();
        $bilangan = is_array($data) && isset($data['data']) ? count($data['data']) : (is_array($data) ? count($data) : 0);

        $this->assertSame(0, $bilangan, 'Tenant dua nampak COA tenant satu melalui API (IDOR).');
    }

    /**
     * Regresi IDOR — pepijat 26 Okt 2026.
     *
     * Controller Finance/SCM dahulu membaca `$request->header('X-Tenant-ID')`.
     * Klien boleh menghantar header itu dengan ID tenant lain dan membaca
     * COA / jurnal / PO / pembekal tenant tersebut.
     * Pembetulan: guna `TenantContext::id()` (dari keahlian, bukan input).
     */
    public function test_idor_header_tenant_diabaikan(): void
    {
        $this->akaun('6000', 'Akaun Rahsia Tenant Satu');

        $u2 = $this->daftar('idor@finance.my', 'Tenant IDOR Sdn Bhd')[0];
        $t2 = \App\Models\Tenant::where('name', 'Tenant IDOR Sdn Bhd')->firstOrFail();

        $this->sebagai($u2, $t2);

        // Cuba mencuri data dengan menghantar ID tenant LAIN dalam header
        $respons = $this->withHeaders([
            'X-Tenant-ID' => (string) $this->t->id,
        ])->getJson('/api/v1/finance/chart-of-accounts')->assertOk();

        $json = $respons->json();
        $teks = json_encode($json);
        $this->assertStringNotContainsString(
            'Akaun Rahsia Tenant Satu',
            $teks,
            'IDOR: header X-Tenant-ID masih diterima — data tenant lain bocor.'
        );
    }

    public function test_api_laluan_tidak_berganda(): void
    {
        // Pepijat 26 Okt 2026: api/v1/finance/finance/* dicipta kerana provider
        // DAN routes/api.php kedua-duanya menambah prefix 'finance'.
        $koleksi = app('router')->getRoutes();
        $berganda = [];

        foreach ($koleksi as $laluan) {
            if (preg_match('#^api/v1/finance/finance#', $laluan->uri())) {
                $berganda[] = $laluan->uri();
            }
        }

        $this->assertSame(
            [],
            $berganda,
            'Laluan prefix berganda dikesan: ' . implode(', ', array_slice($berganda, 0, 5))
        );
    }
}
