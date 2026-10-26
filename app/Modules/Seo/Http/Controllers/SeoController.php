<?php

namespace App\Modules\Seo\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Seo\Models\SeoAuditIssue;
use App\Modules\Seo\Models\SeoKeyword;
use App\Modules\Seo\Models\SeoMetadata;
use App\Modules\Seo\Models\SeoSuggestion;
use App\Modules\Seo\Services\SeoService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function __construct(private SeoService $seo) {}

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    /**
     * Profil SEO tenant.
     */
    public function profil(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->seo->profil($this->tenantId()),
        ]);
    }

    /**
     * Kemas kini profil SEO.
     */
    public function kemasProfil(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'site_name' => ['nullable', 'string', 'max:180'],
            'site_url' => ['nullable', 'url', 'max:255'],
            'default_title_template' => ['nullable', 'string', 'max:255'],
            'default_description_template' => ['nullable', 'string', 'max:500'],
            'default_keyword' => ['nullable', 'string', 'max:255'],
            'og_image_url' => ['nullable', 'url', 'max:500'],
            'twitter_handle' => ['nullable', 'string', 'max:80'],
            'twitter_card_type' => ['nullable', 'in:summary,summary_large_image,app,player'],
            'robots_txt' => ['nullable', 'string', 'max:5000'],
            'google_site_verification' => ['nullable', 'string', 'max:255'],
            'bing_site_verification' => ['nullable', 'string', 'max:255'],
            'locale' => ['nullable', 'string', 'max:20'],
            'timezone' => ['nullable', 'string', 'max:60'],
            'auto_sitemap' => ['nullable', 'boolean'],
            'auto_robots' => ['nullable', 'boolean'],
            'is_indexable' => ['nullable', 'boolean'],
        ]);

        $profil = $this->seo->profil($this->tenantId());
        $profil->update($validated);

        return response()->json(['success' => true, 'data' => $profil->fresh()]);
    }

    /**
     * Senarai metadata.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SeoMetadata::query()->orderBy('object_type')->orderByDesc('seo_score');

        if ($request->filled('object_type')) {
            $query->where('object_type', $request->input('object_type'));
        }

        if ($request->filled('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        return response()->json(['success' => true, 'data' => $query->limit(200)->get()]);
    }

    /**
     * Simpan metadata objek.
     */
    public function simpan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'object_type' => ['required', 'in:product,page,article,video,category,blog_post'],
            'object_id' => ['required', 'integer', 'min:1'],
            'slug' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'canonical_url' => ['nullable', 'url', 'max:500'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:1000'],
            'og_image_url' => ['nullable', 'url', 'max:500'],
            'og_type' => ['nullable', 'in:website,product,article,video,profile'],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string', 'max:1000'],
            'twitter_image_url' => ['nullable', 'url', 'max:500'],
            'json_ld' => ['nullable', 'array'],
            'hreflang' => ['nullable', 'array'],
            'no_index' => ['nullable', 'boolean'],
            'no_follow' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $metadata = $this->seo->simpanMetadata(
            $this->tenantId(),
            $validated['object_type'],
            $validated['object_id'],
            $validated
        );

        return response()->json(['success' => true, 'data' => $metadata], 201);
    }

    /**
     * Butiran metadata + preview SERP.
     */
    public function show(Request $request, string $type, int $id): JsonResponse
    {
        $metadata = $this->seo->metadata($this->tenantId(), $type, $id);

        if (! $metadata) {
            return response()->json(['success' => false, 'message' => 'Metadata tidak dijumpai'], 404);
        }

        $metadata->load(['issues', 'suggestions']);

        return response()->json([
            'success' => true,
            'data' => $metadata,
            'preview' => $this->seo->previewSerp($metadata),
        ]);
    }

    /**
     * Audit metadata objek.
     */
    public function audit(Request $request, string $type, int $id): JsonResponse
    {
        $metadata = $this->seo->metadata($this->tenantId(), $type, $id);

        if (! $metadata) {
            return response()->json(['success' => false, 'message' => 'Metadata tidak dijumpai'], 404);
        }

        $isu = $this->seo->auditMetadata($this->tenantId(), $metadata);

        return response()->json([
            'success' => true,
            'data' => [
                'isu' => $isu,
                'jumlah' => count($isu),
                'skor' => (float) $metadata->fresh()->seo_score,
            ],
        ]);
    }

    /**
     * Audit seluruh tenant.
     */
    public function auditSemua(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->seo->auditTenant($this->tenantId()),
        ]);
    }

    /**
     * Jana cadangan AI untuk objek.
     */
    public function janaCadangan(Request $request, string $type, int $id): JsonResponse
    {
        $metadata = $this->seo->metadata($this->tenantId(), $type, $id);

        if (! $metadata) {
            return response()->json(['success' => false, 'message' => 'Metadata tidak dijumpai'], 404);
        }

        $validated = $request->validate([
            'content' => ['nullable', 'string', 'max:5000'],
            'heuristik' => ['nullable', 'boolean'],
        ]);

        $cadangan = $validated['heuristik'] ?? false
            ? $this->seo->janaCadanganHeuristik($this->tenantId(), $metadata)
            : $this->seo->janaCadanganAI($this->tenantId(), $metadata, $validated);

        return response()->json(['success' => true, 'data' => $cadangan]);
    }

    /**
     * Senarai cadangan.
     */
    public function cadangan(Request $request): JsonResponse
    {
        $query = SeoSuggestion::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('confidence');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json(['success' => true, 'data' => $query->limit(100)->get()]);
    }

    /**
     * Terima cadangan.
     */
    public function terimaCadangan(int $id): JsonResponse
    {
        $metadata = $this->seo->terimaCadangan($this->tenantId(), $id);

        return response()->json(['success' => true, 'data' => $metadata]);
    }

    /**
     * Tolak cadangan.
     */
    public function tolakCadangan(int $id): JsonResponse
    {
        $cadangan = $this->seo->tolakCadangan($this->tenantId(), $id);

        return response()->json(['success' => true, 'data' => $cadangan]);
    }

    /**
     * Senarai isu SEO.
     */
    public function isu(Request $request): JsonResponse
    {
        $query = SeoAuditIssue::query()
            ->where('tenant_id', $this->tenantId())
            ->where('is_resolved', false)
            ->orderBy('severity');

        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        if ($request->filled('object_type')) {
            $query->where('object_type', $request->input('object_type'));
        }

        return response()->json(['success' => true, 'data' => $query->limit(200)->get()]);
    }

    /**
     * Tanda isu selesai.
     */
    public function selesaikanIsu(int $id): JsonResponse
    {
        $isu = SeoAuditIssue::query()
            ->where('tenant_id', $this->tenantId())
            ->findOrFail($id);

        $isu->update(['is_resolved' => true, 'resolved_at' => now()]);

        return response()->json(['success' => true, 'data' => $isu->fresh()]);
    }

    /**
     * Sitemap XML.
     */
    public function sitemap(Request $request): Response
    {
        $xml = $this->seo->janaSitemap(
            $this->tenantId(),
            $request->input('object_type')
        );

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    /**
     * robots.txt dinamik.
     */
    public function robots(): Response
    {
        $profil = $this->seo->profil($this->tenantId());

        return response($profil->robotsLalai(), 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * Jana JSON-LD.
     */
    public function jsonLd(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'object_type' => ['required', 'in:product,page,article,video,category,blog_post'],
            'data' => ['required', 'array'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->seo->janaJsonLd($validated['object_type'], $validated['data']),
        ]);
    }

    /**
     * Kata kunci.
     */
    public function kataKunci(Request $request): JsonResponse
    {
        $query = SeoKeyword::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('search_volume');

        if ($request->filled('intent')) {
            $query->where('intent', $request->input('intent'));
        }

        return response()->json(['success' => true, 'data' => $query->limit(200)->get()]);
    }

    /**
     * Simpan kata kunci.
     */
    public function simpanKataKunci(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'keyword' => ['required', 'string', 'max:180'],
            'intent' => ['nullable', 'in:informational,commercial,transactional,navigational'],
            'source' => ['nullable', 'in:manual,ai,analytics,competitor'],
            'search_volume' => ['nullable', 'integer', 'min:0'],
            'difficulty' => ['nullable', 'integer', 'min:0', 'max:100'],
            'current_rank' => ['nullable', 'numeric', 'min:0'],
            'target_url' => ['nullable', 'url', 'max:500'],
            'is_tracked' => ['nullable', 'boolean'],
            'related_keywords' => ['nullable', 'array'],
        ]);

        $keyword = SeoKeyword::updateOrCreate(
            ['tenant_id' => $this->tenantId(), 'keyword' => $validated['keyword']],
            array_merge($validated, ['tenant_id' => $this->tenantId()])
        );

        return response()->json(['success' => true, 'data' => $keyword], 201);
    }
}
