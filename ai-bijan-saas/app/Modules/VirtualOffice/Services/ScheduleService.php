<?php

namespace App\Modules\VirtualOffice\Services;

use App\Modules\VirtualOffice\Models\Schedule;
use App\Modules\VirtualOffice\Models\ScheduleAttendee;
use Illuminate\Support\Facades\DB;

class ScheduleService
{
    /**
     * Create a new schedule.
     */
    public function create(array $data, int $tenantId): Schedule
    {
        return DB::transaction(function () use ($data, $tenantId) {
            $schedule = Schedule::create([
                'tenant_id' => $tenantId,
                'agent_id' => $data['agent_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'type' => $data['type'] ?? 'meeting',
                'is_recurring' => $data['is_recurring'] ?? false,
                'recurrence_pattern' => $data['recurrence_pattern'] ?? null,
                'location' => $data['location'] ?? null,
                'meeting_link' => $data['meeting_link'] ?? null,
                'attendees' => $data['attendees'] ?? [],
            ]);

            // Add attendees if provided
            if (isset($data['attendees'])) {
                foreach ($data['attendees'] as $userId) {
                    $schedule->attendees()->create([
                        'user_id' => $userId,
                        'response' => 'pending',
                    ]);
                }
            }

            return $schedule;
        });
    }

    /**
     * Update an existing schedule.
     */
    public function update(Schedule $schedule, array $data): Schedule
    {
        $schedule->update($data);
        return $schedule->fresh();
    }

    /**
     * Cancel a schedule.
     */
    public function cancelSchedule(Schedule $schedule): Schedule
    {
        $schedule->cancel();
        return $schedule->fresh();
    }

    /**
     * Complete a schedule.
     */
    public function completeSchedule(Schedule $schedule): Schedule
    {
        $schedule->complete();
        return $schedule->fresh();
    }

    /**
     * Respond to schedule invitation.
     */
    public function respondToSchedule(Schedule $schedule, int $userId, string $response): ScheduleAttendee
    {
        $attendee = $schedule->attendees()->where('user_id', $userId)->first();

        if ($attendee) {
            $attendee->update([
                'response' => $response,
                'responded_at' => now(),
            ]);
        } else {
            $attendee = $schedule->attendees()->create([
                'user_id' => $userId,
                'response' => $response,
                'responded_at' => now(),
            ]);
        }

        return $attendee;
    }

    /**
     * Get user's upcoming schedules.
     */
    public function getUserSchedules(int $tenantId, int $userId, ?int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Schedule::where('tenant_id', $tenantId)
            ->where(function ($query) use ($userId) {
                $query->whereHas('attendees', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->orWhereHas('agent', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            })
            ->where('status', 'scheduled')
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->limit($limit)
            ->get();
    }

    /**
     * Get today's schedules for a tenant.
     */
    public function getTodaySchedules(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        return Schedule::where('tenant_id', $tenantId)
            ->whereBetween('start_time', [$todayStart, $todayEnd])
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get schedules by date range.
     */
    public function getSchedulesByRange(int $tenantId, string $startDate, string $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return Schedule::where('tenant_id', $tenantId)
            ->whereBetween('start_time', [$startDate, $endDate])
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get agent's schedule for a day.
     */
    public function getAgentScheduleForDay(int $agentId, string $date): \Illuminate\Database\Eloquent\Collection
    {
        $dayStart = \Carbon\Carbon::parse($date)->startOfDay();
        $dayEnd = \Carbon\Carbon::parse($date)->endOfDay();

        return Schedule::where('agent_id', $agentId)
            ->whereBetween('start_time', [$dayStart, $dayEnd])
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Check scheduling conflicts.
     */
    public function hasConflict(int $agentId, string $startTime, string $endTime): bool
    {
        return Schedule::where('agent_id', $agentId)
            ->whereNotIn('status', ['cancelled'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                      ->orWhereBetween('end_time', [$startTime, $endTime])
                      ->orWhere(function ($q) use ($startTime, $endTime) {
                          $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                      });
            })
            ->exists();
    }

    /**
     * Get schedule statistics for tenant.
     */
    public function getTenantStatistics(int $tenantId): array
    {
        $now = now();

        return [
            'total_scheduled' => Schedule::where('tenant_id', $tenantId)
                ->where('status', 'scheduled')->count(),
            'completed' => Schedule::where('tenant_id', $tenantId)
                ->where('status', 'completed')->count(),
            'cancelled' => Schedule::where('tenant_id', $tenantId)
                ->where('status', 'cancelled')->count(),
            'today_count' => Schedule::where('tenant_id', $tenantId)
                ->whereDate('start_time', $now->toDateString())
                ->whereNotIn('status', ['cancelled'])->count(),
            'this_week_count' => Schedule::where('tenant_id', $tenantId)
                ->whereBetween('start_time', [
                    $now->startOfWeek(),
                    $now->endOfWeek(),
                ])->whereNotIn('status', ['cancelled'])->count(),
        ];
    }
}
