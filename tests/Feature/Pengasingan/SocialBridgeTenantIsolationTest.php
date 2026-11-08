<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\SocialBridge\Models\SocialAccount;
use App\Modules\SocialBridge\Models\SocialPost;
use App\Modules\SocialBridge\Models\SocialPostTarget;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul SocialBridge (siaran silang, asal: Post Bridge).
 *
 * Ditambah 9 Nov 2026. Akaun sosial mengandungi token akses rahsia;
 * catatan mengandungi kandungan kempen yang belum diterbitkan.
 */
class SocialBridgeTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'SocialBridge';

    protected array $model = [
        SocialAccount::class,
        SocialPost::class,
        SocialPostTarget::class,
    ];

    protected ?string $laluanApi = '/api/v1/social-bridge/posts';

    protected ?string $namaRahsia = 'Catatan Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        switch ($this->rekodSemasa) {
            case SocialAccount::class:
                return [
                    'platform' => 'instagram',
                    'account_name' => 'Akaun Rahsia Tenant Satu '.$n,
                    'handle' => '@rahasia'.$n,
                    'status' => SocialAccount::STATUS_ACTIVE,
                    'is_brand' => true,
                ];

            case SocialPost::class:
                return [
                    'uuid' => 'sp-uuid-'.$n,
                    'title' => 'Catatan Rahsia Tenant Satu '.$n,
                    'body' => 'Kandungan Rahsia Tenant Satu '.$n,
                    'type' => SocialPost::TYPE_TEXT,
                    'status' => SocialPost::STATUS_DRAFT,
                ];

            default:
                $catatan = SocialPost::create([
                    'uuid' => 'sp-target-'.$n,
                    'title' => 'Catatan Sasaran '.$n,
                    'body' => 'Kandungan '.$n,
                    'status' => SocialPost::STATUS_DRAFT,
                ]);

                return [
                    'social_post_id' => $catatan->id,
                    'platform' => 'x',
                    'status' => SocialPostTarget::STATUS_PENDING,
                    'content' => 'Sasaran Rahsia Tenant Satu '.$n,
                ];
        }
    }
}
