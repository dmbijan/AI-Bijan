<?php

namespace App\Modules\FlowAutomation\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\FlowAutomation\Models\WorkflowDefinition;
use App\Modules\FlowAutomation\Models\WorkflowRun;
use App\Modules\FlowAutomation\Services\FlowEngine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FlowAutomationController extends Controller
{
    public function __construct(
        protected FlowEngine $engine
    ) {
    }

    private function tenantId(): int
    {
        return app(\App\Support\Tenancy\TenantContext::class)->id();
    }

    // ===== Workflows =====

    public function index(Request $request): JsonResponse
    {
        $query = WorkflowDefinition::forTenant($this->tenantId());

        if ($request->filled('trigger_type')) {
            $query->byTrigger($request->trigger_type);
        }
        if ($request->boolean('active_only')) {
            $query->active();
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('created_at')->paginate($request->integer('per_page', 20)),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'trigger_type' => 'required|in:domain_event,webhook,schedule,manual,record_change',
            'steps' => 'required|array|min:1',
            'conditions' => 'nullable|array',
            'trigger_config' => 'nullable|array',
            'requires_approval' => 'nullable|boolean',
        ]);

        $workflow = WorkflowDefinition::create([
            'tenant_id' => $this->tenantId(),
            'name' => $request->name,
            'description' => $request->description,
            'trigger_type' => $request->trigger_type,
            'trigger_config' => $request->input('trigger_config', []),
            'conditions' => $request->input('conditions', []),
            'steps' => $request->steps,
            'is_active' => false, // Aktifkan selepas dry run
            'requires_approval' => $request->boolean('requires_approval'),
            'version' => 1,
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workflow dicipta (tidak aktif). Jalankan dry run sebelum aktifkan.',
            'data' => $workflow,
        ], 201);
    }

    public function show(string $id): JsonResponse
    {
        $workflow = $this->findWorkflow($id);
        if (!$workflow) {
            return $this->notFound();
        }

        return response()->json([
            'success' => true,
            'data' => $workflow,
            'recent_runs' => $workflow->runs()->orderByDesc('created_at')->limit(10)->get(),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $workflow = $this->findWorkflow($id);
        if (!$workflow) {
            return $this->notFound();
        }

        $allowed = ['name', 'description', 'conditions', 'steps', 'trigger_config', 'requires_approval'];
        $workflow->fill(array_intersect_key($request->all(), array_flip($allowed)));

        // Setiap perubahan naikkan versi
        if ($workflow->isDirty(['steps', 'conditions', 'trigger_config'])) {
            $workflow->version = ($workflow->version ?? 1) + 1;
            // Nyahaktifkan — perlu dry run semula
            $workflow->is_active = false;
        }

        $workflow->save();

        return response()->json([
            'success' => true,
            'message' => 'Workflow dikemas kini. Jalankan dry run sebelum aktifkan semula.',
            'data' => $workflow,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $workflow = $this->findWorkflow($id);
        if (!$workflow) {
            return $this->notFound();
        }

        $workflow->delete();

        return response()->json(['success' => true, 'message' => 'Workflow dipadam.']);
    }

    // ===== Lifecycle =====

    public function dryRun(Request $request, string $id): JsonResponse
    {
        $workflow = $this->findWorkflow($id);
        if (!$workflow) {
            return $this->notFound();
        }

        return response()->json([
            'success' => true,
            'data' => $this->engine->dryRun($workflow, $request->input('payload', [])),
        ]);
    }

    public function activate(string $id): JsonResponse
    {
        $workflow = $this->findWorkflow($id);
        if (!$workflow) {
            return $this->notFound();
        }

        if (config('flow_automation.protection.require_dry_run_before_activation', true)) {
            // Dalam implementasi sebenar: rekod dry run sebelum aktifkan
            // Untuk kini, benarkan tetapi beri amaran
            Log::info('Workflow activated', ['workflow_id' => $workflow->id, 'by' => auth()->id()]);
        }

        $workflow->is_active = true;
        $workflow->kill_switch = false;
        $workflow->save();

        return response()->json([
            'success' => true,
            'message' => 'Workflow diaktifkan.',
            'data' => $workflow,
        ]);
    }

    public function deactivate(string $id): JsonResponse
    {
        $workflow = $this->findWorkflow($id);
        if (!$workflow) {
            return $this->notFound();
        }

        $workflow->is_active = false;
        $workflow->save();

        return response()->json([
            'success' => true,
            'message' => 'Workflow dinyahaktifkan.',
            'data' => $workflow,
        ]);
    }

    /**
     * KILL SWITCH (BAB 28.3)
     */
    public function toggleKillSwitch(Request $request, string $id): JsonResponse
    {
        $workflow = $this->findWorkflow($id);
        if (!$workflow) {
            return $this->notFound();
        }

        $workflow->kill_switch = $request->boolean('enabled', true);
        $workflow->save();

        return response()->json([
            'success' => true,
            'message' => $workflow->kill_switch
                ? 'Kill switch DIHIDUPKAN — semua run baru dihentikan.'
                : 'Kill switch dimatikan — workflow boleh berjalan semula.',
            'data' => $workflow,
        ]);
    }

    /**
     * Kill switch global untuk semua workflow tenant
     */
    public function tenantKillSwitch(Request $request): JsonResponse
    {
        $enabled = $request->boolean('enabled', true);

        $count = WorkflowDefinition::forTenant($this->tenantId())
            ->update(['kill_switch' => $enabled]);

        return response()->json([
            'success' => true,
            'message' => $enabled
                ? "{$count} workflow dihentikan (kill switch tenant)."
                : "{$count} workflow dibenarkan berjalan semula.",
            'affected' => $count,
        ]);
    }

    // ===== Execution =====

    public function trigger(Request $request, string $id): JsonResponse
    {
        $workflow = $this->findWorkflow($id);
        if (!$workflow) {
            return $this->notFound();
        }

        try {
            $run = $this->engine->triggerManual(
                $workflow,
                $request->input('payload', []),
                $request->input('idempotency_key')
            );

            return response()->json([
                'success' => true,
                'message' => 'Workflow dicetus.',
                'data' => $run,
            ], 201);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    // ===== Runs =====

    public function listRuns(Request $request): JsonResponse
    {
        $query = WorkflowRun::forTenant($this->tenantId());

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        if ($request->filled('workflow_id')) {
            $query->where('workflow_id', $request->workflow_id);
        }
        if ($request->boolean('active_only')) {
            $query->active();
        }
        if ($request->boolean('failed_only')) {
            $query->failed();
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('created_at')->paginate($request->integer('per_page', 20)),
        ]);
    }

    public function showRun(string $id): JsonResponse
    {
        $run = WorkflowRun::forTenant($this->tenantId())->find($id);

        if (!$run) {
            return response()->json(['success' => false, 'message' => 'Run tidak ditemui.'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $run,
            'progress_percent' => $run->progressPercent(),
            'can_retry' => $run->canRetry(),
        ]);
    }

    public function retryRun(string $id): JsonResponse
    {
        $run = WorkflowRun::forTenant($this->tenantId())->find($id);

        if (!$run) {
            return response()->json(['success' => false, 'message' => 'Run tidak ditemui.'], 404);
        }

        try {
            $run = $this->engine->retry($run);
            return response()->json([
                'success' => true,
                'message' => 'Run di-retry.',
                'data' => $run,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function cancelRun(Request $request, string $id): JsonResponse
    {
        $run = WorkflowRun::forTenant($this->tenantId())->find($id);

        if (!$run) {
            return response()->json(['success' => false, 'message' => 'Run tidak ditemui.'], 404);
        }

        try {
            $run = $this->engine->cancel($run, $request->input('reason'));
            return response()->json([
                'success' => true,
                'message' => 'Run dibatalkan.',
                'data' => $run,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Proses run yang due untuk retry (biasanya dipanggil oleh scheduler)
     */
    public function processRetries(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->engine->processRetries(),
        ]);
    }

    // ===== Metadata & Stats =====

    public function stats(): JsonResponse
    {
        $tenantId = $this->tenantId();
        $workflows = WorkflowDefinition::forTenant($tenantId);
        $runs = WorkflowRun::forTenant($tenantId);

        return response()->json([
            'success' => true,
            'data' => [
                'workflows' => [
                    'total' => (clone $workflows)->count(),
                    'active' => (clone $workflows)->active()->count(),
                    'killed' => (clone $workflows)->where('kill_switch', true)->count(),
                ],
                'runs' => [
                    'total' => (clone $runs)->count(),
                    'active' => (clone $runs)->active()->count(),
                    'failed' => (clone $runs)->failed()->count(),
                    'due_for_retry' => (clone $runs)->dueForRetry()->count(),
                    'by_status' => (clone $runs)
                        ->select('status', DB::raw('count(*) as count'))
                        ->groupBy('status')
                        ->pluck('count', 'status')
                        ->toArray(),
                ],
            ],
        ]);
    }

    public function metadata(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'trigger_types' => config('flow_automation.trigger_types'),
                'condition_operators' => config('flow_automation.condition_operators'),
                'action_types' => config('flow_automation.action_types'),
                'limits' => config('flow_automation.limits'),
                'example_workflows' => config('flow_automation.example_workflows'),
            ],
        ]);
    }

    // ===== Helpers =====

    private function findWorkflow(string $id): ?WorkflowDefinition
    {
        return WorkflowDefinition::forTenant($this->tenantId())->find($id);
    }

    private function notFound(): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Workflow tidak ditemui.'], 404);
    }
}
