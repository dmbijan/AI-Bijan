<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Marketplace\Models\Vendor;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Marketplace (vendor).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Vendor membawa kadar komisen dan hasil —
 * kebocoran mendedahkan terma komersial dan senarai penjual pesaing.
 */
class MarketplaceTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Marketplace';

    protected array $model = [
        Vendor::class,
    ];

    protected ?string $laluanApi = '/api/v1/marketplace/vendors';

    protected ?string $namaRahsia = 'Vendor Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        return [
            'name' => 'Vendor Rahsia Tenant Satu ' . $n,
            'slug' => 'vendor-rahsia-' . $n,
            'contact_email' => 'rahsia' . $n . '@vendor-tenant-satu.test',
            'is_active' => true,
        ];
    }
}
