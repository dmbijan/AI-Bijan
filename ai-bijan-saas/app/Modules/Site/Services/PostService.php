<?php

namespace App\Modules\Site\Services;

use App\Modules\Site\Models\Post;
use Illuminate\Support\Facades\DB;

class PostService
{
    /**
     * Get all posts for a site.
     */
    public function getPosts(int $siteId, int $tenantId, array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Post::where('site_id', $siteId)->where('tenant_id', $tenantId);

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['tag'])) {
            $query->byTag($filters['tag']);
        }

        if (isset($filters['author_id'])) {
            $query->where('author_id', $filters['author_id']);
        }

        return $query->orderBy('published_at', 'desc')->get();
    }

    /**
     * Get a post by ID.
     */
    public function getPostById(int $postId, int $tenantId): ?Post
    {
        return Post::where('id', $postId)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Get a post by slug.
     */
    public function getPostBySlug(string $slug, int $siteId, int $tenantId): ?Post
    {
        return Post::where('slug', $slug)
            ->where('site_id', $siteId)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Create a new post.
     */
    public function createPost(array $data): Post
    {
        if (empty($data['slug'])) {
            $data['slug'] = Post::generateSlug($data['title']);
        }

        return DB::transaction(function () use ($data) {
            return Post::create($data);
        });
    }

    /**
     * Update a post.
     */
    public function updatePost(Post $post, array $data): Post
    {
        return DB::transaction(function () use ($post, $data) {
            $post->update($data);
            return $post->fresh();
        });
    }

    /**
     * Delete a post.
     */
    public function deletePost(Post $post): bool
    {
        return $post->delete();
    }

    /**
     * Publish a post.
     */
    public function publishPost(Post $post): Post
    {
        return $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]) ? $post->fresh() : null;
    }

    /**
     * Schedule a post for future publication.
     */
    public function schedulePost(Post $post, string $publishAt): Post
    {
        return $post->update([
            'status' => 'scheduled',
            'published_at' => $publishAt,
        ]) ? $post->fresh() : null;
    }

    /**
     * Unpublish a post.
     */
    public function unpublishPost(Post $post): Post
    {
        return $post->update(['status' => 'draft']) ? $post->fresh() : null;
    }

    /**
     * Record a view.
     */
    public function recordView(Post $post): void
    {
        $post->recordView();
    }

    /**
     * Get unique slug.
     */
    public function getUniqueSlug(string $title, int $siteId, int $tenantId): string
    {
        $baseSlug = Post::generateSlug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (Post::where('slug', $slug)
            ->where('site_id', $siteId)
            ->where('tenant_id', $tenantId)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get recent published posts.
     */
    public function getRecentPosts(int $siteId, int $tenantId, int $limit = 5): \Illuminate\Database\Eloquent\Collection
    {
        return Post::where('site_id', $siteId)
            ->where('tenant_id', $tenantId)
            ->published()
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
