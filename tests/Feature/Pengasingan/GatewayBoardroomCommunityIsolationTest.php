<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\AIGateway\Models\AiCreditWallet;
use App\Modules\AIGateway\Models\AiUsageEvent;
use App\Modules\Boardroom\Models\BoardroomFinding;
use App\Modules\Boardroom\Models\BoardroomSession;
use App\Modules\Community\Models\CommunityPost;
use App\Modules\Community\Models\CommunitySpace;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — AIGateway (dompet kredit + peristiwa penggunaan),
 * Boardroom (sesi keputusan) dan Community (ruang + siaran).
 *
 * Ditambah 28 Okt 2026 (T-111). Sebelum ini ketiga-tiga modul dikira "diliputi"
 * kerana `skrip/audit-tenant.php` memberi kredit melalui import dalam korpus
 * ujian gabungan — tiada fail yang benar-benar menguji mereka.
 *
 * • AIGateway: dompet kredit ialah wang. Kebocoran = satu tenant boleh
 *   menggunakan kredit berbayar tenant lain.
 * • Boardroom: sesi mengandungi keputusan sulit pengurusan.
 * • Community: ruang + siaran ialah kandungan pelanggan.
 */
class GatewayBoardroomCommunityIsolationTest extends UjianPengasingan
{
    protected string $modul = 'AIGateway,Boardroom,Community';

    protected array $model = [
        AiCreditWallet::class,
        AiUsageEvent::class,
        BoardroomSession::class,
        BoardroomFinding::class,
        CommunitySpace::class,
        CommunityPost::class,
    ];

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === AiCreditWallet::class) {
            return ['balance' => 1000, 'currency' => 'MYR'];
        }

        if ($this->rekodSemasa === AiUsageEvent::class) {
            return [
                'provider_model_id' => 'demo-model',
                'model_display_name' => 'Demo',
                'tier' => 'standard',
                'task_type' => 'chat',
                'input_tokens' => 10,
                'output_tokens' => 20,
                'total_tokens' => 30,
                'credits_charged' => 1,
                'cost_rm' => 0.5,
                'status' => AiUsageEvent::STATUS_SUCCESS,
            ];
        }

        if ($this->rekodSemasa === BoardroomSession::class) {
            return [
                'requested_by' => $this->u->id,
                'question' => 'Adakah kita patut buka cawangan kedua? (Rahsia Tenant Satu ' . $n . ')',
                'category' => 'growth',
                'status' => 'draft',
            ];
        }

        if ($this->rekodSemasa === BoardroomFinding::class) {
            $sesi = BoardroomSession::create([
                'requested_by' => $this->u->id,
                'question' => 'Sesi induk untuk dapatan ' . $n,
                'category' => 'growth',
                'status' => 'draft',
            ]);

            return [
                'session_id' => $sesi->id,
                'agent_key' => 'analyst',
                'finding_type' => BoardroomFinding::TYPE_FACT,
                'title' => 'Dapatan Rahsia Tenant Satu ' . $n,
                'content' => 'Rahsia Tenant Satu — margin turun 12%.',
            ];
        }

        if ($this->rekodSemasa === CommunitySpace::class) {
            return [
                'name' => 'Ruang Rahsia Tenant Satu ' . $n,
                'slug' => 'ruang-rahsia-' . $n,
            ];
        }

        $ruang = CommunitySpace::create([
            'name' => 'Ruang Rahsia Tenant Satu ' . $n,
            'slug' => 'ruang-rahsia-' . $n,
        ]);

        return [
            'community_space_id' => $ruang->id,
            'author_user_id' => $this->u->id,
            'title' => 'Siaran Rahsia Tenant Satu ' . $n,
            'body' => 'Rahsia Tenant Satu — kandungan.',
        ];
    }
}
