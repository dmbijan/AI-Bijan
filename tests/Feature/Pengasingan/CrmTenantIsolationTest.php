<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\CRM\Models\Customer;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul CRM (pelanggan).
 *
 * Ditambah 27 Okt 2026 (P1-1d). CRM menyimpan nama, e-mel, telefon, alamat
 * dan nombor cukai pelanggan — data peribadi di bawah PDPA 2010.
 */
class CrmTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'CRM';

    protected array $model = [
        Customer::class,
    ];

    protected ?string $laluanApi = '/api/v1/crm/customers';

    protected ?string $namaRahsia = 'Pelanggan Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        return [
            'customer_code' => 'CUS-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'customer_type' => 'business',
            'name' => 'Pelanggan Rahsia Tenant Satu ' . $n,
            'contact_email' => 'rahsia' . $n . '@tenant-satu.test',
            'status' => 'active',
        ];
    }
}
