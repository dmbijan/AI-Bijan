<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Milestone;
use App\Modules\Projects\Services\MilestoneService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MilestoneController extends Controller
{
    protected $milestoneService;

    public function __construct(MilestoneService $milestoneService)
    {
        $this->milestoneService = $milestoneService;
    }

    /**
     * List all milestones for a project.
     */
    public function index(Project $project, Request $request): JsonResponse
    {
        if ($project->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $query = $project->milestones();

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        $milestones = $query->orderBy('due_date')->paginate($request->get('per_page', 15));

        return response()->json($milestones);
    }

    /**
     * Show a specific milestone.
     */
    public function show(Milestone $milestone): JsonResponse
    {
        if ($milestone->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Milestone not found.'], 404);
        }

        return response()->json($milestone);
    }

    /**
     * Create a new milestone.
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        if ($project->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'completed'])],
            'completion_percentage' => 'sometimes|numeric|min:0|max:100',
        ]);

        $milestone = $this->milestoneService->createMilestone(
            array_merge($validated, ['project_id' => $project->id]),
            app(\App\Support\Tenancy\TenantContext::class)->id()
        );

        return response()->json($milestone, 201);
    }

    /**
     * Update a milestone.
     */
    public function update(Request $request, Milestone $milestone): JsonResponse
    {
        if ($milestone->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Milestone not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'sometimes|date',
            'status' => ['sometimes', Rule::in(['pending', 'in_progress', 'completed', 'overdue'])],
            'completion_percentage' => 'sometimes|numeric|min:0|max:100',
        ]);

        $milestone = $this->milestoneService->updateMilestone($milestone, $validated, app(\App\Support\Tenancy\TenantContext::class)->id());

        return response()->json($milestone);
    }

    /**
     * Delete a milestone.
     */
    public function destroy(Milestone $milestone): JsonResponse
    {
        if ($milestone->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Milestone not found.'], 404);
        }

        $this->milestoneService->deleteMilestone($milestone);

        return response()->json(['message' => 'Milestone deleted successfully.']);
    }

    /**
     * Mark milestone as completed.
     */
    public function complete(Milestone $milestone): JsonResponse
    {
        if ($milestone->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Milestone not found.'], 404);
        }

        $milestone = $this->milestoneService->completeMilestone($milestone);

        return response()->json($milestone);
    }

    /**
     * Get overdue milestones for a project.
     */
    public function overdue(Project $project): JsonResponse
    {
        if ($project->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $milestones = $this->milestoneService->getOverdueMilestones($project->id);

        return response()->json($milestones);
    }
}
