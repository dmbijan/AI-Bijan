<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\POS\Models\CashShift;
use App\Modules\POS\Models\POSRegister;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul POS (jualan, daftar, syif tunai).
 *
 * Ditambah 27 Okt 2026 (P1-1d). POS menyimpan jualan harian, jumlah tunai
 * dan syif juruwang — kebocoran bermakna tenant lain boleh membaca hasil
 * jualan pesaing.
 */
class POSTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'POS';

    protected array $model = [
        POSRegister::class,
        CashShift::class,
    ];

    protected ?string $laluanApi = '/api/v1/pos/registers';

    protected ?string $namaRahsia = null;

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        // CashShift perlukan register_id — cipta daftar dalam konteks sama
        if ($this->rekodSemasa === CashShift::class) {
            return [
                'register_id' => POSRegister::create([
                    'name' => 'Daftar Ujian ' . $n,
                    'code' => 'REG-' . $n,
                    'created_by' => $this->u->id,
                ])->id,
                'status' => 'open',
                'opening_cash' => 100,
                'opened_by' => $this->u->id,
                'shift_date' => '2026-10-27',
                'opened_at' => now(),
            ];
        }

        return [
            'name' => 'Daftar Rahsia Tenant Satu ' . $n,
            'code' => 'POS-REG-' . $n,
            'created_by' => $this->u->id,
        ];
    }
}
