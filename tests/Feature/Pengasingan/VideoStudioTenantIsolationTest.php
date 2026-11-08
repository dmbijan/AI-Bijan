<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\VideoStudio\Models\VideoBrandKit;
use App\Modules\VideoStudio\Models\VideoProject;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul VideoStudio (orkestrasi skill video AI BIJAN).
 *
 * Ditambah 8 Nov 2026. Projek video menyimpan skrip, audio dan aset
 * pelanggan; brand kit mendedahkan identiti jenama syarikat.
 */
class VideoStudioTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'VideoStudio';

    protected array $model = [
        VideoBrandKit::class,
        VideoProject::class,
    ];

    protected ?string $laluanApi = '/api/v1/video-studio/projects';

    protected ?string $namaRahsia = 'Projek Video Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === VideoBrandKit::class) {
            return [
                'name' => 'Brand Kit Rahsia Tenant Satu '.$n,
                'slug' => 'brand-kit-rahsia-'.$n,
                'brand' => 'Jenama Rahsia',
                'handle' => '@rahasia'.$n,
                'cta' => 'Ikut kami',
                'color_primary' => '#1B2A4A',
                'color_accent' => '#FF8A00',
                'color_background' => '#FFF8EC',
                'font' => 'Plus Jakarta Sans',
            ];
        }

        return [
            'uuid' => 'video-uuid-'.$n,
            'name' => 'Projek Video Rahsia Tenant Satu '.$n,
            'slug' => 'video-rahsia-'.$n,
            'skill' => VideoProject::SKILL_MOGRAPH,
            'status' => VideoProject::STATUS_DRAFT,
            'topic' => 'Topik Rahsia Tenant Satu',
            'gaya' => 'MIDNIGHT',
            'aspect_ratio' => '9:16',
            'frame_rate' => 60,
        ];
    }
}
