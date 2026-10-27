<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Payroll\Models\Employee;
use App\Modules\Payroll\Models\PayrollRun;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — Payroll (pekerja + larian gaji).
 *
 * Ditambah 28 Okt 2026 (T-111). Kebocoran di sini mendedahkan No. KP, gaji
 * pokok dan nombor akaun bank pekerja pesaing — kegagalan PDPA paling serius.
 * Sebelum ini modul dikira diliputi hanya melalui import dalam fail bergabung.
 */
class PayrollIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Payroll';

    protected array $model = [
        Employee::class,
        PayrollRun::class,
    ];

    protected ?string $laluanApi = '/api/v1/payroll/employees';

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === Employee::class) {
            return [
                'name' => 'Pekerja Rahsia Tenant Satu ' . $n,
                'ic_no' => '9001011456' . str_pad((string) $n, 2, '0', STR_PAD_LEFT),
                'position' => 'Jurutera',
                'start_date' => now()->subYear()->toDateString(),
                'employment_type' => 'permanent',
                'basic_salary' => 5000,
                'is_active' => true,
            ];
        }

        return [
            'name' => 'Larian Gaji Rahsia Tenant Satu ' . $n,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'pay_date' => now()->endOfMonth()->toDateString(),
            'status' => 'draft',
        ];
    }
}
