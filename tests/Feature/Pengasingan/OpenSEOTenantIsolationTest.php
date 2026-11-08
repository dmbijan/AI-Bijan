<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\OpenSEO\Models\SeoBacklink;
use App\Modules\OpenSEO\Models\SeoKeywordResearch;
use App\Modules\OpenSEO\Models\SeoProject;
use App\Modules\OpenSEO\Models\SerpSnapshot;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul OpenSEO (intel Competitive & SEO).
 *
 * Ditambah 8 Nov 2026. Projek SEO mendedahkan domain, kata kunci dan
 * backlink pesaing — maklumat strategik perniagaan.
 */
class OpenSEOTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'OpenSEO';

    protected array $model = [
        SeoProject::class,
        SeoKeywordResearch::class,
        SerpSnapshot::class,
        SeoBacklink::class,
    ];

    protected ?string $laluanApi = '/api/v1/open-seo/projects';

    protected ?string $namaRahsia = 'Projek SEO Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === SeoProject::class) {
            return [
                'name' => 'Projek SEO Rahsia Tenant Satu '.$n,
                'slug' => 'projek-seo-rahsia-'.$n,
                'domain' => 'rahasia-'.$n.'.test',
                'base_url' => 'https://rahasia-'.$n.'.test',
                'status' => SeoProject::STATUS_ACTIVE,
            ];
        }

        $projek = SeoProject::create([
            'name' => 'Projek SEO Anak '.$n,
            'slug' => 'projek-seo-anak-'.$n,
            'domain' => 'anak-'.$n.'.test',
        ]);

        if ($this->rekodSemasa === SeoKeywordResearch::class) {
            return [
                'seo_project_id' => $projek->id,
                'keyword' => 'kata kunci rahsia '.$n,
                'intent' => 'commercial',
                'search_volume' => 500,
                'difficulty' => 30,
            ];
        }

        if ($this->rekodSemasa === SerpSnapshot::class) {
            return [
                'seo_project_id' => $projek->id,
                'keyword' => 'kata kunci rahsia '.$n,
                'position' => 3,
                'url' => 'https://rahasia-'.$n.'.test/page',
                'title' => 'Tajuk Rahsia Tenant Satu '.$n,
            ];
        }

        return [
            'seo_project_id' => $projek->id,
            'source_url' => 'https://sumber-rahsia-'.$n.'.test/post',
            'source_domain' => 'sumber-rahsia-'.$n.'.test',
            'target_url' => 'https://rahasia-'.$n.'.test',
            'anchor_text' => 'Anchor Rahsia Tenant Satu '.$n,
            'domain_authority' => 45,
        ];
    }
}
