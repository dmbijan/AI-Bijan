<?php

namespace App\Modules\TeamAI\Services;

use App\Modules\TeamAI\Models\TeamAiRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TeamAiRepositorySync
{
    private const MAX_FILES = 50;
    private const MAX_FILE_BYTES = 65536;
    private const MAX_TOTAL_BYTES = 1048576;

    public function sync(TeamAiRepository $repository): array
    {
        $headers = [
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => '2022-11-28',
            'User-Agent' => 'AI-Bijan-TeamAI-Workspace',
        ];

        if ($repository->access_token_ciphertext) {
            $headers['Authorization'] = 'Bearer '.decrypt($repository->access_token_ciphertext);
        }

        $base = 'https://api.github.com/repos/'.rawurlencode($repository->owner).'/'.rawurlencode($repository->repository);
        $treeResponse = Http::withHeaders($headers)->timeout(20)->get(
            $base.'/git/trees/'.rawurlencode($repository->branch),
            ['recursive' => '1']
        );
        $this->requireSuccess($treeResponse->status(), 'Mendapatkan senarai fail repository GitHub');

        $tree = $treeResponse->json('tree');
        if (! is_array($tree)) {
            throw new RuntimeException('Respons GitHub tidak mengandungi senarai fail yang sah.');
        }
        if ($treeResponse->json('truncated') === true) {
            throw ValidationException::withMessages([
                'repository' => 'GitHub memulangkan senarai fail yang terpotong. Pecahkan aset AI kepada repository yang lebih kecil sebelum sync.',
            ]);
        }

        $candidates = [];
        foreach ($tree as $entry) {
            if (($entry['type'] ?? null) !== 'blob' || ! is_string($entry['path'] ?? null)) {
                continue;
            }

            $category = $this->categoryFor($entry['path']);
            if ($category !== null && $this->isImportableTextFile($entry['path'])) {
                $candidates[] = [$entry, $category];
            }
        }

        if (count($candidates) > self::MAX_FILES) {
            throw ValidationException::withMessages([
                'repository' => 'Repository mempunyai lebih daripada 50 aset AI yang boleh diimport. Pecahkan aset kepada beberapa repository dahulu.',
            ]);
        }

        $assets = [];
        $totalBytes = 0;
        foreach ($candidates as [$entry, $category]) {
            $response = Http::withHeaders($headers)->timeout(15)->get(
                $base.'/contents/'.implode('/', array_map('rawurlencode', explode('/', $entry['path']))),
                ['ref' => $repository->branch]
            );
            $this->requireSuccess($response->status(), 'Membaca fail aset daripada GitHub');

            if ($response->json('encoding') !== 'base64' || ! is_string($response->json('content'))) {
                throw new RuntimeException('GitHub memulangkan kandungan fail dalam format yang tidak disokong.');
            }

            $content = base64_decode(preg_replace('/\s+/', '', $response->json('content')), true);
            if (! is_string($content)) {
                throw new RuntimeException('Kandungan fail GitHub tidak dapat dinyahkod.');
            }

            $bytes = strlen($content);
            if ($bytes > self::MAX_FILE_BYTES) {
                throw ValidationException::withMessages([
                    'repository' => "Fail {$entry['path']} melebihi had 64 KB.",
                ]);
            }
            $totalBytes += $bytes;
            if ($totalBytes > self::MAX_TOTAL_BYTES) {
                throw ValidationException::withMessages([
                    'repository' => 'Jumlah aset melebihi had 1 MB untuk satu sync.',
                ]);
            }

            $assets[] = [
                'path' => $entry['path'],
                'category' => $category,
                'title' => $this->titleFor($entry['path']),
                'git_sha' => $entry['sha'] ?? null,
                'content' => $this->redactSecrets($content),
            ];
        }

        return DB::transaction(function () use ($repository, $assets, $treeResponse): array {
            $currentPaths = [];
            foreach ($assets as $asset) {
                $repository->assets()->updateOrCreate(
                    ['path' => $asset['path']],
                    [
                        'tenant_id' => $repository->tenant_id,
                        'category' => $asset['category'],
                        'title' => $asset['title'],
                        'git_sha' => $asset['git_sha'],
                        'content' => $asset['content'],
                    ]
                );
                $currentPaths[] = $asset['path'];
            }

            $stale = $repository->assets();
            if ($currentPaths === []) {
                $stale->delete();
            } else {
                $stale->whereNotIn('path', $currentPaths)->delete();
            }

            $repository->forceFill([
                'last_commit_sha' => $treeResponse->json('sha'),
                'last_synced_at' => now(),
            ])->save();

            return ['imported' => count($assets), 'categories' => array_count_values(array_column($assets, 'category'))];
        });
    }

    private function requireSuccess(int $status, string $action): void
    {
        if ($status < 200 || $status >= 300) {
            Log::warning('Team AI GitHub sync request failed', ['action' => $action, 'status' => $status]);
            throw new RuntimeException("{$action} gagal (HTTP {$status}). Semak akses repository dan cuba lagi.");
        }
    }

    private function isImportableTextFile(string $path): bool
    {
        $normalized = strtolower($path);
        $name = basename($normalized);

        if (
            preg_match('/(^|\/)\.env(?:\.|$)/', $normalized)
            || preg_match('/(^|\/)(secrets?|credentials?)(\/|$)/', $normalized)
            || preg_match('/\.(pem|key|p12|pfx|crt|cer|sqlite|db)$/', $name)
            || in_array($name, ['.npmrc', '.netrc', 'id_rsa', 'id_ed25519'], true)
        ) {
            return false;
        }

        return in_array(pathinfo($name, PATHINFO_EXTENSION), ['md', 'mdc', 'txt', 'json', 'yaml', 'yml', 'toml'], true);
    }

    private function categoryFor(string $path): ?string
    {
        $path = strtolower(str_replace('\\', '/', $path));
        $segments = explode('/', $path);
        $first = $segments[0] ?? '';

        if ($first === '.github' && ($segments[1] ?? '') === 'workflows') {
            return null;
        }
        if ($path === 'culture.md') {
            return 'culture';
        }
        if (in_array($first, ['skills'], true) || str_contains($path, '/skills/')) {
            return 'skills';
        }
        if (in_array($first, ['rules', 'instructions', 'claudemd'], true) || str_contains($path, '/instructions/') || str_contains($path, '/rules/')) {
            return 'rules';
        }
        if ($first === 'agents' || str_contains($path, '/agents/')) {
            return 'agents';
        }
        if ($first === 'hooks' || str_contains($path, '/hooks/')) {
            return 'hooks';
        }
        if ($first === 'workflows' || str_starts_with($path, '.teamai/workflows/')) {
            return 'workflows';
        }
        if ($first === 'mcp' || str_starts_with($path, '.teamai/mcp/')) {
            return 'mcp';
        }
        if (in_array($first, ['culture', 'learnings', 'wiki', 'models', 'teamwiki', 'docs', 'packages'], true)) {
            return match ($first) {
                'teamwiki', 'docs' => 'wiki',
                default => $first,
            };
        }
        if ($first === 'teamai.yaml' || $path === '.teamai/teamai.yaml') {
            return 'packages';
        }

        return null;
    }

    private function titleFor(string $path): string
    {
        $name = pathinfo(basename($path), PATHINFO_FILENAME);
        $title = trim(preg_replace('/[-_]+/', ' ', $name));

        return mb_substr($title !== '' ? $title : $path, 0, 180);
    }

    private function redactSecrets(string $content): string
    {
        return preg_replace_callback(
            '/(["\']?(?:api[_-]?key|access[_-]?token|(?:github|gitlab|oauth)[_-]?token|private[_-]?key|client[_-]?key|authorization|token|password|client[_-]?secret|secret|credential)["\']?\s*[:=]\s*)(?:"[^"\r\n]*"|\'[^\'\r\n]*\'|[^,\r\n}#]+)/i',
            fn (array $matches) => $matches[1].'"[REDACTED]"',
            $content
        ) ?? $content;
    }
}
