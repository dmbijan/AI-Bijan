<?php

namespace App\Modules\Site\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Site\Services\SiteService;
use App\Modules\Site\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SiteController extends Controller
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
    protected SiteService $siteService;

    public function __construct(SiteService $siteService)
    {
        $this->siteService = $siteService;
    }

    /**
     * List all sites for the tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $sites = $this->siteService->getSites($this->tenantId());
        
        return response()->json([
            'success' => true,
            'data' => $sites,
        ]);
    }

    /**
     * Get a single site.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $site = $this->siteService->getSiteById($id, $this->tenantId());

        if (!$site) {
            return response()->json([
                'success' => false,
                'message' => 'Site not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $site,
        ]);
    }

    /**
     * Get site by slug.
     */
    public function getBySlug(Request $request, string $slug): JsonResponse
    {
        $site = $this->siteService->getSiteBySlug($slug, $this->tenantId());

        if (!$site) {
            return response()->json([
                'success' => false,
                'message' => 'Site not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $site,
        ]);
    }

    /**
     * Create a new site.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:sites,slug',
            'description' => 'nullable|string',
            'logo_url' => 'nullable|string|max:500',
            'favicon_url' => 'nullable|string|max:500',
            'primary_domain' => 'nullable|string|max:255',
            'secondary_domains' => 'nullable|array',
            'theme' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:draft,published,archived',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'language' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:100',
        ]);

        $validated['tenant_id'] = $this->tenantId();

        $site = $this->siteService->createSite($validated);

        return response()->json([
            'success' => true,
            'message' => 'Site created successfully.',
            'data' => $site,
        ], 201);
    }

    /**
     * Update a site.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $site = $this->siteService->getSiteById($id, $this->tenantId());

        if (!$site) {
            return response()->json([
                'success' => false,
                'message' => 'Site not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:sites,slug,' . $id,
            'description' => 'nullable|string',
            'logo_url' => 'nullable|string|max:500',
            'favicon_url' => 'nullable|string|max:500',
            'primary_domain' => 'nullable|string|max:255',
            'secondary_domains' => 'nullable|array',
            'theme' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:draft,published,archived',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string',
            'language' => 'nullable|string|max:10',
            'timezone' => 'nullable|string|max:100',
        ]);

        $site = $this->siteService->updateSite($site, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Site updated successfully.',
            'data' => $site,
        ]);
    }

    /**
     * Delete a site.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $site = $this->siteService->getSiteById($id, $this->tenantId());

        if (!$site) {
            return response()->json([
                'success' => false,
                'message' => 'Site not found.',
            ], 404);
        }

        $this->siteService->deleteSite($site);

        return response()->json([
            'success' => true,
            'message' => 'Site deleted successfully.',
        ]);
    }

    /**
     * Toggle live status.
     */
    public function toggleLive(Request $request, int $id): JsonResponse
    {
        $site = $this->siteService->getSiteById($id, $this->tenantId());

        if (!$site) {
            return response()->json([
                'success' => false,
                'message' => 'Site not found.',
            ], 404);
        }

        $site = $this->siteService->toggleLive($site);

        return response()->json([
            'success' => true,
            'message' => 'Site live status updated.',
            'data' => $site,
        ]);
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggleMaintenance(Request $request, int $id): JsonResponse
    {
        $site = $this->siteService->getSiteById($id, $this->tenantId());

        if (!$site) {
            return response()->json([
                'success' => false,
                'message' => 'Site not found.',
            ], 404);
        }

        $site = $this->siteService->toggleMaintenance($site);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance mode updated.',
            'data' => $site,
        ]);
    }

    /**
     * Set primary domain.
     */
    public function setPrimaryDomain(Request $request, int $id): JsonResponse
    {
        $site = $this->siteService->getSiteById($id, $this->tenantId());

        if (!$site) {
            return response()->json([
                'success' => false,
                'message' => 'Site not found.',
            ], 404);
        }

        $validated = $request->validate([
            'domain' => 'required|string|max:255',
        ]);

        $site = $this->siteService->setPrimaryDomain($site, $validated['domain']);

        return response()->json([
            'success' => true,
            'message' => 'Primary domain updated.',
            'data' => $site,
        ]);
    }
}
