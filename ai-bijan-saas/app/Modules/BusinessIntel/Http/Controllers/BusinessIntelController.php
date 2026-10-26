<?php

namespace App\Modules\BusinessIntel\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\BusinessIntel\Models\BiCompetitor;
use App\Modules\BusinessIntel\Models\BiHealthSnapshot;
use App\Modules\BusinessIntel\Models\BiIdea;
use App\Modules\BusinessIntel\Models\BiMarketSignal;
use App\Modules\BusinessIntel\Models\BiStudy;
use App\Modules\BusinessIntel\Services\BusinessIntelService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessIntelController extends Controller
{
    public function __construct(private BusinessIntelService $bi) {}

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    // ---------------------------------------------------------------
    // Kesihatan
    // ---------------------------------------------------------------

    /**
     * Kira kesihatan semasa (tanpa simpan).
     */
    public function kesihatan(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->bi->kiraKesihatan($this->tenantId()),
        ]);
    }

    /**
     * Simpan snapshot kesihatan hari ini.
     */
    public function simpanKesihatan(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->bi->simpanKesihatan($this->tenantId()),
        ], 201);
    }

    /**
     * Snapshot terkini.
     */
    public function snapshotTerkini(): JsonResponse
    {
        $snapshot = BiHealthSnapshot::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('snapshot_date')
            ->first();

        return response()->json(['success' => true, 'data' => $snapshot]);
    }

    /**
     * Trend kesihatan.
     */
    public function trend(Request $request): JsonResponse
    {
        $hari = min(max((int) $request->integer('days', 90), 7), 365);

        return response()->json([
            'success' => true,
            'data' => $this->bi->trendKesihatan($this->tenantId(), $hari),
        ]);
    }

    // ---------------------------------------------------------------
    // Kajian
    // ---------------------------------------------------------------

    public function kajian(Request $request): JsonResponse
    {
        $query = BiStudy::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json(['success' => true, 'data' => $query->limit(50)->get()]);
    }

    /**
     * Jalankan kajian bisnes dengan AI.
     */
    public function jalankanKajian(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'study_type' => ['nullable', 'in:general,pricing,product,market,operations,customer'],
            'question' => ['nullable', 'string', 'max:2000'],
        ]);

        $study = $this->bi->jalankanKajian($this->tenantId(), $validated);

        return response()->json(['success' => true, 'data' => $study], 201);
    }

    public function butiranKajian(int $id): JsonResponse
    {
        $study = BiStudy::query()
            ->with('ideas')
            ->where('tenant_id', $this->tenantId())
            ->findOrFail($id);

        return response()->json(['success' => true, 'data' => $study]);
    }

    /**
     * Data perniagaan mentah (untuk ketelusan).
     */
    public function dataBisnes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->bi->kumpulData($this->tenantId()),
        ]);
    }

    // ---------------------------------------------------------------
    // Idea feed
    // ---------------------------------------------------------------

    public function idea(Request $request): JsonResponse
    {
        $status = $request->filled('status') ? $request->string('status')->toString() : null;
        $limit = min(max((int) $request->integer('limit', 50), 1), 200);

        return response()->json([
            'success' => true,
            'data' => $this->bi->ideaFeed($this->tenantId(), $status, $limit),
        ]);
    }

    public function terimaIdea(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->bi->terimaIdea($this->tenantId(), $id),
        ]);
    }

    public function tolakIdea(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->bi->tolakIdea($this->tenantId(), $id, $validated['reason'] ?? null),
        ]);
    }

    public function lengkapkanIdea(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'actual_outcome' => ['nullable', 'numeric'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->bi->lengkapkanIdea(
                $this->tenantId(),
                $id,
                isset($validated['actual_outcome']) ? (float) $validated['actual_outcome'] : null
            ),
        ]);
    }

    public function ciptaIdea(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['nullable', 'string', 'max:60'],
            'source' => ['nullable', 'in:ai,competitor,data,manual,customer'],
            'impact' => ['nullable', 'in:low,medium,high'],
            'effort' => ['nullable', 'in:low,medium,high'],
            'impact_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $idea = BiIdea::create(array_merge($validated, [
            'tenant_id' => $this->tenantId(),
        ]));

        return response()->json(['success' => true, 'data' => $idea], 201);
    }

    // ---------------------------------------------------------------
    // Pesaing
    // ---------------------------------------------------------------

    public function pesaing(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => BiCompetitor::query()
                ->where('tenant_id', $this->tenantId())
                ->orderByDesc('threat_level')
                ->get(),
        ]);
    }

    public function simpanPesaing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['nullable', 'integer'],
            'name' => ['required_without:id', 'string', 'max:180'],
            'website' => ['nullable', 'url', 'max:500'],
            'industry' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'size:2'],
            'channels' => ['nullable', 'array'],
            'products' => ['nullable', 'array'],
            'pricing' => ['nullable', 'array'],
            'strengths' => ['nullable', 'string', 'max:2000'],
            'weaknesses' => ['nullable', 'string', 'max:2000'],
            'positioning' => ['nullable', 'string', 'max:1000'],
            'threat_level' => ['nullable', 'in:low,medium,high'],
        ]);

        if (! empty($validated['id'])) {
            $pesaing = BiCompetitor::query()
                ->where('tenant_id', $this->tenantId())
                ->findOrFail($validated['id']);
            $pesaing->update($validated);
        } else {
            $validated['tenant_id'] = $this->tenantId();
            $pesaing = BiCompetitor::create($validated);
        }

        return response()->json(['success' => true, 'data' => $pesaing->fresh()], 201);
    }

    public function rekodSnapshotPesaing(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'observed_data' => ['nullable', 'array'],
            'ai_analysis' => ['nullable', 'string', 'max:5000'],
            'threats' => ['nullable', 'array'],
            'opportunities' => ['nullable', 'array'],
            'source_type' => ['nullable', 'in:manual,public_scrape,api,tenant_input'],
            'source_url' => ['nullable', 'url', 'max:500'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->bi->rekodSnapshotPesaing($this->tenantId(), $id, $validated),
        ], 201);
    }

    public function ringkasanPersaingan(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->bi->ringkasanPersaingan($this->tenantId()),
        ]);
    }

    // ---------------------------------------------------------------
    // Sinyal pasaran
    // ---------------------------------------------------------------

    public function sinyal(Request $request): JsonResponse
    {
        $query = BiMarketSignal::query()
            ->where('tenant_id', $this->tenantId())
            ->orderByDesc('strength');

        if ($request->filled('signal_type')) {
            $query->where('signal_type', $request->input('signal_type'));
        }

        return response()->json(['success' => true, 'data' => $query->limit(100)->get()]);
    }

    public function janaSinyal(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->bi->janaSinyalPasaran($this->tenantId()),
        ], 201);
    }

    // ---------------------------------------------------------------
    // Pembelajaran AI
    // ---------------------------------------------------------------

    public function pembelajaran(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->bi->pembelajaranAktif($this->tenantId()),
        ]);
    }
}
