<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\Timesheet;
use App\Modules\Projects\Services\TimesheetService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TimesheetController extends Controller
{
    protected $timesheetService;

    public function __construct(TimesheetService $timesheetService)
    {
        $this->timesheetService = $timesheetService;
    }

    /**
     * List all timesheets for a project.
     */
    public function index(Project $project, Request $request): JsonResponse
    {
        if ($project->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $query = $project->timesheets()
            ->with(['user', 'task', 'approver']);

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('work_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('work_date', '<=', $request->end_date);
        }

        $timesheets = $query->orderBy('work_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($timesheets);
    }

    /**
     * Show a specific timesheet.
     */
    public function show(Timesheet $timesheet): JsonResponse
    {
        if ($timesheet->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Timesheet not found.'], 404);
        }

        $timesheet->load(['user', 'task', 'project', 'approver']);

        return response()->json($timesheet);
    }

    /**
     * Create a new timesheet entry.
     */
    public function store(Request $request, Project $project): JsonResponse
    {
        if ($project->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $validated = $request->validate([
            'work_date' => 'required|date',
            'hours' => 'required|numeric|min:0.5|max:24',
            'task_id' => 'nullable|exists:tasks,id',
            'note' => 'nullable|string|max:1000',
            'billable' => 'sometimes|boolean',
        ]);

        $timesheet = $this->timesheetService->createTimesheet(
            array_merge($validated, ['project_id' => $project->id]),
            app(\App\Support\Tenancy\TenantContext::class)->id(),
            Auth::id()
        );

        return response()->json($timesheet, 201);
    }

    /**
     * Update a timesheet entry.
     */
    public function update(Request $request, Timesheet $timesheet): JsonResponse
    {
        if ($timesheet->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Timesheet not found.'], 404);
        }

        // Users can only update their own timesheets (unless they're admin/manager)
        if ($timesheet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'hours' => 'sometimes|numeric|min:0.5|max:24',
            'task_id' => 'nullable|exists:tasks,id',
            'note' => 'nullable|string|max:1000',
            'billable' => 'sometimes|boolean',
        ]);

        $timesheet = $this->timesheetService->updateTimesheet($timesheet, $validated, app(\App\Support\Tenancy\TenantContext::class)->id());

        return response()->json($timesheet);
    }

    /**
     * Submit timesheet for approval.
     */
    public function submit(Timesheet $timesheet): JsonResponse
    {
        if ($timesheet->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Timesheet not found.'], 404);
        }

        if ($timesheet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $timesheet = $this->timesheetService->submitForApproval($timesheet);

        return response()->json($timesheet);
    }

    /**
     * Approve a timesheet.
     */
    public function approve(Timesheet $timesheet): JsonResponse
    {
        if ($timesheet->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Timesheet not found.'], 404);
        }

        $timesheet = $this->timesheetService->approveTimesheet($timesheet, Auth::id());

        return response()->json($timesheet);
    }

    /**
     * Reject a timesheet.
     */
    public function reject(Request $request, Timesheet $timesheet): JsonResponse
    {
        if ($timesheet->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Timesheet not found.'], 404);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $timesheet = $this->timesheetService->rejectTimesheet($timesheet, Auth::id(), $validated['reason']);

        return response()->json($timesheet);
    }

    /**
     * Delete a timesheet entry.
     */
    public function destroy(Timesheet $timesheet): JsonResponse
    {
        if ($timesheet->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Timesheet not found.'], 404);
        }

        // Users can only delete their own timesheets (unless they're admin/manager)
        if ($timesheet->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $this->timesheetService->deleteTimesheet($timesheet);

        return response()->json(['message' => 'Timesheet deleted successfully.']);
    }

    /**
     * Get timesheet summary for a project.
     */
    public function summary(Project $project, Request $request): JsonResponse
    {
        if ($project->tenant_id !== app(\App\Support\Tenancy\TenantContext::class)->id()) {
            return response()->json(['message' => 'Project not found.'], 404);
        }

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $summary = $this->timesheetService->getProjectSummary($project->id, $startDate, $endDate);

        return response()->json($summary);
    }

    /**
     * Get weekly report for current user.
     */
    public function myWeeklyReport(Request $request): JsonResponse
    {
        $weekNumber = $request->input('week_number', now()->week);
        $year = $request->input('year', now()->year);

        $report = $this->timesheetService->getWeeklyReport(Auth::id(), $weekNumber, $year);

        return response()->json($report);
    }

    /**
     * Get timesheets for current user.
     */
    public function myTimesheets(Request $request): JsonResponse
    {
        $projectId = $request->input('project_id');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $timesheets = $this->timesheetService->getUserTimesheets(
            Auth::id(),
            $projectId,
            $startDate,
            $endDate
        );

        return response()->json($timesheets);
    }
}
