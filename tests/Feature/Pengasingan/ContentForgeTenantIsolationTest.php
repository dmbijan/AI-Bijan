<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\ContentForge\Models\ContentDocument;
use App\Modules\ContentForge\Models\ContentSite;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul ContentForge (kandungan & SEO, asal: SEO Writing AI).
 *
 * Ditambah 9 Nov 2026. Dokumen boleh mengandungi kandungan kempen yang
 * belum diterbitkan; laman menyimpan kata laluan WordPress.
 */
class ContentForgeTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'ContentForge';

    protected array $model = [
        ContentDocument::class,
        ContentSite::class,
    ];

    protected ?string $laluanApi = '/api/v1/content-forge/documents';

    protected ?string $namaRahsia = 'Dokumen Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === ContentDocument::class) {
            return [
                'uuid' => 'cf-uuid-'.$n,
                'title' => 'Dokumen Rahsia Tenant Satu '.$n,
                'slug' => 'dokumen-rahsia-'.$n,
                'type' => ContentDocument::TYPE_ARTICLE,
                'status' => ContentDocument::STATUS_DRAFT,
                'body_markdown' => 'Kandungan Rahsia Tenant Satu '.$n,
                'keywords' => ['kata kunci rahsia '.$n],
                'target_word_count' => 1200,
            ];
        }

        return [
            'name' => 'Laman Rahsia Tenant Satu '.$n,
            'url' => 'https://rahasia-'.$n.'.test',
            'username' => 'admin'.$n,
            'app_password' => 'kata-laluan-rahasia-'.$n,
            'status' => ContentSite::STATUS_CONNECTED,
        ];
    }
}
