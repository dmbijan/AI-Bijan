<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\MediaStudio\Models\MediaAsset;
use App\Modules\Prospecting\Models\Prospect;
use App\Modules\Prospecting\Models\ProspectList;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Prospecting (prospek) + MediaStudio (aset media).
 *
 * Ditambah 27 Okt 2026 (P1-1d).
 *
 * • Prospecting: senarai prospek mengandungi e-mel/telefon yang dikumpul —
 *   kebocoran ialah risiko PDPA paling serius dalam modul ini (data pihak
 *   ketiga yang tidak memberi persetujuan kepada tenant lain).
 * • MediaStudio: aset media peribadi tenant.
 */
class ProspectingMediaIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Prospecting';

    protected array $model = [
        Prospect::class,
        ProspectList::class,
        MediaAsset::class,
    ];

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected string $sebabTiadaApi = 'laluan prospek memerlukan peranan khas';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === ProspectList::class) {
            return [
                'name' => 'Senarai Rahsia Tenant Satu ' . $n,
            ];
        }

        if ($this->rekodSemasa === MediaAsset::class) {
            return [
                'kind' => 'image',
                'storage_disk' => 'media',
                'storage_path' => 'aset/rahsia-' . $n . '.jpg',
                'original_name' => 'rahsia-' . $n . '.jpg',
                'mime' => 'image/jpeg',
            ];
        }

        return [
            'first_name' => 'Rahsia',
            'last_name' => 'Tenant Satu ' . $n,
            'email' => 'rahsia' . $n . '@prospek-tenant-satu.test',
            'company' => 'Syarikat Rahsia ' . $n,
        ];
    }
}
