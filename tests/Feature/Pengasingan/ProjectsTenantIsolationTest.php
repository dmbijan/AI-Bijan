<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Projects\Models\Project;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Projects (projek).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Projek menyimpan pelanggan, bajet dan kos
 * sebenar — kebocoran mendedahkan margin dan harga tawaran pesaing.
 */
class ProjectsTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Projects';

    protected array $model = [
        Project::class,
    ];

    /**
     * Laluan senarai projek ialah `GET api/v1/pm` (bukan `/pm/projects`) —
     * lihat TODO T-01: modul didaftar oleh `ProjectsServiceProvider` sahaja.
     */
    protected ?string $laluanApi = '/api/v1/pm';

    protected ?string $namaRahsia = 'Projek Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        return [
            'code' => 'PRJ-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'name' => 'Projek Rahsia Tenant Satu ' . $n,
            'status' => 'aktif',
            'budget' => 50000,
            'owner_id' => $this->u->id,
        ];
    }
}
