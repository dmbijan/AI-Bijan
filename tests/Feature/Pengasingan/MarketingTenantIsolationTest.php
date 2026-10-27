<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Marketing\Models\Campaign;
use App\Modules\Marketing\Models\Contact;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Marketing (kempen, kenalan pemasaran).
 *
 * Ditambah 27 Okt 2026 (P1-1d). `marketing_contacts` menyimpan e-mel, nombor
 * WhatsApp dan status persetujuan (opt-in) — kebocoran bermakna tenant lain
 * boleh menghantar mesej kepada senarai pelanggan pesaing, yang juga
 * melanggar PDPA 2010 (pemasaran tanpa persetujuan).
 */
class MarketingTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Marketing';

    protected array $model = [
        Campaign::class,
        Contact::class,
    ];

    /** `marketing_campaigns.code` NOT NULL tetapi tiada dalam `$fillable`. */
    protected bool $gunaForce = true;

    protected ?string $laluanApi = '/api/v1/marketing/contacts';

    protected ?string $namaRahsia = 'Rahsia';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === Contact::class) {
            return [
                'first_name' => 'Rahsia',
                'last_name' => 'Tenant Satu ' . $n,
                'email' => 'rahsia' . $n . '@marketing-tenant-satu.test',
                'phone' => '6012345' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            ];
        }

        static $n = 0;
        $n++;

        // `marketing_campaigns.code` NOT NULL tetapi TIDAK dalam `$fillable`
        // model Campaign → perlu `forceCreate` (gunaForce) untuk cipta rekod.
        return [
            'code' => 'CMP-' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'name' => 'Kempen Rahsia Tenant Satu ' . $n,
            'slug' => 'kempen-rahsia-' . $n,
            'type' => 'email',
            'status' => 'draft',
        ];
    }
}
