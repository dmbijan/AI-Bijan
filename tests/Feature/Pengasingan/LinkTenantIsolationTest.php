<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Link\Models\DigitalCard;
use App\Modules\Link\Models\LinkPage;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Link (halaman pautan, kad digital).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Kad digital membawa e-mel, telefon dan
 * alamat pemegang — data peribadi PDPA.
 */
class LinkTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Link';

    protected array $model = [
        LinkPage::class,
        DigitalCard::class,
    ];

    protected ?string $laluanApi = '/api/v1/link/pages';

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === DigitalCard::class) {
            return [
                'title' => 'Kad Rahsia Tenant Satu ' . $n,
                'short_code' => 'kad-' . $n,
                'holder_name' => 'Rahsia Tenant Satu ' . $n,
                'email' => 'rahsia' . $n . '@kad-tenant-satu.test',
            ];
        }

        return [
            'title' => 'Pautan Rahsia Tenant Satu ' . $n,
            'slug' => 'pautan-rahsia-' . $n,
            'short_code' => 'pautan-' . $n,
            'type' => 'page',
        ];
    }
}
