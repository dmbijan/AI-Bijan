<?php

namespace Tests\Feature;

use App\Modules\HRMS\Models\Attendance;
use App\Modules\HRMS\Models\Department;
use App\Modules\HRMS\Models\Employee;
use App\Modules\HRMS\Models\LeaveRequest;
use App\Modules\HRMS\Models\LeaveType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Pengasingan tenant — modul HRMS.
 *
 * Ditambah 27 Okt 2026 (P1-1b). HRMS menyimpan nombor IC, gaji (via Payroll),
 * rekod kehadiran, cuti dan data biometrik — semuanya data peribadi di bawah
 * PDPA 2010.
 *
 * ⚠️ `hrms_attendance`, `hrms_leave_requests`, `hrms_leave_allocations`,
 * `hrms_employee_documents`, `hrms_employee_shifts`, `hrms_overtime_requests`
 * dan `hrms_leave_request_history` ialah JADUAL ANAK (tiada `tenant_id`,
 * betul ikut konvensyen). Ia hanya selamat jika diakses melalui relasi
 * `employee`, atau jika pemanggil menulis `whereHas('employee', ...)`.
 *
 * Ujian ini mengukur sama ada andaian itu benar dalam amalan.
 */
class HrmsTenantIsolationTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@hrms.my', 'HRMS Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    private function pekerja(string $nama = 'Ali', ?Department $jabatan = null): Employee
    {
        static $n = 0;
        $n++;

        return Employee::create([
            'employee_no' => 'EMP' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'first_name' => $nama,
            'last_name' => 'bin Abu',
            'email' => strtolower($nama) . $n . '@hrms.test',
            'ic_number' => '900101-01-5555',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'department_id' => $jabatan?->id,
            'date_joined' => '2025-01-01',   // kolum sebenar: date_joined (bukan hire_date)
            'status' => 'active',
        ]);
    }

    // ===============================================================
    // A. PENGASINGAN — PEKERJA & JABATAN
    // ===============================================================

    public function test_tenant_lain_tidak_nampak_pekerja(): void
    {
        $pekerja = $this->pekerja('Rahsia');

        [, $t2] = $this->daftar('t2@hrms.my', 'Tenant Dua Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertSame(0, Employee::count(), 'Pekerja tenant lain kelihatan (bocor IC).');
        $this->assertNull(Employee::find($pekerja->id));

        $this->assertSame(
            1,
            Employee::withoutGlobalScope('tenant')->where('tenant_id', $this->t->id)->count()
        );
    }

    public function test_tenant_lain_tidak_nampak_jabatan(): void
    {
        Department::create(['code' => 'FIN', 'name' => 'Kewangan']);

        [, $t2] = $this->daftar('t3@hrms.my', 'Tenant Tiga Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertSame(0, Department::count());
    }

    public function test_nombor_ic_tidak_bocor_melalui_api(): void
    {
        $this->pekerja('Sulit');

        $u2 = $this->daftar('t4@hrms.my', 'Tenant Empat Sdn Bhd')[0];
        $t2 = \App\Models\Tenant::where('name', 'Tenant Empat Sdn Bhd')->firstOrFail();

        $this->sebagai($u2, $t2);

        $teks = json_encode($this->getJson('/api/v1/hrms/employees')->json());

        $this->assertStringNotContainsString(
            '900101-01-5555',
            $teks,
            'Nombor IC pekerja tenant lain bocor (pelanggaran PDPA).'
        );
    }

    // ===============================================================
    // A. PENGASINGAN — TULIS
    // ===============================================================

    public function test_tidak_boleh_cipta_pekerja_untuk_tenant_lain(): void
    {
        [, $t2] = $this->daftar('t5@hrms.my', 'Tenant Lima Sdn Bhd');
        $this->dalamTenant($this->t);

        $this->expectException(\RuntimeException::class);
        Employee::create([
            'tenant_id' => $t2->id,
            'employee_no' => 'HACK01',
            'first_name' => 'Palsu',
            'email' => 'palsu@hrms.test',
        ]);
    }

    public function test_tenant_id_pekerja_tidak_boleh_diubah(): void
    {
        $pekerja = $this->pekerja();

        [, $t2] = $this->daftar('t6@hrms.my', 'Tenant Enam Sdn Bhd');
        $this->dalamTenant($this->t);

        $this->expectException(\RuntimeException::class);
        $pekerja->tenant_id = $t2->id;
        $pekerja->save();
    }

    // ===============================================================
    // B. JADUAL ANAK — KEHADIRAN & CUTI
    // ===============================================================

    public function test_kehadiran_tiada_skop_sendiri_didokumenkan(): void
    {
        $pekerja = $this->pekerja();

        Attendance::create([
            'employee_id' => $pekerja->id,
            'date' => '2026-10-20',
            'check_in_time' => '09:00:00',
            'check_out_time' => '18:00:00',
            'total_hours' => 8,
            'status' => 'present',
        ]);

        [, $t2] = $this->daftar('t7@hrms.my', 'Tenant Tujuh Sdn Bhd');
        $this->dalamTenant($t2);

        // Amalan betul: tapis melalui induk
        $melaluiInduk = Attendance::whereHas('employee')->count();
        $this->assertSame(0, $melaluiInduk, 'whereHas(employee) mesti menapis dengan betul.');

        // Query terus: terdedah selagi skop *_via_induk belum ditambah.
        $terus = Attendance::query()->where('employee_id', $pekerja->id)->count();
        $this->assertSame(
            1,
            $terus,
            'Jika ini 0, skop *_via_induk sudah ditambah — kemas kini ujian ini.'
        );
    }

    public function test_cuti_tiada_skop_sendiri_didokumenkan(): void
    {
        $pekerja = $this->pekerja();
        $jenis = LeaveType::create(['code' => 'ANNUAL', 'name' => 'Cuti Tahunan']);

        LeaveRequest::create([
            'employee_id' => $pekerja->id,
            'leave_type_id' => $jenis->id,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-03',
            'days_requested' => 3,
            'status' => 'pending',
        ]);

        [, $t2] = $this->daftar('t8@hrms.my', 'Tenant Lapan Sdn Bhd');
        $this->dalamTenant($t2);

        $melaluiInduk = LeaveRequest::whereHas('employee')->count();
        $this->assertSame(0, $melaluiInduk, 'whereHas(employee) mesti kosong untuk tenant lain.');

        $terus = LeaveRequest::query()->where('employee_id', $pekerja->id)->count();
        $this->assertSame(
            1,
            $terus,
            'Jika ini 0, skop *_via_induk sudah ditambah — kemas kini ujian ini.'
        );
    }

    // ===============================================================
    // C. API
    // ===============================================================

    public function test_api_pekerja_tenant_lain_pulang_404(): void
    {
        $pekerja = $this->pekerja('Sulit');

        $u2 = $this->daftar('t9@hrms.my', 'Tenant Sembilan Sdn Bhd')[0];
        $t2 = \App\Models\Tenant::where('name', 'Tenant Sembilan Sdn Bhd')->firstOrFail();

        $this->sebagai($u2, $t2);

        $this->getJson('/api/v1/hrms/employees/' . $pekerja->id)->assertNotFound();
    }

    // ===============================================================
    // D. BIOMETRIK — pagar consent (PDPA)
    // ===============================================================

    public function test_tetapan_biometrik_tenant_lain_tidak_nampak(): void
    {
        [, $t2] = $this->daftar('t10@hrms.my', 'Tenant Sepuluh Sdn Bhd');
        $this->dalamTenant($t2);

        // Tiada tetapan biometrik untuk tenant dua — dan tidak boleh melihat
        // tetapan tenant satu melalui API.
        $u2 = \App\Models\User::where('email', 't10@hrms.my')->firstOrFail();
        $this->sebagai($u2, $t2);

        $respons = $this->getJson('/api/v1/hrms/biometric/consent-status');
        // 404 jika belum disediakan, atau 200 dengan enabled=false — bukan 500.
        $this->assertContains(
            $respons->status(),
            [200, 404],
            'Endpoint biometrik mesti pulang 200/404, bukan ralat pelayan.'
        );
    }
}
