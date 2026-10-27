<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\BrainHub\Models\Insight;
use App\Modules\ExecutiveAI\Models\ExecutiveBriefing;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul BrainHub (insight AI) + ExecutiveAI (taklimat).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Insight AI dan taklimat eksekutif ialah
 * kesimpulan daripada SELURUH data tenant (hasil, stok, HR). Kebocoran di
 * sini bermakna ringkasan strategik penuh tenant lain terbaca — kesan paling
 * besar berbanding kebocoran satu jadual.
 */
class BrainHubExecutiveIsolationTest extends UjianPengasingan
{
    protected string $modul = 'BrainHub,ExecutiveAI';

    protected array $model = [
        Insight::class,
        ExecutiveBriefing::class,
    ];

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected string $sebabTiadaApi = 'laluan insight AI memerlukan entitlement/kredit AI';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === ExecutiveBriefing::class) {
            return [
                'user_id' => $this->u->id,
                'type' => 'daily',
                'content' => json_encode(['ringkasan' => 'Rahsia Tenant Satu ' . $n]),
            ];
        }

        return [
            'title' => 'Insight Rahsia Tenant Satu ' . $n,
            'description' => 'Ringkasan dalaman tenant satu.',
            'category' => 'sales',
            'type' => 'trend',
            'severity' => 'low',
        ];
    }
}
