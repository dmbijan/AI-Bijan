<?php

namespace App\Modules\ProofOfValue\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ProofOfValue\Models\PovExport;
use App\Modules\ProofOfValue\Models\PovMetric;
use App\Modules\ProofOfValue\Services\ProofOfValueService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProofOfValueController extends Controller
{
    public function __construct(private ProofOfValueService $pov) {}

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    /**
     * Jana katalog metrik standard.
     */
    public function janaKatalog(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => ['nullable', 'string', 'max:60'],
        ]);

        $dicipta = $this->pov->janaKatalog($this->tenantId(), $validated['module'] ?? null);

        return response()->json([
            'success' => true,
            'message' => "{$dicipta} metrik baharu dicipta",
            'data' => ['dicipta' => $dicipta],
        ]);
    }

    /**
     * Senarai metrik.
     */
    public function index(Request $request): JsonResponse
    {
        $query = PovMetric::query()
            ->where('tenant_id', $this->tenantId())
            ->orderBy('module')
            ->orderBy('key');

        if ($request->filled('module')) {
            $query->where('module', $request->string('module')->toString());
        }

        $metrics = $query->get();

        return response()->json([
            'success' => true,
            'data' => $metrics->map(fn ($m) => [
                'id' => $m->id,
                'module' => $m->module,
                'key' => $m->key,
                'name' => $m->name,
                'description' => $m->description,
                'unit' => $m->unit,
                'direction' => $m->direction,
                'target_value' => $m->target_value !== null ? (float) $m->target_value : null,
                'current_value' => $m->bacaanTerkini()?->value,
                'baseline_value' => $m->baselineTerkini()?->baseline_value,
                'change_percent' => $m->perubahan(),
                'target_met' => $m->sasaranTercapai(),
                'is_verified' => $m->is_verified,
                'is_active' => $m->is_active,
            ]),
        ]);
    }

    /**
     * Tambah/ubah metrik tersuai.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'module' => ['required', 'string', 'max:60'],
            'key' => ['required', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:1000'],
            'unit' => ['nullable', 'in:percent,rm,count,minutes,ratio,days'],
            'direction' => ['nullable', 'in:up,down'],
            'target_value' => ['nullable', 'numeric'],
        ]);

        $metric = PovMetric::updateOrCreate(
            [
                'tenant_id' => $this->tenantId(),
                'module' => $validated['module'],
                'key' => $validated['key'],
            ],
            array_merge($validated, ['tenant_id' => $this->tenantId()])
        );

        return response()->json(['success' => true, 'data' => $metric], 201);
    }

    /**
     * Rekod bacaan.
     */
    public function rekodBacaan(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'numeric'],
            'reading_date' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:80'],
        ]);

        $reading = $this->pov->rekodBacaan(
            $this->tenantId(),
            $id,
            (float) $validated['value'],
            $validated['reading_date'] ?? null,
            $validated['source'] ?? null
        );

        return response()->json(['success' => true, 'data' => $reading]);
    }

    /**
     * Rekod baseline.
     */
    public function rekodBaseline(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'numeric'],
            'baseline_date' => ['nullable', 'date'],
            'source' => ['nullable', 'string', 'max:80'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $baseline = $this->pov->rekodBaseline(
            $this->tenantId(),
            $id,
            (float) $validated['value'],
            $validated['baseline_date'] ?? null,
            $validated['source'] ?? null,
            $validated['note'] ?? null
        );

        return response()->json(['success' => true, 'data' => $baseline], 201);
    }

    /**
     * Sahkan metrik.
     */
    public function sahkan(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'verified' => ['required', 'boolean'],
            'note' => ['nullable', 'string', 'max:250'],
        ]);

        $metric = $this->pov->sahkanMetrik(
            $this->tenantId(),
            $id,
            $validated['verified'],
            $validated['note'] ?? null
        );

        return response()->json(['success' => true, 'data' => $metric]);
    }

    /**
     * Ringkasan bukti nilai (untuk command centre).
     */
    public function ringkasan(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->pov->ringkasan($this->tenantId()),
        ]);
    }

    /**
     * Jana eksport bukti jualan.
     */
    public function janaEksport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ]);

        $export = $this->pov->janaEksport(
            $this->tenantId(),
            $validated['name'],
            $validated['period_start'],
            $validated['period_end']
        );

        return response()->json(['success' => true, 'data' => $export], 201);
    }

    /**
     * Senarai eksport.
     */
    public function exports(): JsonResponse
    {
        $exports = PovExport::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $exports]);
    }
}
