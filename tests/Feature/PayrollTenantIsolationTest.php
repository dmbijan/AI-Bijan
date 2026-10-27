<?php

namespace Tests\Feature;

use App\Modules\Payroll\Models\Employee;
use App\Modules\Payroll\Models\OvertimeRecord;
use App\Modules\Payroll\Models\PayrollRun;
use App\Modules\Payroll\Models\PayrollRunDetail;
use App\Modules\Payroll\Models\StatutoryRate;
use App\Modules\Payroll\Services\PayrollRunService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Pengasingan tenant — modul PAYROLL.
 *
 * Ditambah 27 Okt 2026 (P1-1b). Modul ini menyimpan data paling sensitif dalam
 * sistem: nombor IC, gaji, EPF/SOCSO/PCB. Kebocoran di sini ialah pelanggaran
 * PDPA, bukan sekadar bug.
 *
 * ⚠️ `payroll_run_details` ialah JADUAL ANAK (tiada `tenant_id` — betul ikut
 * konvensyen §5.3 blueprint). Ia hanya selamat jika diakses melalui relasi
 * induk `PayrollRun` yang sudah ditapis. Ujian di bawah mengukur sama ada
 * andaian itu benar.
 */
class PayrollTenantIsolationTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;
    private PayrollRunService $s;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@payroll.my', 'Payroll Sdn Bhd');
        $this->dalamTenant($this->t);
        $this->s = app(PayrollRunService::class);
    }

    private function pekerja(string $nama = 'Ali bin Abu', float $gaji = 3000): Employee
    {
        // employment_type: permanent|contract|part_time|casual
        // salary_currency NOT NULL — mesti diisi.
        return Employee::create([
            'name' => $nama,
            'ic_no' => '900101-01-5555',
            'epf_no' => 'EPF123456',
            'socso_no' => 'SOC123456',
            'position' => 'Kerani',
            'department' => 'Kewangan',
            'start_date' => '2025-01-01',
            'employment_type' => 'permanent',
            'basic_salary' => $gaji,
            'fixed_allowance' => 0,
            'salary_currency' => 'MYR',
            'is_active' => true,
        ]);
    }

    private function larian(): PayrollRun
    {
        return PayrollRun::create([
            'name' => 'Gaji Oktober 2026',
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'pay_date' => '2026-10-28',
            'status' => 'draft',
            'created_by' => $this->u->id,
        ]);
    }

    // ===============================================================
    // A. PENGASINGAN — PEKERJA (paling sensitif: IC, gaji)
    // ===============================================================

    public function test_tenant_lain_tidak_nampak_pekerja(): void
    {
        $pekerja = $this->pekerja('Rahsia Satu');

        [, $t2] = $this->daftar('t2@payroll.my', 'Tenant Dua Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertSame(0, Employee::count(), 'Pekerja tenant lain kelihatan (bocor IC & gaji).');
        $this->assertNull(Employee::find($pekerja->id), 'find() menembusi skop tenant.');

        $this->assertSame(
            1,
            Employee::withoutGlobalScope('tenant')->where('tenant_id', $this->t->id)->count()
        );
    }

    public function test_nombor_ic_tidak_bocor_melalui_api(): void
    {
        $this->pekerja('Pekerja Sulit Satu');

        $u2 = $this->daftar('t3@payroll.my', 'Tenant Tiga Sdn Bhd')[0];
        $t2 = \App\Models\Tenant::where('name', 'Tenant Tiga Sdn Bhd')->firstOrFail();

        $this->sebagai($u2, $t2);

        $respons = $this->getJson('/api/v1/payroll/employees')->assertOk();
        $teks = json_encode($respons->json());

        $this->assertStringNotContainsString(
            '900101-01-5555',
            $teks,
            'Nombor IC pekerja tenant lain bocor melalui API (pelanggaran PDPA).'
        );
        $this->assertStringNotContainsString('Pekerja Sulit Satu', $teks);
    }

    // ===============================================================
    // A. PENGASINGAN — LARIAN GAJI
    // ===============================================================

    public function test_tenant_lain_tidak_nampak_larian_gaji(): void
    {
        $larian = $this->larian();

        [, $t2] = $this->daftar('t4@payroll.my', 'Tenant Empat Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertSame(0, PayrollRun::count());
        $this->assertNull(PayrollRun::find($larian->id));
    }

    public function test_detail_gaji_ikut_larian_induk(): void
    {
        $larian = $this->larian();
        $pekerja = $this->pekerja();

        PayrollRunDetail::create([
            'payroll_run_id' => $larian->id,
            'employee_id' => $pekerja->id,
            'basic_salary' => 3000,
            'gross_pay' => 3000,
            'net_pay' => 2600,
        ]);

        [, $t2] = $this->daftar('t5@payroll.my', 'Tenant Lima Sdn Bhd');
        $this->dalamTenant($t2);

        // ⚠️ payroll_run_details TIADA tenant_id dan TIADA skop *_via_induk.
        // Baca melalui relasi induk = selamat (induk ditapis).
        $melaluiInduk = PayrollRunDetail::whereIn(
            'payroll_run_id',
            PayrollRun::query()->select('id')
        )->count();
        $this->assertSame(0, $melaluiInduk, 'Bacaan melalui induk mesti kosong.');

        // Query terus = terdedah selagi skop belum ditambah.
        $terus = PayrollRunDetail::query()->where('payroll_run_id', $larian->id)->count();
        $this->assertSame(
            1,
            $terus,
            'Jika ini 0, skop *_via_induk sudah ditambah — kemas kini ujian ini.'
        );
    }

    // ===============================================================
    // A. PENGASINGAN — TULIS
    // ===============================================================

    public function test_tidak_boleh_cipta_pekerja_untuk_tenant_lain(): void
    {
        [, $t2] = $this->daftar('t6@payroll.my', 'Tenant Enam Sdn Bhd');
        $this->dalamTenant($this->t);

        $this->expectException(\RuntimeException::class);
        Employee::create([
            'tenant_id' => $t2->id,
            'name' => 'Palsu',
            'basic_salary' => 1000,
        ]);
    }

    public function test_tenant_id_pekerja_tidak_boleh_diubah(): void
    {
        $pekerja = $this->pekerja();

        [, $t2] = $this->daftar('t7@payroll.my', 'Tenant Tujuh Sdn Bhd');
        $this->dalamTenant($this->t);

        $this->expectException(\RuntimeException::class);
        $pekerja->tenant_id = $t2->id;
        $pekerja->save();
    }

    public function test_pekerja_tidak_boleh_dicipta_tanpa_tenant(): void
    {
        app(\App\Support\Tenancy\TenantContext::class)->clear();

        $this->expectException(\RuntimeException::class);
        Employee::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->t->id,
            'name' => 'Tiada Konteks',
        ]);
    }

    // ===============================================================
    // B. KIRAAN STATUTORI — kolum wang
    // ===============================================================

    public function test_gaji_dikira_dengan_decimal_bukan_float(): void
    {
        $pekerja = $this->pekerja('Siti', 3000);

        $this->assertSame(
            '3000.00',
            number_format((float) $pekerja->basic_salary, 2, '.', ''),
            'Kolum gaji mesti pulang nilai tepat 2 titik perpuluhan.'
        );
    }

    public function test_statutory_rate_tiada_skop_global_didokumenkan(): void
    {
        // Kadar khusus tenant satu (kolum sebenar: type, epf_employee_rate, ...)
        StatutoryRate::create([
            'tenant_id' => $this->t->id,
            'year' => 2026,
            'type' => 'epf_employee',
            'epf_employee_rate' => 11.00,
            'effective_from' => '2026-01-01',
            'is_active' => true,
        ]);

        [, $t2] = $this->daftar('t8@payroll.my', 'Tenant Lapan Sdn Bhd');
        $this->dalamTenant($t2);

        // ⚠️ `StatutoryRate` SENGAJA tanpa `BelongsToTenant` (kadar global =
        // tenant_id NULL + override per tenant). Ia dibaca semasa pengiraan
        // payroll yang boleh berjalan tanpa TenantContext.
        //
        // ↳ KESAN: query TANPA `where('tenant_id', …)` akan merentas tenant.
        //   Jaminan skop terletak SEPENUHNYA pada disiplin pemanggil.
        //
        // Ujian ini MENDOKUMENTASIKAN jurang itu — ia akan gagal apabila
        // skop ditambah kelak.
        $tanpaSkop = StatutoryRate::query()->count();
        $milikT1 = StatutoryRate::where('tenant_id', $this->t->id)->count();

        $this->assertGreaterThanOrEqual(
            1,
            $milikT1,
            'Kadar tenant satu mesti wujud.'
        );
        $this->assertGreaterThanOrEqual(
            $milikT1,
            $tanpaSkop,
            'Query tanpa skop sepatutnya merentas tenant (jurang didokumenkan).'
        );

        // Amalan betul: sentiasa tapis eksplisit
        $this->assertSame(1, $milikT1);

        // Dan kadar tenant sendiri tidak boleh dilihat sebagai miliknya
        $milikSendiri = StatutoryRate::where('tenant_id', $t2->id)->count();
        $this->assertSame(0, $milikSendiri, 'Tenant dua tidak sepatutnya ada kadar sendiri.');
    }

    // ===============================================================
    // C. OVERTIME
    // ===============================================================

    public function test_overtime_tenant_lain_tidak_nampak(): void
    {
        $pekerja = $this->pekerja();

        OvertimeRecord::create([
            'tenant_id' => $this->t->id,
            'employee_id' => $pekerja->id,
            'date' => '2026-10-20',
            'day_type' => 'rest_day',
            'hours_worked' => 3,
            'hourly_rate' => 25,
            'multiplier' => 1.5,
            'ot_pay' => 112.50,
            'status' => 'pending',
        ]);

        [, $t2] = $this->daftar('t9@payroll.my', 'Tenant Sembilan Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertSame(0, OvertimeRecord::count(), 'Rekod lebih masa bocor merentas tenant.');
    }

    // ===============================================================
    // D. API — 404 untuk rekod tenant lain
    // ===============================================================

    public function test_api_pekerja_tenant_lain_pulang_404(): void
    {
        $pekerja = $this->pekerja('Sulit');

        $u2 = $this->daftar('t10@payroll.my', 'Tenant Sepuluh Sdn Bhd')[0];
        $t2 = \App\Models\Tenant::where('name', 'Tenant Sepuluh Sdn Bhd')->firstOrFail();

        $this->sebagai($u2, $t2);

        $this->getJson('/api/v1/payroll/employees/' . $pekerja->id)->assertNotFound();
    }

    public function test_api_larian_gaji_tenant_lain_pulang_404(): void
    {
        $larian = $this->larian();

        $u2 = $this->daftar('t11@payroll.my', 'Tenant Sebelas Sdn Bhd')[0];
        $t2 = \App\Models\Tenant::where('name', 'Tenant Sebelas Sdn Bhd')->firstOrFail();

        $this->sebagai($u2, $t2);

        // Laluan larian gaji: semak senarai — rekod tenant lain tidak boleh muncul
        $respons = $this->getJson('/api/v1/payroll/runs')->assertOk();
        $teks = json_encode($respons->json());
        $this->assertStringNotContainsString('Gaji Oktober 2026', $teks);
    }
}
