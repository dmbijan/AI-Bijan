<?php

namespace App\Modules\ContentForge\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ContentForge\Models\ContentDocument;
use App\Modules\ContentForge\Models\ContentSite;
use App\Modules\ContentForge\Services\ContentForgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContentForgeController extends Controller
{
    public function __construct(private ContentForgeService $forge) {}

    /**
     * Senarai dokumen.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ContentDocument::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 50))->get(),
            'meta' => [
                'jenis' => config('content_forge.types'),
                'bahasa' => config('content_forge.languages'),
            ],
        ]);
    }

    /**
     * Cipta dokumen.
     */
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:250'],
            'type' => ['nullable', 'string', 'in:article,product_roundup,product_review,how_to,product_listing,landing'],
            'language' => ['nullable', 'string', 'max:5'],
            'brief' => ['nullable', 'string', 'max:5000'],
            'keywords' => ['nullable', 'array', 'max:'.config('content_forge.limits.max_keywords', 20)],
            'keywords.*' => ['string', 'max:120'],
            'target_word_count' => ['nullable', 'integer', 'min:300', 'max:'.config('content_forge.limits.max_word_count', 5000)],
            'meta_description' => ['nullable', 'string', 'max:300'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $dokumen = $this->forge->ciptaDokumen($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Dokumen dicipta.',
            'data' => $dokumen,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ContentDocument::findOrFail($id),
        ]);
    }

    /**
     * Kemas kini dokumen.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $dokumen = ContentDocument::findOrFail($id);

        $v = Validator::make($request->all(), [
            'title' => ['sometimes', 'string', 'max:250'],
            'body_html' => ['nullable', 'string'],
            'body_markdown' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'meta_title' => ['nullable', 'string', 'max:250'],
            'keywords' => ['nullable', 'array'],
            'status' => ['nullable', 'string', 'in:draft,generating,generated,optimizing,ready,published,failed'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $dokumen->update($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Dokumen dikemas kini.',
            'data' => $dokumen->fresh(),
        ]);
    }

    /**
     * Jalankan pemeriksa SEO.
     */
    public function scoreSeo(int $id): JsonResponse
    {
        $dokumen = ContentDocument::findOrFail($id);
        $hasil = $this->forge->skorSeo($dokumen);

        return response()->json([
            'success' => true,
            'data' => [
                'score' => $hasil['score'],
                'issues' => $hasil['issues'],
                'document' => $dokumen->fresh(),
            ],
        ]);
    }

    /**
     * Analisis SERP.
     */
    public function analyzeSerp(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'keywords' => ['required', 'array', 'min:1'],
            'keywords.*' => ['string', 'max:120'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        return response()->json([
            'success' => true,
            'data' => $this->forge->analisisSerp($v->validated()['keywords']),
        ]);
    }

    /**
     * Jana rangka (outline).
     */
    public function outline(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:250'],
            'keywords' => ['nullable', 'array'],
            'type' => ['nullable', 'string', 'max:40'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();

        return response()->json([
            'success' => true,
            'data' => $this->forge->janaRangka(
                $data['title'],
                $data['keywords'] ?? [],
                $data['type'] ?? 'article'
            ),
        ]);
    }

    /**
     * Terbitkan ke WordPress.
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'site_id' => ['required', 'integer', 'exists:content_sites,id'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $dokumen = ContentDocument::findOrFail($id);
        $laman = ContentSite::findOrFail($v->validated()['site_id']);

        $hasil = $this->forge->terbitkan($dokumen, $laman);

        return response()->json([
            'success' => $hasil['success'],
            'message' => $hasil['mesej'],
            'data' => ['url' => $hasil['url']],
        ], $hasil['success'] ? 200 : 422);
    }

    /**
     * Senarai laman WordPress.
     */
    public function sites(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ContentSite::query()->orderBy('name')->get(),
        ]);
    }

    /**
     * Sambung laman WordPress.
     */
    public function storeSite(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'url' => ['required', 'url', 'max:255'],
            'username' => ['required', 'string', 'max:120'],
            'app_password' => ['required', 'string', 'max:255'],
            'auto_publish' => ['nullable', 'boolean'],
            'default_category' => ['nullable', 'string', 'max:120'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $laman = ContentSite::create(array_merge($v->validated(), [
            'status' => ContentSite::STATUS_CONNECTED,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Laman WordPress disambungkan.',
            'data' => $laman,
        ], 201);
    }

    /**
     * Ringkasan.
     */
    public function summary(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->forge->ringkasan(),
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
