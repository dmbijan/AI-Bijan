<?php

namespace App\Modules\Site\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Site\Services\PageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    protected PageService $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    /**
     * List all pages for a site.
     */
    public function index(Request $request, int $siteId): JsonResponse
    {
        $pages = $this->pageService->getPages($siteId, $request->tenant_id);
        
        return response()->json([
            'success' => true,
            'data' => $pages,
        ]);
    }

    /**
     * Get a single page.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $page = $this->pageService->getPageById($id, $request->tenant_id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $page,
        ]);
    }

    /**
     * Get page by slug.
     */
    public function getBySlug(Request $request, int $siteId, string $slug): JsonResponse
    {
        $page = $this->pageService->getPageBySlug($slug, $siteId, $request->tenant_id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $page,
        ]);
    }

    /**
     * Create a new page.
     */
    public function store(Request $request, int $siteId): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'template' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:draft,published,scheduled',
            'content' => 'nullable|string',
            'sections' => 'nullable|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'author_id' => 'nullable|integer',
            'published_at' => 'nullable|date',
        ]);

        $validated['tenant_id'] = $request->tenant_id;
        $validated['site_id'] = $siteId;

        $page = $this->pageService->createPage($validated);

        return response()->json([
            'success' => true,
            'message' => 'Page created successfully.',
            'data' => $page,
        ], 201);
    }

    /**
     * Update a page.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $page = $this->pageService->getPageById($id, $request->tenant_id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255',
            'template' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:draft,published,scheduled',
            'content' => 'nullable|string',
            'sections' => 'nullable|array',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'author_id' => 'nullable|integer',
            'published_at' => 'nullable|date',
        ]);

        $page = $this->pageService->updatePage($page, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Page updated successfully.',
            'data' => $page,
        ]);
    }

    /**
     * Delete a page.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $page = $this->pageService->getPageById($id, $request->tenant_id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], 404);
        }

        $this->pageService->deletePage($page);

        return response()->json([
            'success' => true,
            'message' => 'Page deleted successfully.',
        ]);
    }

    /**
     * Publish a page.
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        $page = $this->pageService->getPageById($id, $request->tenant_id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], 404);
        }

        $page = $this->pageService->publishPage($page);

        return response()->json([
            'success' => true,
            'message' => 'Page published successfully.',
            'data' => $page,
        ]);
    }

    /**
     * Unpublish a page.
     */
    public function unpublish(Request $request, int $id): JsonResponse
    {
        $page = $this->pageService->getPageById($id, $request->tenant_id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], 404);
        }

        $page = $this->pageService->unpublishPage($page);

        return response()->json([
            'success' => true,
            'message' => 'Page unpublished successfully.',
            'data' => $page,
        ]);
    }

    /**
     * Duplicate a page.
     */
    public function duplicate(Request $request, int $id): JsonResponse
    {
        $page = $this->pageService->getPageById($id, $request->tenant_id);

        if (!$page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found.',
            ], 404);
        }

        $newPage = $this->pageService->duplicatePage($page);

        return response()->json([
            'success' => true,
            'message' => 'Page duplicated successfully.',
            'data' => $newPage,
        ], 201);
    }
}
