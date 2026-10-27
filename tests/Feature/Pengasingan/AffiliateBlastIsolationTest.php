<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Affiliate\Models\Affiliate;
use App\Modules\Blast\Models\BlastCampaign;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Affiliate (ahli gabungan) + Blast (kempen pukal).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Kedua-duanya menyimpan data peribadi untuk
 * pemasaran: e-mel/telefon ahli gabungan dan senarai penerima blast. Kebocoran
 * bermakna tenant boleh menghantar kempen kepada audiens tenant lain —
 * pelanggaran PDPA, bukan sekadar pepijat data.
 */
class AffiliateBlastIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Affiliate';

    protected array $model = [
        Affiliate::class,
        BlastCampaign::class,
    ];

    protected ?string $laluanApi = '/api/v1/affiliate/affiliates';

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === BlastCampaign::class) {
            return [
                'name' => 'Blast Rahsia Tenant Satu ' . $n,
                'channel' => 'email',
                'subject' => 'Rahsia Tenant Satu',
                'status' => 'draft',
            ];
        }

        return [
            'affiliate_code' => 'RAHSIA' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'first_name' => 'Rahsia',
            'last_name' => 'Tenant Satu ' . $n,
            'email' => 'rahsia' . $n . '@afiliasi-tenant-satu.test',
            'status' => 'pending',
        ];
    }
}
