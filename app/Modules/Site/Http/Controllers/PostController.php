<?php

namespace App\Modules\Site\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Site\Services\PostService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$this->tenantId()` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * List all posts for a site.
     */
    public function index(Request $request, int $siteId): JsonResponse
    {
        $posts = $this->postService->getPosts($siteId, $this->tenantId(), [
            'status' => $request->input('status'),
            'tag' => $request->input('tag'),
            'author_id' => $request->input('author_id'),
        ]);
        
        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }

    /**
     * Get a single post.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id, $this->tenantId());

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found.',
            ], 404);
        }

        // Record view
        $this->postService->recordView($post);

        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }

    /**
     * Get post by slug.
     */
    public function getBySlug(Request $request, int $siteId, string $slug): JsonResponse
    {
        $post = $this->postService->getPostBySlug($slug, $siteId, $this->tenantId());

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found.',
            ], 404);
        }

        // Record view
        $this->postService->recordView($post);

        return response()->json([
            'success' => true,
            'data' => $post,
        ]);
    }

    /**
     * Create a new post.
     */
    public function store(Request $request, int $siteId): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|string|max:500',
            'status' => 'nullable|string|in:draft,published,scheduled',
            'author_id' => 'nullable|integer',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        $validated['tenant_id'] = $this->tenantId();
        $validated['site_id'] = $siteId;

        $post = $this->postService->createPost($validated);

        return response()->json([
            'success' => true,
            'message' => 'Post created successfully.',
            'data' => $post,
        ], 201);
    }

    /**
     * Update a post.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id, $this->tenantId());

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found.',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'featured_image' => 'nullable|string|max:500',
            'status' => 'nullable|string|in:draft,published,scheduled',
            'author_id' => 'nullable|integer',
            'published_at' => 'nullable|date',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'tags' => 'nullable|array',
        ]);

        $post = $this->postService->updatePost($post, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Post updated successfully.',
            'data' => $post,
        ]);
    }

    /**
     * Delete a post.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id, $this->tenantId());

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found.',
            ], 404);
        }

        $this->postService->deletePost($post);

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully.',
        ]);
    }

    /**
     * Publish a post.
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        $post = $this->postService->getPostById($id, $this->tenantId());

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found.',
            ], 404);
        }

        $post = $this->postService->publishPost($post);

        return response()->json([
            'success' => true,
            'message' => 'Post published successfully.',
            'data' => $post,
        ]);
    }

    /**
     * Schedule a post.
     */
    public function schedule(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'published_at' => 'required|date',
        ]);

        $post = $this->postService->getPostById($id, $this->tenantId());

        if (!$post) {
            return response()->json([
                'success' => false,
                'message' => 'Post not found.',
            ], 404);
        }

        $post = $this->postService->schedulePost($post, $validated['published_at']);

        return response()->json([
            'success' => true,
            'message' => 'Post scheduled successfully.',
            'data' => $post,
        ]);
    }

    /**
     * Get recent published posts.
     */
    public function recent(Request $request, int $siteId): JsonResponse
    {
        $limit = $request->input('limit', 5);
        $posts = $this->postService->getRecentPosts($siteId, $this->tenantId(), $limit);
        
        return response()->json([
            'success' => true,
            'data' => $posts,
        ]);
    }
}
