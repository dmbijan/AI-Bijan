<?php

namespace App\Modules\MediaEditor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\MediaEditor\Models\MediaProject;
use App\Modules\MediaEditor\Models\MediaRenderJob;
use App\Modules\MediaEditor\Models\MediaTemplate;
use App\Modules\MediaEditor\Services\MediaEditorService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MediaEditorController extends Controller
{
    public function __construct(private MediaEditorService $editor) {}

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    public function resolutions(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => MediaEditorService::RESOLUTIONS]);
    }

    public function layerTypes(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => MediaEditorService::LAYER_TYPES]);
    }

    public function templates(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => MediaTemplate::query()
                ->where(function ($q) {
                    $q->whereNull('tenant_id')->orWhere('tenant_id', $this->tenantId());
                })
                ->where('is_active', true)
                ->orderByDesc('is_official')
                ->orderByDesc('use_count')
                ->get(),
        ]);
    }

    public function useTemplate(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['name' => ['nullable', 'string', 'max:180']]);

        $projek = $this->editor->gunaTemplate($this->tenantId(), $id, $validated['name'] ?? null);

        return response()->json(['success' => true, 'data' => $projek], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $query = MediaProject::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('created_at');

        if ($request->filled('project_type')) {
            $query->where('project_type', $request->input('project_type'));
        }

        return response()->json(['success' => true, 'data' => $query->limit(100)->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'project_type' => ['nullable', 'in:image,video,carousel,story'],
            'canvas' => ['nullable', 'array'],
            'resolution' => ['nullable', 'string', 'max:20'],
        ]);

        $projek = $this->editor->ciptaProjek($this->tenantId(), $validated);

        return response()->json(['success' => true, 'data' => $projek], 201);
    }

    public function show(int $id): JsonResponse
    {
        $projek = MediaProject::query()
            ->with('renderJobs')
            ->where('tenant_id', $this->tenantId())
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $projek]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:180'],
            'canvas' => ['nullable', 'array'],
            'layers' => ['nullable', 'array'],
            'timeline' => ['nullable', 'array'],
            'brand_kit' => ['nullable', 'array'],
            'resolution' => ['nullable', 'string', 'max:20'],
        ]);

        $projek = $this->editor->kemasProjek($this->tenantId(), $id, $validated);

        return response()->json(['success' => true, 'data' => $projek]);
    }

    public function tambahLapisan(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:text,image,video,shape,sticker,group,audio'],
            'x' => ['required', 'numeric'],
            'y' => ['required', 'numeric'],
            'text' => ['nullable', 'string', 'max:2000'],
            'src' => ['nullable', 'string', 'max:1000'],
            'width' => ['nullable', 'numeric'],
            'height' => ['nullable', 'numeric'],
            'opacity' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'rotation' => ['nullable', 'numeric'],
        ]);

        $projek = $this->editor->tambahLapisan($this->tenantId(), $id, $validated);

        return response()->json(['success' => true, 'data' => $projek], 201);
    }

    public function buangLapisan(int $id, int $index): JsonResponse
    {
        $projek = $this->editor->buangLapisan($this->tenantId(), $id, $index);

        return response()->json(['success' => true, 'data' => $projek]);
    }

    public function render(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'output_format' => ['nullable', 'in:mp4,webm,png,jpg,gif'],
            'resolution' => ['nullable', 'string', 'max:20'],
        ]);

        $job = $this->editor->janaRender($this->tenantId(), $id, $validated);

        return response()->json(['success' => true, 'data' => $job], 201);
    }

    public function jobs(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => MediaRenderJob::query()
                ->where('tenant_id', $this->tenantId())
                ->where('media_project_id', $id)
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    public function ringkasan(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->editor->ringkasan($this->tenantId()),
        ]);
    }
}
