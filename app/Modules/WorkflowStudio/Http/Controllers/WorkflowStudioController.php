<?php

namespace App\Modules\WorkflowStudio\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\WorkflowStudio\Models\Workflow;
use App\Modules\WorkflowStudio\Models\WorkflowRun;
use App\Modules\WorkflowStudio\Models\WorkflowTemplate;
use App\Modules\WorkflowStudio\Services\WorkflowEngine;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkflowStudioController extends Controller
{
    public function __construct(private WorkflowEngine $engine) {}

    private function tenantId(): int
    {
        return app(TenantContext::class)->id();
    }

    /** Katalog jenis node untuk builder. */
    public function nodeTypes(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->engine->katalogNode()]);
    }

    /** Templat tersedia. */
    public function templates(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => WorkflowTemplate::query()
                ->where(function ($q) {
                    $q->whereNull('tenant_id')->orWhere('tenant_id', $this->tenantId());
                })
                ->where('is_active', true)
                ->orderByDesc('is_official')
                ->orderByDesc('install_count')
                ->get(),
        ]);
    }

    /** Pasang templat. */
    public function pasangTemplate(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate(['name' => ['nullable', 'string', 'max:180']]);

        $workflow = $this->engine->pasangTemplate($this->tenantId(), $id, $validated['name'] ?? null);

        return response()->json(['success' => true, 'data' => $workflow], 201);
    }

    /** Senarai workflow. */
    public function index(Request $request): JsonResponse
    {
        $query = Workflow::query()->where('tenant_id', $this->tenantId())->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json(['success' => true, 'data' => $query->limit(100)->get()]);
    }

    /** Cipta workflow. */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'definition' => ['required', 'array'],
            'trigger_type' => ['nullable', 'in:manual,schedule,event,webhook,ai'],
            'dry_run_mode' => ['nullable', 'boolean'],
        ]);

        $sah = $this->engine->sahkanDefinisi($validated['definition']);

        if (! $sah['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'Definisi tidak sah',
                'errors' => $sah['errors'],
            ], 422);
        }

        $workflow = Workflow::create(array_merge($validated, [
            'tenant_id' => $this->tenantId(),
            'status' => Workflow::STATUS_DRAFT,
            'is_enabled' => false,
            'dry_run_mode' => $validated['dry_run_mode'] ?? true,
        ]));

        return response()->json(['success' => true, 'data' => $workflow], 201);
    }

    /** Kemas kini workflow. */
    public function update(Request $request, int $id): JsonResponse
    {
        $workflow = Workflow::query()->where('tenant_id', $this->tenantId())->findOrFail($id);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:2000'],
            'definition' => ['nullable', 'array'],
            'status' => ['nullable', 'in:draft,active,paused,archived'],
            'is_enabled' => ['nullable', 'boolean'],
            'dry_run_mode' => ['nullable', 'boolean'],
            'trigger_type' => ['nullable', 'in:manual,schedule,event,webhook,ai'],
        ]);

        if (isset($validated['definition'])) {
            $sah = $this->engine->sahkanDefinisi($validated['definition']);

            if (! $sah['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Definisi tidak sah',
                    'errors' => $sah['errors'],
                ], 422);
            }
        }

        $workflow->update($validated);

        return response()->json(['success' => true, 'data' => $workflow->fresh()]);
    }

    /** Jalankan workflow. */
    public function run(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'payload' => ['nullable', 'array'],
            'dry_run' => ['nullable', 'boolean'],
        ]);

        $run = $this->engine->laksana(
            $this->tenantId(),
            $id,
            $validated['payload'] ?? [],
            $validated['dry_run'] ?? null
        );

        return response()->json(['success' => true, 'data' => $run]);
    }

    /** Sejarah run. */
    public function runs(int $id): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => WorkflowRun::query()
                ->where('tenant_id', $this->tenantId())
                ->where('workflow_id', $id)
                ->orderByDesc('created_at')
                ->limit(50)
                ->get(),
        ]);
    }

    /** Ringkasan. */
    public function ringkasan(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->engine->ringkasan($this->tenantId()),
        ]);
    }
}
