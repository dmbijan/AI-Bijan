<?php

namespace App\Modules\Community\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Community\Models\CommunityPost;
use App\Modules\Community\Models\CommunitySpace;
use App\Modules\Community\Services\CommunityService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function __construct(private CommunityService $service) {}

    private function tenantId(): ?int
    {
        try {
            return app(TenantContext::class)->id();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Senarai ruang. */
    public function spaces(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->ruangBolehLihat(auth()->check()),
        ]);
    }

    /** Cipta ruang. */
    public function createSpace(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[a-z0-9\-]+$/'],
            'description' => ['nullable', 'string', 'max:2000'],
            'space_type' => ['nullable', 'in:forum,group,academy,showcase,help'],
            'visibility' => ['nullable', 'in:public,members,private'],
        ]);

        $ruang = $this->service->ciptaRuang($validated);

        return response()->json(['success' => true, 'data' => $ruang], 201);
    }

    /** Feed pos. */
    public function feed(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'space_id' => ['nullable', 'integer'],
            'sort' => ['nullable', 'in:terbaru,popular'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->service->feed(
                $validated['space_id'] ?? null,
                $validated['sort'] ?? 'terbaru',
                $validated['limit'] ?? 25
            ),
        ]);
    }

    /** Cipta pos. */
    public function createPost(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'space_id' => ['required', 'integer'],
            'post_type' => ['nullable', 'in:discussion,question,showcase,announcement,idea'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:20000'],
            'attachments' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
        ]);

        $pos = $this->service->ciptaPos(
            $validated['space_id'],
            $validated,
            $this->tenantId()
        );

        return response()->json(['success' => true, 'data' => $pos], 201);
    }

    /** Butiran pos. */
    public function showPost(int $id): JsonResponse
    {
        $pos = CommunityPost::query()->with('replies')->findOrFail($id);

        $pos->increment('view_count');

        return response()->json(['success' => true, 'data' => $pos->fresh()]);
    }

    /** Balas pos. */
    public function reply(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $balasan = $this->service->balas($id, $validated['body'], $this->tenantId());

        return response()->json(['success' => true, 'data' => $balasan], 201);
    }

    /** Terima balasan. */
    public function acceptReply(Request $request, int $postId, int $replyId): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->terimaBalasan($postId, $replyId),
        ]);
    }

    /** Ringkasan. */
    public function ringkasan(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->service->ringkasan()]);
    }
}
