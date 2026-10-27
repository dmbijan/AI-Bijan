<?php

namespace App\Modules\TeamAI\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\TeamAI\Models\TeamAiAsset;
use App\Modules\TeamAI\Models\TeamAiRepository;
use App\Modules\TeamAI\Services\TeamAiRepositorySync;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TeamAiWorkspaceController extends Controller
{
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    public function repositories(): JsonResponse
    {
        $repositories = TeamAiRepository::query()
            ->where('tenant_id', $this->tenantId())
            ->withCount('assets')
            ->orderBy('owner')
            ->orderBy('repository')
            ->get()
            ->map(fn (TeamAiRepository $repository) => [
                'id' => $repository->id,
                'owner' => $repository->owner,
                'repository' => $repository->repository,
                'branch' => $repository->branch,
                'has_access_token' => filled($repository->access_token_ciphertext),
                'asset_count' => $repository->assets_count,
                'last_commit_sha' => $repository->last_commit_sha,
                'last_synced_at' => $repository->last_synced_at,
            ]);

        return response()->json(['success' => true, 'data' => $repositories]);
    }

    public function saveRepository(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'owner' => ['required', 'string', 'max:39', 'regex:/^[A-Za-z0-9](?:[A-Za-z0-9-]{0,37}[A-Za-z0-9])?$/'],
            'repository' => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_.-]+$/'],
            'branch' => ['nullable', 'string', 'max:200', 'regex:/^[A-Za-z0-9._/-]+$/', 'not_regex:/(^\/|\/$|(^|\/)\.\.?($|\/)|\/\/)/'],
            'access_token' => ['nullable', 'string', 'max:500'],
        ]);

        $tenantId = $this->tenantId();
        $repository = TeamAiRepository::query()->firstOrNew([
            'tenant_id' => $tenantId,
            'owner' => $validated['owner'],
            'repository' => $validated['repository'],
        ]);
        $repository->branch = $validated['branch'] ?? 'main';
        if (filled($validated['access_token'] ?? null)) {
            $repository->access_token_ciphertext = Crypt::encryptString($validated['access_token']);
        }
        $repository->save();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $repository->id,
                'owner' => $repository->owner,
                'repository' => $repository->repository,
                'branch' => $repository->branch,
                'has_access_token' => filled($repository->access_token_ciphertext),
            ],
        ], $repository->wasRecentlyCreated ? 201 : 200);
    }

    public function sync(int $id, TeamAiRepositorySync $sync): JsonResponse
    {
        $repository = TeamAiRepository::query()
            ->where('tenant_id', $this->tenantId())
            ->findOrFail($id);

        try {
            $result = $sync->sync($repository);
        } catch (RuntimeException $exception) {
            Log::warning('Team AI repository sync failed', [
                'tenant_id' => $this->tenantId(),
                'repository_id' => $repository->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 502);
        }

        return response()->json(['success' => true, 'data' => $result]);
    }

    public function deleteRepository(int $id): JsonResponse
    {
        TeamAiRepository::query()
            ->where('tenant_id', $this->tenantId())
            ->findOrFail($id)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function assets(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['nullable', 'in:skills,rules,agents,hooks,workflows,mcp,culture,learnings,wiki,models,packages'],
            'repository_id' => ['nullable', 'integer'],
        ]);

        $assets = TeamAiAsset::query()
            ->where('tenant_id', $this->tenantId())
            ->with('repository:id,owner,repository')
            ->when($validated['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($validated['repository_id'] ?? null, fn ($query, $id) => $query->where('team_ai_repository_id', $id))
            ->orderBy('category')
            ->orderBy('path')
            ->paginate(50);

        return response()->json(['success' => true, 'data' => $assets]);
    }
}
