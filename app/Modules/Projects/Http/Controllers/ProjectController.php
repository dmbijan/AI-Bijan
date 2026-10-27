<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Services\TaskService;
use App\Modules\Projects\Services\MilestoneService;
use App\Modules\Projects\Services\TimesheetService;
use App\Modules\Projects\Services\DocumentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$request->tenant_id` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected $taskService;
    protected $milestoneService;
    protected $timesheetService;
    protected $documentService;

    public function __construct(
        TaskService $taskService,
        MilestoneService $milestoneService,
        TimesheetService $timesheetService,
        DocumentService $documentService
    ) {
        $this->taskService = $taskService;
        $this->milestoneService = $milestoneService;
        $this->timesheetService = $timesheetService;
        $this->documentService = $documentService;
    }

    /**
     * Get all projects for the authenticated user's tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();
        
        $query = Project::where('tenant_id', $tenantId)
            ->with(['manager', 'client']);

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->byPriority($request->priority);
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $projects = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($projects);
    }

    /**
     * Get a single project.
     */
    public function show(Project $project): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        if ($project->tenant_id !== $tenantId) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $project->load([
            'manager',
            'client',
            'tasks' => function ($query) {
                $query->with(['assignee', 'parent', 'subtasks', 'dependencies', 'dependents']);
            },
            'milestones',
            'members.user',
            'documents.uploader',
        ]);

        return response()->json($project);
    }

    /**
     * Create a new project.
     */
    public function store(Request $request): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:draft,active,on_hold,completed,cancelled',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'manager_id' => 'nullable|exists:users,id',
            'client_id' => 'nullable|exists:customers,id',
            'tags' => 'nullable|array',
            'members' => 'nullable|array',
        ]);

        return DB::transaction(function () use ($validated, $tenantId) {
            $validated['code'] = Project::generateCode();
            $ahli = $validated['members'] ?? [];
            unset($validated['members']);
            $validated['owner_id'] ??= auth()->id();

            $project = Project::create($validated);

            foreach ($ahli as $member) {
                $project->members()->create([
                    'tenant_id' => $tenantId,
                    'user_id' => is_array($member) ? $member['user_id'] : $member,
                ]);
            }

            $project->load(['manager', 'client', 'members.user']);

            return response()->json($project, 201);
        });
    }

    /**
     * Update a project.
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        if ($project->tenant_id !== $tenantId) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:draft,active,on_hold,completed,cancelled',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'manager_id' => 'nullable|exists:users,id',
            'client_id' => 'nullable|exists:customers,id',
            'tags' => 'nullable|array',
        ]);

        $project->update($validated);

        // Handle completion
        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $project->update(['completed_at' => now()]);
        }

        $project->load(['manager', 'client']);

        return response()->json($project);
    }

    /**
     * Delete a project.
     */
    public function destroy(Project $project): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        if ($project->tenant_id !== $tenantId) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully.']);
    }

    /**
     * Add member to project.
     */
    public function addMember(Request $request, Project $project): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        if ($project->tenant_id !== $tenantId) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Check if already a member
        $existing = $project->members()->where('user_id', $validated['user_id'])->first();
        if ($existing) {
            throw ValidationException::withMessages(['user_id' => ['User is already a member of this project.']]);
        }

        $member = $project->members()->create([
            'tenant_id' => $tenantId,
            'user_id' => $validated['user_id'],
        ]);

        $member->load('user');

        return response()->json($member, 201);
    }

    /**
     * Remove member from project.
     */
    public function removeMember(Project $project, int $userId): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        if ($project->tenant_id !== $tenantId) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $member = $project->members()->where('user_id', $userId)->first();
        
        if (!$member) {
            return response()->json(['message' => 'Member not found.'], 404);
        }

        $member->delete();

        return response()->json(['message' => 'Member removed successfully.']);
    }

    /**
     * Get project summary statistics.
     */
    public function summary(Project $project): JsonResponse
    {
        $tenantId = app(\App\Support\Tenancy\TenantContext::class)->id();

        if ($project->tenant_id !== $tenantId) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $summary = [
            'project' => $project,
            'total_tasks' => $project->tasks()->count(),
            'completed_tasks' => $project->tasks()->where('status', 'done')->count(),
            'active_tasks' => $project->activeTasks()->count(),
            'overdue_tasks' => $project->tasks()->overdue()->count(),
            'total_milestones' => $project->milestones()->count(),
            'completed_milestones' => $project->milestones()->where('status', 'completed')->count(),
            'total_hours' => $project->total_hours,
            'billable_hours' => $project->billable_hours,
            'completion_percentage' => $project->completion_percentage,
            'budget_vs_spent' => [
                'budget' => $project->budget,
                'spent' => $project->spent,
                'remaining' => $project->budget ? $project->budget - $project->spent : null,
            ],
        ];

        return response()->json($summary);
    }
}
