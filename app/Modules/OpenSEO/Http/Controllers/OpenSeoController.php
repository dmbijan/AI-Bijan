<?php

namespace App\Modules\OpenSEO\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\OpenSEO\Models\SeoBacklink;
use App\Modules\OpenSEO\Models\SeoKeywordResearch;
use App\Modules\OpenSEO\Models\SeoProject;
use App\Modules\OpenSEO\Models\SerpSnapshot;
use App\Modules\OpenSEO\Services\SeoIntelligenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OpenSeoController extends Controller
{
    public function __construct(private SeoIntelligenceService $intel) {}

    /**
     * Senarai projek SEO.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SeoProject::query()->orderByDesc('last_crawled_at')->orderBy('name');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * Cip projek SEO.
     */
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'domain' => ['nullable', 'string', 'max:255'],
            'base_url' => ['nullable', 'url', 'max:255'],
            'language' => ['nullable', 'string', 'max:5'],
            'country' => ['nullable', 'string', 'max:3'],
            'device' => ['nullable', 'string', 'in:desktop,mobile,tablet'],
            'status' => ['nullable', 'string', 'in:active,paused,archived'],
            'target_keywords' => ['nullable', 'array'],
            'competitors' => ['nullable', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);

        $projek = SeoProject::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Projek SEO berjaya dicipta.',
            'data' => $projek,
        ], 201);
    }

    /**
     * Kemas kini projek SEO.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $projek = SeoProject::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:120'],
            'domain' => ['nullable', 'string', 'max:255'],
            'base_url' => ['nullable', 'url', 'max:255'],
            'status' => ['nullable', 'string', 'in:active,paused,archived'],
            'device' => ['nullable', 'string', 'in:desktop,mobile,tablet'],
            'target_keywords' => ['nullable', 'array'],
            'competitors' => ['nullable', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $projek->update($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Projek SEO berjaya dikemas kini.',
            'data' => $projek->fresh(),
        ]);
    }

    /**
     * Buang projek SEO.
     */
    public function destroy(int $id): JsonResponse
    {
        SeoProject::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Projek SEO berjaya dibuang.',
        ]);
    }

    /**
     * Ringkasan prestasi projek.
     */
    public function ringkasan(int $id): JsonResponse
    {
        $projek = SeoProject::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->intel->ringkasan($projek),
        ]);
    }

    /**
     * Rekod kedudukan SERP.
     */
    public function rekodSerp(Request $request, int $id): JsonResponse
    {
        $projek = SeoProject::findOrFail($id);

        $v = Validator::make($request->all(), [
            'keyword' => ['required', 'string', 'max:180'],
            'position' => ['nullable', 'integer', 'min:0', 'max:100'],
            'url' => ['nullable', 'url', 'max:500'],
            'title' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:500'],
            'search_volume' => ['nullable', 'integer', 'min:0'],
            'difficulty' => ['nullable', 'integer', 'min:0', 'max:100'],
            'device' => ['nullable', 'string', 'in:desktop,mobile,tablet'],
            'country' => ['nullable', 'string', 'max:3'],
            'serp_features' => ['nullable', 'array'],
            'competitors' => ['nullable', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $snapshot = $this->intel->rekodSerp($projek, $v->validated()['keyword'], $v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Kedudukan SERP berjaya direkod.',
            'data' => $snapshot,
        ], 201);
    }

    /**
     * Sejarah SERP projek.
     */
    public function serp(Request $request, int $id): JsonResponse
    {
        $projek = SeoProject::findOrFail($id);

        $query = SerpSnapshot::query()->where('seo_project_id', $projek->id);

        if ($request->filled('keyword')) {
            $query->where('keyword', $request->input('keyword'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('checked_at')
                ->limit(min(500, (int) $request->input('limit', 100)))
                ->get(),
        ]);
    }

    /**
     * Senarai kata kunci dan skor keutamaan.
     */
    public function kataKunci(Request $request, int $id): JsonResponse
    {
        $projek = SeoProject::findOrFail($id);

        $query = SeoKeywordResearch::query()->where('seo_project_id', $projek->id);

        if ($request->filled('content_gap')) {
            $query->where('content_gap', $request->boolean('content_gap'));
        }

        if ($request->filled('intent')) {
            $query->where('intent', $request->input('intent'));
        }

        if ($request->filled('sort')) {
            $query->orderByDesc($request->input('sort'));
        } else {
            $query->orderByDesc('priority_score');
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit(min(1000, (int) $request->input('limit', 200)))->get(),
        ]);
    }

    /**
     * Simpan kata kunci hasil penyelidikan.
     */
    public function simpanKataKunci(Request $request, int $id): JsonResponse
    {
        $projek = SeoProject::findOrFail($id);

        $v = Validator::make($request->all(), [
            'keyword' => ['required', 'string', 'max:180'],
            'intent' => ['nullable', 'string', 'in:informational,commercial,transactional,navigational'],
            'search_volume' => ['nullable', 'integer', 'min:0'],
            'difficulty' => ['nullable', 'integer', 'min:0', 'max:100'],
            'cpc' => ['nullable', 'numeric', 'min:0'],
            'competition' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $kataKunci = $this->intel->simpanKataKunci($projek, $v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Kata kunci berjaya disimpan.',
            'data' => $kataKunci,
        ], 201);
    }

    /**
     * Analisis celah kandungan vs pesaing.
     */
    public function analisisCelah(int $id): JsonResponse
    {
        $projek = SeoProject::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->intel->analisisCelah($projek),
        ]);
    }

    /**
     * Senarai backlink.
     */
    public function backlink(Request $request, int $id): JsonResponse
    {
        $projek = SeoProject::findOrFail($id);

        $query = SeoBacklink::query()->where('seo_project_id', $projek->id);

        if ($request->filled('is_lost')) {
            $query->where('is_lost', $request->boolean('is_lost'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('domain_authority')
                ->limit(min(1000, (int) $request->input('limit', 200)))
                ->get(),
        ]);
    }

    /**
     * Rekod backlink baharu.
     */
    public function simpanBacklink(Request $request, int $id): JsonResponse
    {
        $projek = SeoProject::findOrFail($id);

        $v = Validator::make($request->all(), [
            'source_url' => ['required', 'url', 'max:500'],
            'source_domain' => ['nullable', 'string', 'max:255'],
            'source_title' => ['nullable', 'string', 'max:300'],
            'target_url' => ['nullable', 'url', 'max:500'],
            'anchor_text' => ['nullable', 'string', 'max:200'],
            'domain_authority' => ['nullable', 'integer', 'min:0', 'max:100'],
            'page_authority' => ['nullable', 'integer', 'min:0', 'max:100'],
            'link_type' => ['nullable', 'string', 'in:text,image,video'],
            'is_dofollow' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $backlink = $this->intel->rekodBacklink($projek, $v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Backlink berjaya direkod.',
            'data' => $backlink,
        ], 201);
    }

    /**
     * Audit teknikal URL.
     */
    public function audit(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'url' => ['required', 'url', 'max:500'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        return response()->json([
            'success' => true,
            'data' => $this->intel->auditUrl($v->validated()['url']),
        ]);
    }

    /**
     * @param  \Illuminate\Contracts\Validation\Validator  $v
     */
    protected function ralatValidasi($v): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Input tidak sah.',
            'errors' => $v->errors(),
        ], 422);
    }
}
