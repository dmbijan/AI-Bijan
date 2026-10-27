<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\DocumentAI\Models\DocumentTemplate;
use App\Modules\IndustryPacks\Models\IndustryProfile;
use App\Modules\ServiceOS\Models\ServiceClient;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul DocumentAI (templat dokumen), ServiceOS
 * (pelanggan servis) + IndustryPacks (profil industri).
 *
 * Ditambah 27 Okt 2026 (P1-1d).
 *
 * ⚠️ Model `DocumentAI\Models\Document` TIDAK diuji di sini: `documents`
 * dicipta oleh DUA migrasi berbeza (`database/migrations/2026_10_18_000003`
 * dan `app/Modules/DocumentAI/database/migrations/2026_10_19_000040`) dengan
 * skema berlainan — model mengikut `document_number` manakala jadual aktif
 * guna `ulid`/`type`/`number`. Ini pertindihan skema yang perlu diselesaikan
 * (lihat docs/TODO.md), bukan sesuatu yang boleh "dibetulkan" dalam ujian.
 */
class DocumentAiServiceIsolationTest extends UjianPengasingan
{
    protected string $modul = 'DocumentAI,IndustryPacks,ServiceOS';

    protected array $model = [
        DocumentTemplate::class,
        ServiceClient::class,
        IndustryProfile::class,
    ];

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected string $sebabTiadaApi = 'laluan dokumen memerlukan entitlement khas';

    /**
     * `document_templates.key/name/type` NOT NULL tetapi TIDAK dalam
     * `$fillable` model (yang senaraikan `template_name`/`template_type`).
     * Perlu `forceCreate` supaya ujian tidak gagal atas sebab skema.
     */
    protected bool $gunaForce = true;

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === ServiceClient::class) {
            return [
                'display_name' => 'Rahsia Tenant Satu ' . $n,
                'email' => 'rahsia' . $n . '@servis-tenant-satu.test',
                'phone' => '6012345' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            ];
        }

        if ($this->rekodSemasa === IndustryProfile::class) {
            return [
                'industry_key' => 'industri_rahsia_' . $n,
                'name' => 'Industri Rahsia Tenant Satu ' . $n,
                'enabled_modules' => json_encode(['pos']),
            ];
        }

        return [
            'key' => 'templat_rahsia_' . $n,
            'name' => 'Templat Rahsia Tenant Satu ' . $n,
            'type' => 'invoice',
            'template_name' => 'Templat Rahsia Tenant Satu ' . $n,
            'template_type' => 'invoice',
            'field_definitions' => json_encode([]),
        ];
    }
}
