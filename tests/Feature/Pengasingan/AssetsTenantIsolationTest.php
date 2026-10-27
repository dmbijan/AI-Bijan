<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Assets\Models\Asset;
use App\Modules\Assets\Models\AssetCategory;
use App\Modules\Assets\Models\MaintenanceRequest;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Assets (aset, penyelenggaraan).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Aset mencatat harga belian, nilai buku dan
 * lokasi — kebocoran mendedahkan struktur kos dan susun atur operasi.
 *
 * ⚠️ Jadual `assets` diwarisi dengan kolum WAJIB yang tidak ada dalam
 * `$fillable` model (`tag`, `category_id`, `purchase_date`,
 * `depreciation_start`, `cost`, `useful_life_months`, `method`). Model tidak
 * boleh mencipta aset tanpa mengisi kolum itu — ini juga bermakna
 * `AssetController::store()` akan gagal melainkan ia menambahnya.
 */
class AssetsTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Assets';

    protected array $model = [
        Asset::class,
        MaintenanceRequest::class,
    ];

    /** `assets` ada kolum warisan WAJIB di luar `$fillable` — lihat nota kelas. */
    protected bool $gunaForce = true;

    protected ?string $laluanApi = '/api/v1/assets';

    protected ?string $namaRahsia = 'Aset Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        $kategori = AssetCategory::forceCreate([
            'name' => 'Kategori Ujian ' . $n,
            'useful_life_months' => 60,
        ]);

        $aset = [
            'tag' => 'AST-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'name' => 'Aset Rahsia Tenant Satu ' . $n,
            'category_id' => $kategori->id,
            'purchase_date' => '2026-01-15',
            'depreciation_start' => '2026-01-15',
            'cost' => 1000,
            'useful_life_months' => 60,
            'method' => 'garis_lurus',
            'status' => 'aktif',
        ];

        if ($this->rekodSemasa === MaintenanceRequest::class) {
            return [
                'asset_id' => Asset::forceCreate($aset)->id,
                'type' => 'corrective',
                'priority' => 'medium',
                'status' => 'open',
                'title' => 'Penyelenggaraan Rahsia ' . $n,
            ];
        }

        return $aset;
    }
}
