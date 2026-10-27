<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\HRMS\Models\Department;
use App\Modules\HRMS\Models\Designation;
use App\Modules\HRMS\Models\Employee;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul HRMS (pekerja, jabatan).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Nombor IC, tarikh lahir, alamat dan status
 * perkahwinan ialah data peribadi sensitif di bawah PDPA 2010 — kebocoran
 * antara tenant ialah insiden pematuhan, bukan sekadar pepijat.
 *
 * `HrmsTenantIsolationTest` (27 Okt) menguji aliran melalui service; ujian
 * ini menguji 4 lapisan trait secara langsung pada model.
 */
class HrmsIsolationTraitTest extends UjianPengasingan
{
    protected string $modul = 'HRMS';

    protected array $model = [
        Department::class,
        Designation::class,
        Employee::class,
    ];

    protected ?string $laluanApi = '/api/v1/hrms/employees';

    protected ?string $namaRahsia = 'Rahsia';

    protected function isi(): array
    {
        // ⚠️ JANGAN guna `static $n` — ia berkongsi keadaan merentas ujian
        // (setiap ujian RefreshDatabase, tetapi static kekal dalam proses).
        // Kod yang berulang mencetuskan UNIQUE(tenant_id, code) dan ujian
        // berikutnya gagal secara palsu.
        $n = $this->kiraRekod();

        if ($this->rekodSemasa === Department::class) {
            return [
                'name' => 'Jabatan Ujian ' . $n,
                'code' => 'DEPT-' . $n,
            ];
        }

        $jabatan = Department::create([
            'name' => 'Jabatan Ujian ' . $n,
            'code' => 'DEPT-' . $n,
        ]);

        if ($this->rekodSemasa === Designation::class) {
            return [
                'name' => 'Jawatan Ujian ' . $n,
                'code' => 'DSG-' . $n,
                'department_id' => $jabatan->id,
            ];
        }

        return [
            'employee_no' => 'EMP-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'first_name' => $this->namaRahsia,
            'last_name' => 'Tenant Satu ' . $n,
            'email' => 'rahsia' . $n . '@hrms-tenant-satu.test',
            'ic_number' => '900101-01-5' . str_pad((string) $n, 3, '0', STR_PAD_LEFT),
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'nationality' => 'Malaysian',
            'country' => 'Malaysia',
            // ⚠️ HRMS guna enum BERBEZA daripada Payroll:
            //   hrms_employees  : full_time|part_time|contract|freelance|intern
            //   employees (Payroll): permanent|contract|part_time|casual
            // Jangan kongsi fixture antara dua modul.
            'employment_type' => 'full_time',
            'date_joined' => '2025-01-01',
            'status' => 'active',
        ];
    }

    /** Nombor unik dalam ujian ini — dikira dari DB, bukan static. */
    private function kiraRekod(): int
    {
        return (int) (\Illuminate\Support\Facades\DB::table('hrms_departments')->count()) + 1;
    }
}
