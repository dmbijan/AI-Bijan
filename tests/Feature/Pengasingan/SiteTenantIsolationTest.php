<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Site\Models\Domain;
use App\Modules\Site\Models\Form;
use App\Modules\Site\Models\Page;
use App\Modules\Site\Models\Site;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Site (laman web, domain, halaman, borang).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Site ialah wajah awam setiap tenant: pilihan
 * tema, kandungan halaman dan domain. Kebocoran bermakna tenant lain boleh
 * menyunting laman pelanggan pesaing. Modul Site juga antara yang terjejas
 * oleh pepijat `$request->tenant_id` (182 rujukan, 27 controller).
 */
class SiteTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Site';

    protected array $model = [
        Site::class,
        Domain::class,
        Page::class,
        Form::class,
    ];

    protected ?string $laluanApi = '/api/v1/site/sites';

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === Site::class) {
            return [
                'name' => 'Laman Rahsia Tenant Satu ' . $n,
                'slug' => 'laman-rahsia-' . $n,
            ];
        }

        $site = Site::create([
            'name' => 'Laman Rahsia Tenant Satu ' . $n,
            'slug' => 'laman-rahsia-' . $n,
        ]);

        if ($this->rekodSemasa === Domain::class) {
            return [
                'site_id' => $site->id,
                'domain' => 'rahsia' . $n . '.tenant-satu.test',
            ];
        }

        if ($this->rekodSemasa === Page::class) {
            return [
                'site_id' => $site->id,
                'title' => 'Halaman Rahsia Tenant Satu ' . $n,
                'slug' => 'halaman-rahsia-' . $n,
            ];
        }

        if ($this->rekodSemasa === Form::class) {
            return [
                'site_id' => $site->id,
                'name' => 'Borang Rahsia Tenant Satu ' . $n,
                'title' => 'Borang Rahsia Tenant Satu ' . $n,
                'fields' => json_encode([['name' => 'email', 'type' => 'email']]),
            ];
        }

        return [
            'name' => 'Laman Rahsia Tenant Satu ' . $n,
            'slug' => 'laman-rahsia-' . $n,
        ];
    }
}
