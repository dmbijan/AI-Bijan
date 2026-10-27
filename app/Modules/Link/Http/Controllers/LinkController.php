<?php

namespace App\Modules\Link\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Link\Services\LinkService;
use App\Modules\Link\Models\LinkPage;
use Illuminate\Http\Request;

class LinkController extends Controller
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
    protected $service;

    public function __construct(LinkService $service)
    {
        $this->service = $service;
    }

    /**
     * Display link pages.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'search']);
        $links = $this->service->getLinkPages($this->tenantId(), $filters);

        return response()->json([
            'success' => true,
            'data' => $links,
        ]);
    }

    /**
     * Store a new link page.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:link_pages,slug',
            'description' => 'nullable|string',
            'type' => 'nullable|in:page,campaign,landing,event',
            'content' => 'nullable|array',
            'redirect_url' => 'nullable|url',
            'thumbnail' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'custom_js' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'password_protected' => 'nullable|boolean',
            'password_hash' => 'nullable|string',
        ]);

        $validated['short_code'] = LinkService::generateShortCode();
        $validated['tenant_id'] = $this->tenantId();
        $validated['status'] = 'active';
        $validated['views_count'] = 0;
        $validated['unique_views_count'] = 0;
        $validated['conversions_count'] = 0;

        $link = LinkPage::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Link page created',
            'data' => $link,
        ], 201);
    }

    /**
     * Display a specific link page.
     */
    public function show(LinkPage $linkPage)
    {
        if ($linkPage->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $linkPage->load('clicks'),
        ]);
    }

    /**
     * Update the link page.
     */
    public function update(Request $request, LinkPage $linkPage)
    {
        if ($linkPage->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:link_pages,slug,' . $linkPage->id,
            'description' => 'nullable|string',
            'type' => 'sometimes|in:page,campaign,landing,event',
            'content' => 'nullable|array',
            'redirect_url' => 'nullable|url',
            'thumbnail' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'custom_css' => 'nullable|string',
            'custom_js' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'status' => 'sometimes|in:active,inactive,pending',
        ]);

        $linkPage->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Link page updated',
            'data' => $linkPage,
        ]);
    }

    /**
     * Track a click on link.
     */
    public function trackClick(string $code, Request $request)
    {
        try {
            $click = $this->service->trackClick($code, $this->tenantId(), [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer_url' => $request->header('referer'),
                'country' => $request->header('cf-ipcountry') ?? null,
                'region' => null,
                'city' => null,
                'device' => $request->isMobile() ? 'mobile' : 'desktop',
                'browser' => $request->browser(),
                'os' => $request->userAgent(),
            ]);

            // Increment views
            $linkPage = $click->linkPage;
            if ($linkPage) {
                $linkPage->incrementViews();
            }

            return response()->json([
                'success' => true,
                'data' => $click,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get link analytics.
     */
    public function getAnalytics(Request $request)
    {
        $analytics = $this->service->getLinkAnalytics($this->tenantId());

        return response()->json([
            'success' => true,
            'data' => $analytics,
        ]);
    }

    /**
     * Delete a link page.
     */
    public function destroy(LinkPage $linkPage)
    {
        if ($linkPage->tenant_id !== request()->tenant_id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $linkPage->delete();

        return response()->json([
            'success' => true,
            'message' => 'Link page deleted',
        ]);
    }
}
