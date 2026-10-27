<?php

namespace Tests\Feature;

use App\Modules\TeamAI\Models\TeamAiRepository;
use App\Modules\TeamAI\Services\TeamAiRepositorySync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\AksesTenant;
use Tests\TestCase;

class TeamAiWorkspaceTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [, $tenant] = $this->daftar('team-ai@bijan.test', 'Team AI Sdn Bhd');
        $this->dalamTenant($tenant);
    }

    public function test_sync_imports_team_assets_and_redacts_credentials(): void
    {
        $repository = TeamAiRepository::create([
            'owner' => 'dmbijan',
            'repository' => 'AI-Bijan',
            'branch' => 'main',
        ]);
        Http::fake([
            'https://api.github.com/repos/dmbijan/AI-Bijan/git/trees/main*' => Http::response([
                'sha' => 'tree-sha',
                'tree' => [
                    ['type' => 'blob', 'path' => 'skills/finance/README.md', 'sha' => 'skill-sha'],
                    ['type' => 'blob', 'path' => 'mcp/server.json', 'sha' => 'mcp-sha'],
                    ['type' => 'blob', 'path' => '.env', 'sha' => 'env-sha'],
                    ['type' => 'blob', 'path' => '.github/workflows/ci.yml', 'sha' => 'ci-sha'],
                ],
            ]),
            'https://api.github.com/repos/dmbijan/AI-Bijan/contents/skills/finance/README.md*' => Http::response([
                'encoding' => 'base64',
                'content' => base64_encode("# Finance skill\n"),
            ]),
            'https://api.github.com/repos/dmbijan/AI-Bijan/contents/mcp/server.json*' => Http::response([
                'encoding' => 'base64',
                'content' => base64_encode('{"api_key":"sensitive-value","github_token":"another-secret","enabled":true}'),
            ]),
        ]);

        $result = app(TeamAiRepositorySync::class)->sync($repository);

        $this->assertSame(2, $result['imported']);
        $this->assertDatabaseHas('team_ai_assets', [
            'team_ai_repository_id' => $repository->id,
            'path' => 'skills/finance/README.md',
            'category' => 'skills',
            'content' => "# Finance skill\n",
        ]);
        $this->assertDatabaseHas('team_ai_assets', [
            'team_ai_repository_id' => $repository->id,
            'path' => 'mcp/server.json',
            'category' => 'mcp',
            'content' => '{"api_key":"[REDACTED]","github_token":"[REDACTED]","enabled":true}',
        ]);
        $this->assertDatabaseMissing('team_ai_assets', ['content' => 'sensitive-value']);
        $this->assertDatabaseMissing('team_ai_assets', ['content' => 'another-secret']);
        $this->assertDatabaseMissing('team_ai_assets', ['path' => '.env']);
        $this->assertDatabaseMissing('team_ai_assets', ['path' => '.github/workflows/ci.yml']);
    }

    public function test_sync_does_not_import_unclassified_code_files(): void
    {
        $repository = TeamAiRepository::create([
            'owner' => 'dmbijan',
            'repository' => 'AI-Bijan',
            'branch' => 'main',
        ]);
        Http::fake([
            'https://api.github.com/repos/dmbijan/AI-Bijan/git/trees/main*' => Http::response([
                'sha' => 'tree-sha',
                'tree' => [
                    ['type' => 'blob', 'path' => 'src/Kernel.php', 'sha' => 'php-sha'],
                ],
            ]),
        ]);

        $result = app(TeamAiRepositorySync::class)->sync($repository);

        $this->assertSame(0, $result['imported']);
        $this->assertDatabaseMissing('team_ai_assets', ['team_ai_repository_id' => $repository->id]);
    }

    public function test_team_ai_workspace_page_renders(): void
    {
        $this->get('/app/team-ai')
            ->assertOk()
            ->assertSee('Team AI')
            ->assertSee('Repository pasukan');
    }

    public function test_truncated_github_tree_is_rejected_without_deleting_existing_assets(): void
    {
        $repository = TeamAiRepository::create([
            'owner' => 'dmbijan',
            'repository' => 'AI-Bijan',
            'branch' => 'main',
        ]);
        $repository->assets()->create([
            'tenant_id' => $repository->tenant_id,
            'path' => 'skills/existing.md',
            'category' => 'skills',
            'title' => 'Existing',
            'content' => 'existing asset',
        ]);
        Http::fake([
            'https://api.github.com/repos/dmbijan/AI-Bijan/git/trees/main*' => Http::response([
                'sha' => 'partial-sha',
                'truncated' => true,
                'tree' => [],
            ]),
        ]);

        try {
            app(TeamAiRepositorySync::class)->sync($repository);
            $this->fail('Sync with a truncated tree should be rejected.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('repository', $exception->errors());
        }

        $this->assertDatabaseHas('team_ai_assets', [
            'team_ai_repository_id' => $repository->id,
            'path' => 'skills/existing.md',
        ]);
    }
}
