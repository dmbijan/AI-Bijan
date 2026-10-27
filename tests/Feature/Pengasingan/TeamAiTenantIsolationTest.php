<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\TeamAI\Models\TeamAiAsset;
use App\Modules\TeamAI\Models\TeamAiRepository;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul TeamAI (repositori + aset pasukan AI).
 *
 * Ditambah 28 Okt 2026. `skrip/audit-tenant.php` melaporkan TeamAI sebagai
 * satu-satunya modul tanpa ujian pengasingan, dan dua modelnya sebagai
 * "trait tanpa kolum" — punca sebenar: migrasi
 * `2026_10_27_000100_create_team_ai_workspace_tables` belum dijalankan pada DB
 * dev, jadi `team_ai_*` tidak wujud dan setiap `create()` gagal.
 *
 * Kebocoran di sini bermakna tenant lain boleh membaca token GitHub tersulit,
 * arahan ejen (`agents/`), `CLAUDE.md` atau definisi MCP pasukan pesaing.
 */
class TeamAiTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'TeamAI';

    protected array $model = [
        TeamAiRepository::class,
        TeamAiAsset::class,
    ];

    protected ?string $laluanApi = '/api/v1/team-ai/repositories';

    protected ?string $namaRahsia = 'rahsia-team-ai';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === TeamAiRepository::class) {
            return [
                'owner' => 'pemilik-tenant-satu',
                'repository' => 'rahsia-team-ai-' . $n,
                'branch' => 'main',
                'access_token_ciphertext' => 'token-' . $n,
            ];
        }

        // Aset memerlukan repositori induk dalam tenant yang SAMA.
        $repositori = TeamAiRepository::create([
            'owner' => 'pemilik-tenant-satu',
            'repository' => 'rahsia-team-ai-' . $n,
            'branch' => 'main',
        ]);

        return [
            'team_ai_repository_id' => $repositori->id,
            'path' => 'agents/rahsia-team-ai-' . $n . '.md',
            'category' => 'agents',
            'title' => 'Ejen Rahsia Tenant Satu ' . $n,
            'content' => 'kandungan rahsia-team-ai ' . $n,
        ];
    }
}
