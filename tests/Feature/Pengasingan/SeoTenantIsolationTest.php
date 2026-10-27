<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Seo\Models\SeoKeyword;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul SEO (kata kunci, kedudukan).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Data kata kunci dan kedudukan ialah hasil
 * penyelidikan pasaran — kebocoran bermakna strategi SEO tenant lain terbaca.
 */
class SeoTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Seo';

    protected array $model = [
        SeoKeyword::class,
    ];

    protected ?string $laluanApi = '/api/v1/seo/keywords';

    protected ?string $namaRahsia = 'kata-kunci-rahsia-tenant-satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        return [
            'keyword' => 'kata-kunci-rahsia-tenant-satu-' . $n,
            'intent' => 'informational',
            'source' => 'manual',
            'is_tracked' => true,
        ];
    }
}
