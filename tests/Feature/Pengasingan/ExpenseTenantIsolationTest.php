<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Expense\Models\Expense;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Expense (perbelanjaan).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Perbelanjaan ialah data wang: pembekal,
 * invois vendor dan jumlah — kebocoran mendedahkan struktur kos tenant lain.
 */
class ExpenseTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Expense';

    protected array $model = [
        Expense::class,
    ];

    protected ?string $laluanApi = '/api/v1/expenses';

    protected ?string $namaRahsia = 'Pembekal Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        return [
            'expense_no' => 'EXP-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT),
            'vendor_name' => 'Pembekal Rahsia Tenant Satu ' . $n,
            'expense_date' => '2026-10-20',
            'status' => 'draft',
            'payment_status' => 'unpaid',
            'created_by_user_id' => $this->u->id,
        ];
    }
}
