<?php

namespace App\Modules\OpenGenAI\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\OpenGenAI\Models\GenAiJob;
use App\Modules\OpenGenAI\Models\GenAiProvider;
use App\Modules\OpenGenAI\Services\GenAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OpenGenAIController extends Controller
{
    public function __construct(private GenAiService $genai) {}

    /**
     * Senarai provider tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $provider = GenAiProvider::query()
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get()
            ->map(fn (GenAiProvider $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'kind' => $p->kind,
                'kind_label' => GenAiService::jenisProvider()[$p->kind] ?? $p->kind,
                'base_url' => $p->base_url,
                'api_key_masked' => $p->apiKeyMasked(),
                'default_model' => $p->default_model,
                'capabilities' => $p->capabilities ?? [],
                'is_active' => $p->is_active,
                'is_default' => $p->is_default,
                'last_error' => $p->last_error,
                'last_checked_at' => $p->last_checked_at?->toIso8601String(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $provider,
            'meta' => [
                'kinds' => GenAiService::jenisProvider(),
                'capabilities' => GenAiService::senaraiKemahiran(),
            ],
        ]);
    }

    /**
     * Tambah provider baharu.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validasi($request);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        // Kunci API disulitkan oleh mutator `setApiKeyAttribute` pada model.

        if (! empty($data['is_default'])) {
            GenAiProvider::query()->update(['is_default' => false]);
        }

        $provider = GenAiProvider::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Provider AI berjaya ditambah.',
            'data' => $this->bentuk($provider),
        ], 201);
    }

    /**
     * Kemas kini provider.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $provider = GenAiProvider::findOrFail($id);
        $data = $this->validasi($request, true);

        // Kunci API tidak dihantar semula = kekal yang sedia ada.
        if (array_key_exists('api_key', $data) && $data['api_key'] !== null && $data['api_key'] !== '') {
            // Disulitkan oleh mutator model.
        } else {
            unset($data['api_key']);
        }

        if (! empty($data['is_default'])) {
            GenAiProvider::query()->where('id', '!=', $provider->id)->update(['is_default' => false]);
        }

        $provider->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Provider AI berjaya dikemas kini.',
            'data' => $this->bentuk($provider->fresh()),
        ]);
    }

    /**
     * Buang provider.
     */
    public function destroy(int $id): JsonResponse
    {
        $provider = GenAiProvider::findOrFail($id);
        $provider->delete();

        return response()->json([
            'success' => true,
            'message' => 'Provider AI berjaya dibuang.',
        ]);
    }

    /**
     * Jana kandungan dengan provider yang dipilih.
     */
    public function generate(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'prompt' => ['required', 'string', 'min:1', 'max:'.config('open_gen_ai.max_prompt_chars', 32000)],
            'capability' => ['nullable', 'string', 'in:text,image,video,audio,code'],
            'provider_id' => ['nullable', 'integer'],
            'model' => ['nullable', 'string', 'max:120'],
            'system_prompt' => ['nullable', 'string', 'max:8000'],
            'max_tokens' => ['nullable', 'integer', 'min:1', 'max:'.config('open_gen_ai.max_output_tokens', 8192)],
            'temperature' => ['nullable', 'numeric', 'min:0', 'max:2'],
            'task_type' => ['nullable', 'string', 'max:60'],
        ]);

        if ($v->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Input tidak sah.',
                'errors' => $v->errors(),
            ], 422);
        }

        $data = $v->validated();
        $kemahiran = $data['capability'] ?? GenAiJob::KEMAHRIRAN_TEXT;

        $hasil = $this->genai->jalankan($kemahiran, $data['prompt'], array_filter($data));

        if (! $hasil['success']) {
            return response()->json([
                'success' => false,
                'message' => $hasil['error'] ?? 'Penjanaan gagal.',
                'meta' => ['job_id' => $hasil['job']?->id],
            ], 502);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'job_id' => $hasil['job']->id,
                'uuid' => $hasil['job']->uuid,
                'output' => $hasil['output'],
                'url' => $hasil['url'],
                'model' => $hasil['job']->model,
                'total_tokens' => $hasil['job']->total_tokens,
                'cost_rm' => $hasil['job']->cost_rm,
                'duration_ms' => $hasil['job']->duration_ms,
            ],
        ]);
    }

    /**
     * Sejarah penjanaan.
     */
    public function jobs(Request $request): JsonResponse
    {
        $query = GenAiJob::query()->orderByDesc('created_at');

        if ($request->filled('capability')) {
            $query->where('capability', $request->input('capability'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit(min(200, (int) $request->input('limit', 50)))->get(),
        ]);
    }

    /**
     * Butiran satu job.
     */
    public function job(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => GenAiJob::findOrFail($id),
        ]);
    }

    /**
     * Ringkasan penggunaan & kos.
     */
    public function ringkasan(): JsonResponse
    {
        $statistik = GenAiJob::query()
            ->selectRaw('capability, status, COUNT(*) as jumlah, SUM(total_tokens) as token, SUM(cost_rm) as kos')
            ->groupBy('capability', 'status')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ranking' => $statistik,
                'jumlah_job' => GenAiJob::count(),
                'jumlah_kos' => round((float) GenAiJob::sum('cost_rm'), 4),
                'jumlah_token' => (int) GenAiJob::sum('total_tokens'),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validasi(Request $request, bool $update = false): array
    {
        $v = Validator::make($request->all(), [
            'name' => [$update ? 'sometimes' : 'required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120'],
            'kind' => ['nullable', 'string', 'max:40'],
            'base_url' => ['nullable', 'string', 'max:255'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'default_model' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'capabilities' => ['nullable', 'array'],
            'capabilities.*' => ['string', 'in:text,image,video,audio,code'],
        ]);

        if ($v->fails()) {
            abort(response()->json([
                'success' => false,
                'message' => 'Input tidak sah.',
                'errors' => $v->errors(),
            ], 422));
        }

        return $v->validated();
    }

    /**
     * Bentuk provider untuk output (tanpa mendedahkan kunci API penuh).
     *
     * @return array<string, mixed>
     */
    protected function bentuk(GenAiProvider $p): array
    {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'kind' => $p->kind,
            'base_url' => $p->base_url,
            'api_key_masked' => $p->apiKeyMasked(),
            'default_model' => $p->default_model,
            'capabilities' => $p->capabilities ?? [],
            'is_active' => $p->is_active,
            'is_default' => $p->is_default,
        ];
    }
}
