<?php

namespace App\Modules\Boardroom\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Boardroom\Models\BoardroomSession;
use App\Modules\Boardroom\Models\BoardroomFinding;
use App\Modules\Boardroom\Services\BoardroomOrchestrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoardroomController extends Controller
{
    public function __construct(
        protected BoardroomOrchestrator $orchestrator
    ) {
    }

    private function tenantId(): int
    {
        return app(\App\Support\Tenancy\TenantContext::class)->id();
    }

    // ===== Sessions =====

    public function index(Request $request): JsonResponse
    {
        $query = BoardroomSession::forTenant($this->tenantId());

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->boolean('open_only')) {
            $query->open();
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('created_at')->paginate($request->integer('per_page', 20)),
        ]);
    }

    /**
     * Langkah 1: Buka sesi dengan persoalan pemilik
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|min:5',
            'category' => 'nullable|string',
            'context' => 'nullable|array',
        ]);

        $session = $this->orchestrator->openSession(
            $this->tenantId(),
            auth()->id(),
            $request->question,
            $request->input('context', []) + ['category' => $request->category]
        );

        return response()->json([
            'success' => true,
            'message' => 'Sesi boardroom dibuka.',
            'data' => $session,
            'next_step' => 'Pilih ejen yang terlibat.',
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $session = BoardroomSession::forTenant($this->tenantId())
            ->with('findings')
            ->find($id);

        if (!$session) {
            return response()->json(['success' => false, 'message' => 'Sesi tidak ditemui.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $session,
            'current_step_info' => $session->getStepInfo(),
        ]);
    }

    /**
     * Langkah 2: Pilih ejen
     */
    public function selectAgents(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'agents' => 'nullable|array',
            'agents.*' => 'string',
        ]);

        $session = $this->findSession($id);
        if (!$session) {
            return $this->notFound();
        }

        try {
            $session = $this->orchestrator->selectAgents($session, $request->input('agents'));
            return response()->json([
                'success' => true,
                'message' => 'Ejen dipilih.',
                'data' => $session,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Langkah 3: Edarkan konteks
     */
    public function distributeContext(string $id): JsonResponse
    {
        $session = $this->findSession($id);
        if (!$session) {
            return $this->notFound();
        }

        try {
            return response()->json([
                'success' => true,
                'data' => $this->orchestrator->distributeContext($session),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Langkah 4: Rekod dapatan ejen
     */
    public function addFinding(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'agent_key' => 'required|string',
            'finding_type' => 'required|in:fact,estimate,assumption,forecast,recommendation',
            'title' => 'required|string',
            'content' => 'required|string',
            'confidence_score' => 'nullable|numeric|min:0|max:1',
        ]);

        $session = $this->findSession($id);
        if (!$session) {
            return $this->notFound();
        }

        try {
            $finding = $this->orchestrator->recordFinding(
                $session,
                $request->agent_key,
                $request->all()
            );

            return response()->json([
                'success' => true,
                'message' => 'Dapatan direkod.',
                'data' => $finding,
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function listFindings(string $id): JsonResponse
    {
        $session = $this->findSession($id);
        if (!$session) {
            return $this->notFound();
        }

        return response()->json([
            'success' => true,
            'data' => $session->findings()->orderBy('created_at')->get(),
            'by_type' => $session->findingsByType(),
            'has_unsupported_facts' => $session->hasUnsupportedFacts(),
        ]);
    }

    /**
     * Langkah 5: Kesan percanggahan
     */
    public function detectConflicts(string $id): JsonResponse
    {
        $session = $this->findSession($id);
        if (!$session) {
            return $this->notFound();
        }

        $result = $this->orchestrator->detectConflicts($session);

        return response()->json([
            'success' => true,
            'data' => $result,
            'session' => $session->fresh(),
        ]);
    }

    /**
     * Langkah 6: Sediakan ringkasan AI CEO
     */
    public function prepareSummary(string $id): JsonResponse
    {
        $session = $this->findSession($id);
        if (!$session) {
            return $this->notFound();
        }

        $session = $this->orchestrator->prepareSummary($session);

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan disediakan. Menunggu keputusan pemilik.',
            'data' => $session,
        ]);
    }

    /**
     * Langkah 7: Rekod keputusan pemilik
     */
    public function decide(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'decision' => 'required|in:approve,reject,defer,request_more_info,modify',
            'reason' => 'nullable|string',
            'options' => 'nullable|array',
        ]);

        $session = $this->findSession($id);
        if (!$session) {
            return $this->notFound();
        }

        try {
            $session = $this->orchestrator->recordDecision(
                $session,
                auth()->id(),
                $request->decision,
                $request->reason,
                $request->input('options')
            );

            return response()->json([
                'success' => true,
                'message' => 'Keputusan direkod.',
                'data' => $session,
            ]);
        } catch (\RuntimeException | \InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function close(string $id): JsonResponse
    {
        $session = $this->findSession($id);
        if (!$session) {
            return $this->notFound();
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi ditutup.',
            'data' => $this->orchestrator->close($session),
        ]);
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $session = $this->findSession($id);
        if (!$session) {
            return $this->notFound();
        }

        return response()->json([
            'success' => true,
            'message' => 'Sesi dibatalkan.',
            'data' => $this->orchestrator->cancel($session, $request->input('reason')),
        ]);
    }

    // ===== Metadata =====

    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->orchestrator->getStats($this->tenantId()),
        ]);
    }

    public function metadata(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'workflow_steps' => config('boardroom.workflow_steps'),
                'finding_types' => config('boardroom.finding_types'),
                'decision_options' => config('boardroom.decision_options'),
                'agent_selection' => config('boardroom.agent_selection'),
            ],
        ]);
    }

    // ===== Helpers =====

    private function findSession(string $id): ?BoardroomSession
    {
        return BoardroomSession::forTenant($this->tenantId())->find($id);
    }

    private function notFound(): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Sesi tidak ditemui.'], 404);
    }
}
