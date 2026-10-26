<?php

namespace App\Modules\ADE\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\ADE\Services\AdeOrchestrator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdeController extends Controller
{
    public function __construct(private AdeOrchestrator $ade)
    {
    }

    private function tenantId(): int
    {
        return (int) app(\App\Support\Tenancy\TenantContext::class)->id();
    }

    // ── Gambaran Keseluruhan ───────────────────────────────────────────

    /**
     * GET /api/v1/ade/summary — ringkasan ADE untuk tenant.
     */
    public function ringkasan(): JsonResponse
    {
        return response()->json($this->ade->ringkasan($this->tenantId()));
    }

    // ── A1 Skills Engine ───────────────────────────────────────────────

    /**
     * GET /api/v1/ade/skills — senarai skill (tenant + global).
     */
    public function senaraiSkill(Request $request): JsonResponse
    {
        $data = $this->ade->skills()->senarai(
            $this->tenantId(),
            $request->query('kategori'),
            $request->boolean('semua_aktif', false) === false
        );

        return response()->json(['data' => $data, 'jumlah' => count($data)]);
    }

    /**
     * POST /api/v1/ade/skills — jana skill tersuai.
     */
    public function janaSkill(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'min:3', 'max:80'],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:1000'],
            'system_prompt' => ['nullable', 'string', 'max:5000'],
            'steps' => ['nullable', 'array', 'max:20'],
            'triggers' => ['nullable', 'array', 'max:15'],
            'tools' => ['nullable', 'array', 'max:50'],
            'config' => ['nullable', 'array'],
            'origin_project' => ['nullable', 'string', 'max:100'],
            'license' => ['nullable', 'string', 'max:30'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        $skill = $this->ade->skills()->janaSkill($this->tenantId(), $validated);

        return response()->json(['data' => $skill], 201);
    }

    /**
     * POST /api/v1/ade/skills/install — pasang skill global ke tenant.
     */
    public function pasangSkill(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'skill_id' => ['required', 'integer'],
        ]);

        $skill = $this->ade->skills()->pasang($this->tenantId(), (int) $validated['skill_id']);

        return response()->json(['data' => $skill]);
    }

    /**
     * POST /api/v1/ade/skills/match — cari skill yang terpicu oleh teks.
     */
    public function padanSkill(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'min:1', 'max:5000'],
            'had' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $skills = $this->ade->skills()->cariPencetus(
            $this->tenantId(),
            (string) $validated['text'],
            (int) ($validated['had'] ?? 5)
        );

        return response()->json([
            'data' => array_map(fn ($s) => $s->toArray(), $skills),
            'jumlah' => count($skills),
        ]);
    }

    /**
     * GET /api/v1/ade/skills/{id}/invocations — sejarah pemanggilan.
     */
    public function sejarahSkill(int $id): JsonResponse
    {
        $tenantId = $this->tenantId();

        $invocations = \App\Modules\ADE\Models\AdeSkillInvocation::where('tenant_id', $tenantId)
            ->where('ade_skill_id', $id)
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->toArray();

        return response()->json(['data' => $invocations, 'jumlah' => count($invocations)]);
    }

    // ── A2 Worktree Isolation ──────────────────────────────────────────

    /**
     * GET /api/v1/ade/sandboxes — senarai sandbox.
     */
    public function senaraiSandbox(Request $request): JsonResponse
    {
        $data = $this->ade->sandboxes()->senarai(
            $this->tenantId(),
            $request->query('status')
        );

        return response()->json(['data' => $data, 'jumlah' => count($data)]);
    }

    /**
     * POST /api/v1/ade/sandboxes — cipta sandbox.
     */
    public function ciptaSandbox(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'type' => ['nullable', 'in:worktree,container,directory,none'],
            'base_branch' => ['nullable', 'string', 'max:100'],
            'repo_path' => ['nullable', 'string', 'max:500'],
        ]);

        $sandbox = $this->ade->sandboxes()->cipta($this->tenantId(), $validated);

        return response()->json(['data' => $sandbox], 201);
    }

    /**
     * POST /api/v1/ade/sandboxes/{id}/baseline — rekod garis dasar ujian.
     */
    public function rekodBaseline(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'passed' => ['required', 'integer', 'min:0'],
            'failed' => ['required', 'integer', 'min:0'],
            'skipped' => ['nullable', 'integer', 'min:0'],
            'duration_ms' => ['nullable', 'integer', 'min:0'],
        ]);

        $sandbox = $this->ade->sandboxes()->rekodBaseline(
            $this->tenantId(),
            $id,
            $validated
        );

        return response()->json(['data' => $sandbox]);
    }

    /**
     * DELETE /api/v1/ade/sandboxes/{id} — buang sandbox.
     */
    public function buangSandbox(int $id): JsonResponse
    {
        $sandbox = $this->ade->sandboxes()->buang($this->tenantId(), $id);

        return response()->json(['data' => $sandbox]);
    }

    /**
     * GET /api/v1/ade/sandboxes/branches — senarai branch repo.
     */
    public function senaraiBranch(Request $request): JsonResponse
    {
        $repoPath = (string) $request->query('repo_path', '');

        if ($repoPath === '') {
            return response()->json(['data' => [], 'jumlah' => 0]);
        }

        $branches = $this->ade->sandboxes()->senaraiBranch($repoPath);

        return response()->json(['data' => $branches, 'jumlah' => count($branches)]);
    }

    // ── A3 Sub-Agent Coordinator ───────────────────────────────────────

    /**
     * GET /api/v1/ade/coordinations — senarai coordination.
     */
    public function senaraiCoordination(Request $request): JsonResponse
    {
        $data = $this->ade->coordinator()->senarai(
            $this->tenantId(),
            $request->query('status')
        );

        return response()->json(['data' => $data, 'jumlah' => count($data)]);
    }

    /**
     * POST /api/v1/ade/coordinations — cipta coordination.
     */
    public function ciptaCoordination(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'objective' => ['nullable', 'string', 'max:5000'],
            'max_parallel' => ['nullable', 'integer', 'min:1', 'max:10'],
            'ade_sandbox_id' => ['nullable', 'integer'],
            'tasks' => ['nullable', 'array', 'max:50'],
        ]);

        $tenantId = $this->tenantId();
        $tasks = $validated['tasks'] ?? [];
        unset($validated['tasks']);

        $coord = $this->ade->coordinator()->cipta($tenantId, $validated);

        foreach ($tasks as $t) {
            $this->ade->coordinator()->tambahTask($tenantId, $coord->id, (array) $t);
        }

        return response()->json(['data' => $coord->fresh()->toArray()], 201);
    }

    /**
     * GET /api/v1/ade/coordinations/{id} — butiran coordination + tasks.
     */
    public function butiranCoordination(int $id): JsonResponse
    {
        return response()->json($this->ade->coordinator()->butiran($this->tenantId(), $id));
    }

    /**
     * POST /api/v1/ade/coordinations/{id}/run — jalankan coordination.
     */
    public function jalankanCoordination(int $id): JsonResponse
    {
        $coord = $this->ade->coordinator()->jalankan($this->tenantId(), $id);

        return response()->json(['data' => $coord->toArray()]);
    }

    /**
     * POST /api/v1/ade/coordinations/{id}/cross-review — semak silang.
     */
    public function semakSilang(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reviewers' => ['required', 'array', 'min:1'],
            'reviewers.*' => ['integer'],
        ]);

        $keputusan = $this->ade->coordinator()->semakSilang(
            $this->tenantId(),
            $id,
            $validated['reviewers']
        );

        return response()->json(['data' => $keputusan]);
    }

    // ── A4 Review Surface ──────────────────────────────────────────────

    /**
     * GET /api/v1/ade/reviews — senarai review.
     */
    public function senaraiReview(Request $request): JsonResponse
    {
        $data = $this->ade->reviews()->senarai(
            $this->tenantId(),
            $request->query('status')
        );

        return response()->json(['data' => $data, 'jumlah' => count($data)]);
    }

    /**
     * POST /api/v1/ade/reviews — cipta review.
     */
    public function ciptaReview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'ade_coordination_id' => ['nullable', 'integer'],
            'ade_sandbox_id' => ['nullable', 'integer'],
            'severity' => ['nullable', 'in:low,medium,high,critical'],
            'diff' => ['nullable', 'array', 'max:2000'],
            'files' => ['nullable', 'array', 'max:500'],
            'test_results' => ['nullable', 'array'],
            'summary' => ['nullable', 'string', 'max:5000'],
            'findings' => ['nullable', 'array', 'max:100'],
            'comments' => ['nullable', 'array', 'max:200'],
            'additions' => ['nullable', 'integer', 'min:0'],
            'deletions' => ['nullable', 'integer', 'min:0'],
        ]);

        $review = $this->ade->reviews()->cipta($this->tenantId(), $validated);

        return response()->json(['data' => $review], 201);
    }

    /**
     * GET /api/v1/ade/reviews/{id}/walkthrough — diff walkthrough.
     */
    public function walkthrough(int $id): JsonResponse
    {
        return response()->json($this->ade->reviews()->walkthrough($this->tenantId(), $id));
    }

    /**
     * POST /api/v1/ade/reviews/{id}/findings — tambah penemuan.
     */
    public function tambahTemuan(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'severity' => ['required', 'in:low,medium,high,critical'],
            'title' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:2000'],
            'file' => ['nullable', 'string', 'max:500'],
            'line' => ['nullable', 'integer', 'min:0'],
            'suggestion' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = $this->ade->reviews()->tambahTemuan($this->tenantId(), $id, $validated);

        return response()->json(['data' => $review]);
    }

    /**
     * POST /api/v1/ade/reviews/{id}/comments — tambah komen baris.
     */
    public function tambahKomen(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'file' => ['nullable', 'string', 'max:500'],
            'line' => ['nullable', 'integer', 'min:0'],
        ]);

        $review = $this->ade->reviews()->tambahKomen($this->tenantId(), $id, $validated);

        return response()->json(['data' => $review]);
    }

    /**
     * POST /api/v1/ade/reviews/{id}/tests — rekod keputusan ujian.
     */
    public function rekodUjian(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'passed' => ['required', 'integer', 'min:0'],
            'failed' => ['required', 'integer', 'min:0'],
            'skipped' => ['nullable', 'integer', 'min:0'],
            'duration_ms' => ['nullable', 'integer', 'min:0'],
            'suite' => ['nullable', 'string', 'max:100'],
        ]);

        $review = $this->ade->reviews()->rekodUjian($this->tenantId(), $id, $validated);

        return response()->json(['data' => $review]);
    }

    /**
     * POST /api/v1/ade/reviews/{id}/approve — sahkan review.
     */
    public function sahkanReview(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approved,changes_requested,rejected,merged'],
        ]);

        $review = $this->ade->reviews()->sahkan(
            $this->tenantId(),
            $id,
            (string) $validated['decision'],
            auth()->id()
        );

        return response()->json(['data' => $review]);
    }

    // ── Aliran penuh ───────────────────────────────────────────────────

    /**
     * POST /api/v1/ade/run — jalankan aliran ADE penuh (skill → sandbox →
     * coordination → review).
     */
    public function jalankanAliran(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'input' => ['required', 'string', 'max:5000'],
            'objective' => ['nullable', 'string', 'max:5000'],
            'max_parallel' => ['nullable', 'integer', 'min:1', 'max:10'],
            'sandbox' => ['nullable', 'array'],
            'tasks' => ['nullable', 'array', 'max:50'],
            'review' => ['nullable', 'array'],
        ]);

        $hasil = $this->ade->jalankanAliran($this->tenantId(), $validated);

        return response()->json($hasil, 201);
    }
}
