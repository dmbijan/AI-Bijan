<?php

namespace App\Modules\WorkManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\Project;
use App\Modules\WorkManagement\Models\ProjectBaseline;
use App\Modules\WorkManagement\Models\ProjectBudgetLine;
use App\Modules\WorkManagement\Models\ProjectPortfolio;
use App\Modules\WorkManagement\Models\ProjectTimeline;
use App\Modules\WorkManagement\Services\WorkManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkManagementController extends Controller
{
    public function __construct(private WorkManagementService $work) {}

    /**
     * Garis masa projek.
     */
    public function timeline(int $projectId): JsonResponse
    {
        $projek = Project::findOrFail($projectId);

        $aktiviti = ProjectTimeline::query()
            ->where('project_id', $projek->id)
            ->where('is_baseline', false)
            ->orderBy('sort_order')
            ->get();

        $kritikal = $this->work->laluanKritikal($projek);

        return response()->json([
            'success' => true,
            'data' => [
                'projek' => $projek,
                'aktiviti' => $aktiviti,
                'laluan_kritikal' => $kritikal,
                'lewat' => $aktiviti->filter(fn ($a) => $a->lewat())->values(),
            ],
        ]);
    }

    /**
     * Tambah aktiviti garis masa.
     */
    public function storeActivity(Request $request, int $projectId): JsonResponse
    {
        $projek = Project::findOrFail($projectId);

        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:200'],
            'type' => ['nullable', 'string', 'in:bar,gantt,boarddown'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'parent_id' => ['nullable', 'integer', 'exists:project_timelines,id'],
            'depends_on_id' => ['nullable', 'integer', 'exists:project_timelines,id'],
            'lag_days' => ['nullable', 'integer', 'min:-90', 'max:90'],
            'color' => ['nullable', 'string', 'max:20'],
            'is_milestone' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $aktiviti = $this->work->tambahAktiviti($projek, $v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Aktiviti berjaya ditambah.',
            'data' => $aktiviti,
        ], 201);
    }

    /**
     * Kemas kini aktiviti.
     */
    public function updateActivity(Request $request, int $projectId, int $activityId): JsonResponse
    {
        Project::findOrFail($projectId);
        $aktiviti = ProjectTimeline::findOrFail($activityId);

        $v = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:200'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'depends_on_id' => ['nullable', 'integer', 'exists:project_timelines,id'],
            'lag_days' => ['nullable', 'integer', 'min:-90', 'max:90'],
            'color' => ['nullable', 'string', 'max:20'],
            'is_milestone' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $aktiviti->update($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Aktiviti berjaya dikemas kini.',
            'data' => $aktiviti->fresh(),
        ]);
    }

    /**
     * Buang aktiviti.
     */
    public function destroyActivity(int $projectId, int $activityId): JsonResponse
    {
        Project::findOrFail($projectId);
        ProjectTimeline::findOrFail($activityId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Aktiviti berjaya dibuang.',
        ]);
    }

    /**
     * Tangkap garis dasar.
     */
    public function captureBaseline(Request $request, int $projectId): JsonResponse
    {
        $projek = Project::findOrFail($projectId);

        $baseline = $this->work->tangkapGarisDasar($projek, $request->input('name'));

        return response()->json([
            'success' => true,
            'message' => 'Garis dasar berjaya direkod.',
            'data' => $baseline,
        ], 201);
    }

    /**
     * Senarai garis dasar.
     */
    public function baselines(int $projectId): JsonResponse
    {
        Project::findOrFail($projectId);

        return response()->json([
            'success' => true,
            'data' => ProjectBaseline::query()
                ->where('project_id', $projectId)
                ->orderByDesc('captured_at')
                ->get(),
        ]);
    }

    /**
     * Analisis variance berbanding garis dasar.
     */
    public function variance(int $projectId): JsonResponse
    {
        $projek = Project::findOrFail($projectId);

        return response()->json([
            'success' => true,
            'data' => $this->work->analisisVariance($projek),
        ]);
    }

    /**
     * Baris bajet projek.
     */
    public function budget(int $projectId): JsonResponse
    {
        $projek = Project::findOrFail($projectId);

        return response()->json([
            'success' => true,
            'data' => [
                'baris' => ProjectBudgetLine::query()
                    ->where('project_id', $projek->id)
                    ->orderBy('sort_order')
                    ->get(),
                'ringkasan' => $this->work->ringkasanBajet($projek),
            ],
        ]);
    }

    /**
     * Tambah baris bajet.
     */
    public function storeBudgetLine(Request $request, int $projectId): JsonResponse
    {
        $projek = Project::findOrFail($projectId);

        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'in:labour,material,subcontract,software,travel,other'],
            'unit' => ['nullable', 'string', 'max:30'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'planned_cost' => ['nullable', 'numeric', 'min:0'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'spent_to_date' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $data['project_id'] = $projek->id;

        if (! isset($data['planned_cost'])) {
            $data['planned_cost'] = round((float) ($data['quantity'] ?? 1) * (float) ($data['unit_cost'] ?? 0), 2);
        }

        $baris = ProjectBudgetLine::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Baris bajet berjaya ditambah.',
            'data' => $baris,
        ], 201);
    }

    /**
     * Kemas kini baris bajet.
     */
    public function updateBudgetLine(Request $request, int $projectId, int $lineId): JsonResponse
    {
        Project::findOrFail($projectId);
        $baris = ProjectBudgetLine::findOrFail($lineId);

        $v = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:200'],
            'category' => ['nullable', 'string', 'in:labour,material,subcontract,software,travel,other'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'planned_cost' => ['nullable', 'numeric', 'min:0'],
            'actual_cost' => ['nullable', 'numeric', 'min:0'],
            'spent_to_date' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $baris->update($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Baris bajet berjaya dikemas kini.',
            'data' => $baris->fresh(),
        ]);
    }

    /**
     * Buang baris bajet.
     */
    public function destroyBudgetLine(int $projectId, int $lineId): JsonResponse
    {
        Project::findOrFail($projectId);
        ProjectBudgetLine::findOrFail($lineId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Baris bajet berjaya dibuang.',
        ]);
    }

    /**
     * Senarai portfolio.
     */
    public function portfolios(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => ProjectPortfolio::query()->with('projects')->get(),
        ]);
    }

    /**
     * Cipta portfolio.
     */
    public function storePortfolio(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'project_ids' => ['nullable', 'array'],
            'project_ids.*' => ['integer', 'exists:projects,id'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();

        $portfolio = ProjectPortfolio::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'owner_id' => auth()->id(),
        ]);

        if (! empty($data['project_ids'])) {
            $portfolio->projects()->sync($data['project_ids']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Portfolio berjaya dicipta.',
            'data' => $portfolio->load('projects'),
        ], 201);
    }

    /**
     * Kemas kini portfolio (termasuk projek terkandung).
     */
    public function updatePortfolio(Request $request, int $id): JsonResponse
    {
        $portfolio = ProjectPortfolio::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
            'project_ids' => ['nullable', 'array'],
            'project_ids.*' => ['integer', 'exists:projects,id'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();

        if (array_key_exists('project_ids', $data)) {
            $portfolio->projects()->sync($data['project_ids'] ?? []);
            unset($data['project_ids']);
        }

        $portfolio->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Portfolio berjaya dikemas kini.',
            'data' => $portfolio->fresh()->load('projects'),
        ]);
    }

    /**
     * Buang portfolio.
     */
    public function destroyPortfolio(int $id): JsonResponse
    {
        ProjectPortfolio::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Portfolio berjaya dibuang.',
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
