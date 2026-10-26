<?php

namespace App\Modules\Site\Services;

use App\Modules\Site\Models\Page;
use Illuminate\Support\Facades\DB;

class PageService
{
    /**
     * Get all pages for a site.
     */
    public function getPages(int $siteId, int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Page::where('site_id', $siteId)
            ->where('tenant_id', $tenantId)
            ->orderBy('title')
            ->get();
    }

    /**
     * Get a page by ID.
     */
    public function getPageById(int $pageId, int $tenantId): ?Page
    {
        return Page::where('id', $pageId)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Get a page by slug.
     */
    public function getPageBySlug(string $slug, int $siteId, int $tenantId): ?Page
    {
        return Page::where('slug', $slug)
            ->where('site_id', $siteId)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Create a new page.
     */
    public function createPage(array $data): Page
    {
        if (empty($data['slug'])) {
            $data['slug'] = Page::generateSlug($data['title']);
        }

        return DB::transaction(function () use ($data) {
            return Page::create($data);
        });
    }

    /**
     * Update a page.
     */
    public function updatePage(Page $page, array $data): Page
    {
        return DB::transaction(function () use ($page, $data) {
            $page->update($data);
            return $page->fresh();
        });
    }

    /**
     * Delete a page.
     */
    public function deletePage(Page $page): bool
    {
        return $page->delete();
    }

    /**
     * Publish a page.
     */
    public function publishPage(Page $page): Page
    {
        return $page->update([
            'status' => 'published',
            'published_at' => now(),
        ]) ? $page->fresh() : null;
    }

    /**
     * Unpublish a page.
     */
    public function unpublishPage(Page $page): Page
    {
        return $page->update(['status' => 'draft']) ? $page->fresh() : null;
    }

    /**
     * Duplicate a page.
     */
    public function duplicatePage(Page $page): Page
    {
        return DB::transaction(function () use ($page) {
            $newPage = $page->replicate();
            $newPage->title = $page->title . ' (Copy)';
            $newPage->slug = Page::generateSlug($newPage->title) . '-copy';
            $newPage->status = 'draft';
            $newPage->save();
            return $newPage;
        });
    }

    /**
     * Reorder pages.
     */
    public function reorderPages(int $siteId, int $tenantId, array $pageOrder): bool
    {
        return DB::transaction(function () use ($siteId, $tenantId, $pageOrder) {
            foreach ($pageOrder as $index => $pageId) {
                Page::where('id', $pageId)
                    ->where('site_id', $siteId)
                    ->where('tenant_id', $tenantId)
                    ->update(['order' => $index]);
            }
            return true;
        });
    }

    /**
     * Get unique slug.
     */
    public function getUniqueSlug(string $title, int $siteId, int $tenantId): string
    {
        $baseSlug = Page::generateSlug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (Page::where('slug', $slug)
            ->where('site_id', $siteId)
            ->where('tenant_id', $tenantId)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
