<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\SCM\Models\PurchaseOrder;
use App\Modules\SCM\Models\Supplier;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul SCM (pembekal, pesanan belian).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Kebocoran di sini mendedahkan harga belian
 * dan butiran bank pembekal — maklumat komersial paling sensitif selepas
 * kewangan.
 */
class ScmTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'SCM';

    protected array $model = [
        Supplier::class,
    ];

    /**
     * Tiada ujian API: laluan `scm/suppliers` tidak didaftarkan oleh
     * `SCMServiceProvider` (hanya `purchase-orders` didaftarkan), jadi tiada
     * endpoint senarai pembekal untuk diuji. Skop data tetap dijamin oleh
     * trait `BelongsToTenant` dan diuji di peringkat model di sini.
     *
     * Nota: `PurchaseOrder` TIDAK digunakan sebagai model ujian kerana ia
     * mempunyai global scope `status = approved` yang akan menyembunyikan
     * rekod baharu dan menjadikan ujian "baca rekod sendiri" mengelirukan.
     */
    protected string $sebabTiadaApi = 'laluan suppliers belum didaftarkan';

    protected ?string $namaRahsia = 'Pembekal Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        return [
            'supplier_code' => 'SUP-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'company_name' => 'Pembekal Rahsia Tenant Satu ' . $n,
            'contact_email' => 'rahsia' . $n . '@pembekal.test',
            'status' => 'active',
        ];
    }
}
