<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Modules\TeamAI\Models\TeamAiAsset;
use App\Modules\TeamAI\Models\TeamAiRepository;
use App\Modules\TeamAI\Services\TeamAiRepositorySync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * TeamAI — pagar tambahan (T-108, 28 Okt 2026).
 *
 * `TeamAiWorkspaceTest` meliputi penapisan/penyulitan sync. Fail ini meliputi
 * perkara yang didakwa dalam `docs/TEAM-AI-WORKSPACE.md` tetapi belum diuji:
 * token disulitkan & tidak dipulangkan, padam cascade, idempotensi sync, dan
 * had 50 fail / 64 KB / 1 MB yang MESTI gagal sebelum menulis separuh.
 */
class TeamAiWorkspaceGuardTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private Tenant $syarikat;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [, $this->syarikat] = $this->daftar('team-ai-guard@bijan.test', 'Team AI Guard Sdn Bhd');
        $this->dalamTenant($this->syarikat);
    }

    private function repositori(string $nama = 'AI-Bijan', ?string $token = null): TeamAiRepository
    {
        return TeamAiRepository::create(array_filter([
            'owner' => 'dmbijan',
            'repository' => $nama,
            'branch' => 'main',
            'access_token_ciphertext' => $token !== null ? Crypt::encryptString($token) : null,
        ]));
    }

    /** `Http::fake` untuk satu pokok + kandungan setiap fail. */
    private function palsuGithub(array $fail): void
    {
        $pokok = [];
        foreach (array_keys($fail) as $path) {
            $pokok[] = ['type' => 'blob', 'path' => $path, 'sha' => 'sha-'.md5($path)];
        }

        $skrip = [
            'https://api.github.com/repos/dmbijan/*/git/trees/main*' => Http::response(['sha' => 'tree-sha', 'tree' => $pokok]),
        ];
        foreach ($fail as $path => $kandungan) {
            $skrip['https://api.github.com/repos/dmbijan/*/contents/'.$path.'*'] = Http::response([
                'encoding' => 'base64',
                'content' => base64_encode($kandungan),
            ]);
        }

        Http::fake($skrip);
    }

    /** Pengguna pemilik tenant semasa (didapati daripada keahlian). */
    private function pemilik()
    {
        $this->dalamTenant($this->syarikat);

        return $this->syarikat->members()->first()->user;
    }

    // ── Token ───────────────────────────────────────────────────────────

    public function test_token_disulitkan_dan_tidak_pernah_dipulangkan(): void
    {
        $this->sebagai($this->pemilik(), $this->syarikat)->postJson('/api/v1/team-ai/repositories', [
            'owner' => 'dmbijan',
            'repository' => 'rahsia',
            'access_token' => 'ghp_nyahatoken123',
        ])->assertCreated()
            ->assertJsonPath('data.has_access_token', true)
            ->assertJsonMissing(['access_token' => 'ghp_nyahatoken123']);

        $this->dalamTenant($this->syarikat);
        $repo = TeamAiRepository::where('repository', 'rahsia')->firstOrFail();

        $this->assertNotSame('ghp_nyahatoken123', $repo->access_token_ciphertext, 'Token disimpan tanpa sulitan.');
        $this->assertSame('ghp_nyahatoken123', Crypt::decryptString($repo->access_token_ciphertext));

        $senarai = $this->getJson('/api/v1/team-ai/repositories')->assertOk()->getContent();
        $this->assertStringNotContainsString('ghp_nyahatoken123', $senarai);
        $this->assertStringNotContainsString('ciphertext', $senarai);
    }

    public function test_repositori_tanpa_token_dibenarkan(): void
    {
        $this->sebagai($this->pemilik(), $this->syarikat)->postJson('/api/v1/team-ai/repositories', [
            'owner' => 'dmbijan',
            'repository' => 'awam',
        ])->assertCreated()->assertJsonPath('data.has_access_token', false);
    }

    // ── Padam cascade ───────────────────────────────────────────────────

    public function test_padam_repositori_menurun_aset_anak(): void
    {
        $repo = $this->repositori();
        TeamAiAsset::create([
            'team_ai_repository_id' => $repo->id,
            'path' => 'agents/a.md',
            'category' => 'agents',
            'title' => 'A',
            'content' => 'x',
        ]);

        $this->assertSame(1, TeamAiAsset::count());

        $this->sebagai($this->pemilik(), $this->syarikat)
            ->deleteJson('/api/v1/team-ai/repositories/'.$repo->id)->assertOk();

        $this->dalamTenant($this->syarikat);
        $this->assertSame(0, TeamAiAsset::withoutGlobalScopes()->count(), 'Aset anak tidak dipadam (FK cascade).');
        $this->assertSame(0, TeamAiRepository::withoutGlobalScopes()->count());
    }

    // ── Idempotensi sync ────────────────────────────────────────────────

    public function test_sync_kedua_tidak_menduplikasi_dan_mengemas_kini_sha(): void
    {
        $repo = $this->repositori();
        $this->palsuGithub(['skills/satu.md' => "# Satu\n"]);

        $pertama = app(TeamAiRepositorySync::class)->sync($repo);
        $this->assertSame(1, $pertama['imported']);

        $kedua = app(TeamAiRepositorySync::class)->sync($repo->fresh());

        $this->assertSame(1, TeamAiAsset::count(), 'Sync kedua mencipta aset berganda.');
        $this->assertSame(1, $kedua['imported']);
        $this->assertSame('tree-sha', $repo->fresh()->last_commit_sha);
        $this->assertNotNull($repo->fresh()->last_synced_at);
    }

    // ── Had 50 fail / 64 KB / 1 MB ──────────────────────────────────────

    public function test_lebih_50_fail_ditolak_sebelum_menulis(): void
    {
        $repo = $this->repositori();
        $fail = [];
        for ($i = 1; $i <= 51; $i++) {
            $fail['skills/f'.$i.'.md'] = "x\n";
        }
        $this->palsuGithub($fail);

        try {
            app(TeamAiRepositorySync::class)->sync($repo);
            $this->fail('Sync 51 fail sepatutnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('repository', $e->errors());
        }

        $this->assertSame(0, TeamAiAsset::withoutGlobalScopes()->count(), 'Separuh aset ditulis walaupun had dilanggar.');
    }

    public function test_fail_melebihi_64kb_ditolak_dan_tiada_separuh_tulisan(): void
    {
        $repo = $this->repositori();
        $this->palsuGithub([
            'skills/kecil.md' => "# kecil\n",
            'skills/besar.md' => str_repeat('a', 65537),
        ]);

        try {
            app(TeamAiRepositorySync::class)->sync($repo);
            $this->fail('Fail 64 KB+ sepatutnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('repository', $e->errors());
        }

        $this->assertSame(0, TeamAiAsset::withoutGlobalScopes()->count(), 'Transaksi tidak digulung semula.');
    }

    public function test_jumlah_melebihi_1mb_ditolak(): void
    {
        $repo = $this->repositori();
        $fail = [];
        // 20 fail × 60 KB = 1.17 MB → melebihi had jumlah; setiap fail masih < 64 KB.
        for ($i = 1; $i <= 20; $i++) {
            $fail['skills/big'.$i.'.md'] = str_repeat('b', 60000);
        }
        $this->palsuGithub($fail);

        try {
            app(TeamAiRepositorySync::class)->sync($repo);
            $this->fail('Jumlah 1 MB+ sepatutnya ditolak.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('repository', $e->errors());
        }

        $this->assertSame(0, TeamAiAsset::withoutGlobalScopes()->count());
    }
}
