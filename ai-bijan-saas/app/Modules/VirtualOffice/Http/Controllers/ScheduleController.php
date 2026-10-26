<?php

namespace App\Modules\VirtualOffice\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\VirtualOffice\Services\ScheduleService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * List schedules for the tenant.
     */
    public function index(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();
        $userId = $request->user()->id;
        $startDate = $request->query('start_date', now()->toDateString());
        $endDate = $request->query('end_date', now()->addDays(7)->toDateString());

        $schedules = $this->scheduleService->getSchedulesByRange($tenantId, $startDate . ' 00:00:00', $endDate . ' 23:59:59');

        return response()->json($schedules);
    }

    /**
     * Get a single schedule.
     */
    public function show(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $schedule = \App\Modules\VirtualOffice\Models\Schedule::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->with(['agent', 'attendees.user'])
            ->first();

        if (!$schedule) {
            return response()->json(['message' => 'Schedule not found'], 404);
        }

        return response()->json($schedule);
    }

    /**
     * Create a new schedule.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'type' => 'sometimes|in:meeting,standup,review,planning,social,other',
            'is_recurring' => 'sometimes|boolean',
            'recurrence_pattern' => 'nullable|array',
            'location' => 'nullable|string',
            'meeting_link' => 'nullable|url',
            'agent_id' => 'nullable|exists:vo_agents,id',
            'attendees' => 'nullable|array',
        ]);

        $tenantId = app(TenantContext::class)->id();

        // Check for scheduling conflicts
        $hasConflict = $this->scheduleService->hasConflict(
            $request->agent_id ?? 0,
            $request->start_time,
            $request->end_time
        );

        if ($hasConflict) {
            return response()->json(['message' => 'Scheduling conflict detected'], 409);
        }

        $schedule = $this->scheduleService->create(array_merge($request->all(), [
            'tenant_id' => $tenantId,
        ]), $tenantId);

        return response()->json($schedule, 201);
    }

    /**
     * Update a schedule.
     */
    public function update(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $schedule = \App\Modules\VirtualOffice\Models\Schedule::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'type' => 'sometimes|in:meeting,standup,review,planning,social,other',
            'is_recurring' => 'sometimes|boolean',
            'recurrence_pattern' => 'nullable|array',
            'location' => 'nullable|string',
            'meeting_link' => 'nullable|url',
            'status' => 'sometimes|in:scheduled,in_progress,completed,cancelled,no_show',
        ]);

        $schedule = $this->scheduleService->update($schedule, $request->all());

        return response()->json($schedule);
    }

    /**
     * Cancel a schedule.
     */
    public function cancel(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $schedule = \App\Modules\VirtualOffice\Models\Schedule::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $schedule = $this->scheduleService->cancelSchedule($schedule);

        return response()->json($schedule);
    }

    /**
     * Complete a schedule.
     */
    public function complete(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $schedule = \App\Modules\VirtualOffice\Models\Schedule::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $schedule = $this->scheduleService->completeSchedule($schedule);

        return response()->json($schedule);
    }

    /**
     * Respond to schedule invitation.
     */
    public function respond(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();
        $userId = $request->user()->id;

        $schedule = \App\Modules\VirtualOffice\Models\Schedule::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $request->validate([
            'response' => 'required|in:accepted,declined,tentative',
        ]);

        $attendee = $this->scheduleService->respondToSchedule(
            $schedule,
            $userId,
            $request->response
        );

        return response()->json($attendee);
    }

    /**
     * Get today's schedules.
     */
    public function today(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();

        $schedules = $this->scheduleService->getTodaySchedules($tenantId);

        return response()->json($schedules);
    }

    /**
     * Get upcoming schedules for user.
     */
    public function upcoming(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();
        $userId = $request->user()->id;
        $limit = $request->query('limit', 10);

        $schedules = $this->scheduleService->getUserSchedules($tenantId, $userId, $limit);

        return response()->json($schedules);
    }

    /**
     * Get schedule statistics.
     */
    public function statistics(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();

        $stats = $this->scheduleService->getTenantStatistics($tenantId);

        return response()->json($stats);
    }

    /**
     * Delete a schedule.
     */
    public function destroy(Request $request, int $id)
    {
        $tenantId = app(TenantContext::class)->id();

        $schedule = \App\Modules\VirtualOffice\Models\Schedule::where('id', $id)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $schedule->delete();

        return response()->json(['message' => 'Schedule deleted successfully']);
    }
}
