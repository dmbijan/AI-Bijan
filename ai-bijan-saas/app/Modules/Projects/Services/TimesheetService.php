<?php

namespace App\Modules\Projects\Services;

use App\Modules\Projects\Models\Timesheet;
use App\Modules\Projects\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TimesheetService
{
    /**
     * Get timesheets for a project.
     */
    public function getProjectTimesheets(int $projectId): \Illuminate\Database\Eloquent\Collection
    {
        return Timesheet::where('project_id', $projectId)
            ->with(['user', 'task', 'approver'])
            ->orderBy('work_date', 'desc')
            ->get();
    }

    /**
     * Get timesheet by ID.
     */
    public function getTimesheet(int $timesheetId, int $tenantId): ?Timesheet
    {
        return Timesheet::where('id', $timesheetId)
            ->where('tenant_id', $tenantId)
            ->with(['user', 'task', 'project'])
            ->first();
    }

    /**
     * Get timesheets for a user in a date range.
     */
    public function getUserTimesheets(int $userId, ?int $projectId = null, ?string $startDate = null, ?string $endDate = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Timesheet::where('user_id', $userId)
            ->with(['project', 'task']);

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($startDate) {
            $query->where('work_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('work_date', '<=', $endDate);
        }

        return $query->orderBy('work_date')->get();
    }

    /**
     * Create a new timesheet entry.
     */
    public function createTimesheet(array $data, int $tenantId, int $userId): Timesheet
    {
        return DB::transaction(function () use ($data, $tenantId, $userId) {
            // Check daily hours limit
            $dailyTotal = Timesheet::where('user_id', $userId)
                ->where('work_date', $data['work_date'])
                ->whereIn('status', ['draf', 'dihantar', 'diluluskan'])
                ->sum('hours');

            $newHours = $data['hours'];
            if (bccomp((string)$dailyTotal, (string)$newHours, 2) > 0 
                || bccomp((string)bcadd((string)$dailyTotal, (string)$newHours, 2), '24', 2) > 0) {
                throw ValidationException::withMessages([
                    'hours' => ['Total hours for the day cannot exceed 24.']
                ]);
            }

            $timesheet = Timesheet::create([
                'tenant_id' => $tenantId,
                'project_id' => $data['project_id'],
                'task_id' => $data['task_id'] ?? null,
                'user_id' => $userId,
                'work_date' => $data['work_date'],
                'hours' => $data['hours'],
                'note' => $data['note'] ?? null,
                'billable' => $data['billable'] ?? true,
                'status' => 'draf',
            ]);

            return $timesheet->fresh(['project', 'task']);
        });
    }

    /**
     * Update a timesheet entry.
     */
    public function updateTimesheet(Timesheet $timesheet, array $data, int $tenantId): Timesheet
    {
        return DB::transaction(function () use ($timesheet, $data, $tenantId) {
            if ($timesheet->tenant_id !== $tenantId) {
                throw new \Exception('Timesheet not found.');
            }

            // Cannot update approved timesheets
            if ($timesheet->status === 'diluluskan') {
                throw ValidationException::withMessages([
                    'update' => ['Cannot update an approved timesheet.']
                ]);
            }

            $updatable = ['hours', 'note', 'task_id', 'billable'];

            foreach ($updatable as $field) {
                if (isset($data[$field])) {
                    $timesheet->{$field} = $data[$field];
                }
            }

            $timesheet->save();

            return $timesheet->fresh(['project', 'task']);
        });
    }

    /**
     * Submit timesheet for approval.
     */
    public function submitForApproval(Timesheet $timesheet): Timesheet
    {
        if ($timesheet->status !== 'draf') {
            throw ValidationException::withMessages([
                'submit' => ['Only draft timesheets can be submitted.']
            ]);
        }

        $timesheet->update(['status' => 'dihantar']);

        return $timesheet->fresh();
    }

    /**
     * Approve a timesheet.
     */
    public function approveTimesheet(Timesheet $timesheet, int $approverId): Timesheet
    {
        if ($timesheet->status !== 'dihantar') {
            throw ValidationException::withMessages([
                'approve' => ['Only submitted timesheets can be approved.']
            ]);
        }

        $timesheet->update([
            'status' => 'diluluskan',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        return $timesheet->fresh(['approver']);
    }

    /**
     * Reject a timesheet.
     */
    public function rejectTimesheet(Timesheet $timesheet, int $approverId, string $reason): Timesheet
    {
        if ($timesheet->status !== 'dihantar') {
            throw ValidationException::withMessages([
                'reject' => ['Only submitted timesheets can be rejected.']
            ]);
        }

        $timesheet->update([
            'status' => 'ditolak',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $timesheet->fresh();
    }

    /**
     * Delete a timesheet.
     */
    public function deleteTimesheet(Timesheet $timesheet): bool
    {
        if ($timesheet->status === 'diluluskan') {
            throw ValidationException::withMessages([
                'delete' => ['Cannot delete an approved timesheet.']
            ]);
        }

        return $timesheet->delete();
    }

    /**
     * Get timesheet summary for a project.
     */
    public function getProjectSummary(int $projectId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = Timesheet::where('project_id', $projectId)
            ->where('status', 'diluluskan');

        if ($startDate) {
            $query->where('work_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('work_date', '<=', $endDate);
        }

        $totalHours = $query->sum('hours');
        $billableHours = (clone $query)->where('billable', true)->sum('hours');
        $nonBillableHours = (clone $query)->where('billable', false)->sum('hours');
        $entryCount = (clone $query)->count();

        // Calculate billable amount (using default rate from config)
        $rate = config('projects.timesheet.billable_rates.default', 100);
        $billableAmount = round($billableHours * $rate, 2);

        return [
            'total_hours' => round($totalHours, 2),
            'billable_hours' => round($billableHours, 2),
            'non_billable_hours' => round($nonBillableHours, 2),
            'entry_count' => $entryCount,
            'billable_amount' => $billableAmount,
        ];
    }

    /**
     * Get weekly timesheet report for a user.
     */
    public function getWeeklyReport(int $userId, int $weekNumber, int $year): array
    {
        $startDate = date('Y-m-d', mktime(0, 0, 0, 1, 1, $year) + (($weekNumber - 1) * 7 * 24 * 60 * 60));
        $endDate = date('Y-m-d', strtotime($startDate . ' +6 days'));

        $timesheets = $this->getUserTimesheets($userId, null, $startDate, $endDate);

        $dailyTotals = [];
        foreach ($timesheets as $ts) {
            $date = $ts->work_date;
            if (!isset($dailyTotals[$date])) {
                $dailyTotals[$date] = [
                    'date' => $date,
                    'hours' => 0,
                    'billable_hours' => 0,
                    'entries' => 0,
                ];
            }
            $dailyTotals[$date]['hours'] += $ts->hours;
            if ($ts->billable) {
                $dailyTotals[$date]['billable_hours'] += $ts->hours;
            }
            $dailyTotals[$date]['entries']++;
        }

        return [
            'week_number' => $weekNumber,
            'year' => $year,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'daily_totals' => array_values($dailyTotals),
            'total_hours' => round(array_sum(array_column($dailyTotals, 'hours')), 2),
            'total_billable_hours' => round(array_sum(array_column($dailyTotals, 'billable_hours')), 2),
        ];
    }

    /**
     * Bulk approve timesheets.
     */
    public function bulkApprove(array $timesheetIds, int $approverId, int $tenantId): int
    {
        return Timesheet::where('tenant_id', $tenantId)
            ->whereIn('id', $timesheetIds)
            ->where('status', 'dihantar')
            ->update([
                'status' => 'diluluskan',
                'approved_by' => $approverId,
                'approved_at' => now(),
            ]);
    }

    /**
     * Bulk reject timesheets.
     */
    public function bulkReject(array $timesheetIds, int $approverId, string $reason, int $tenantId): int
    {
        return Timesheet::where('tenant_id', $tenantId)
            ->whereIn('id', $timesheetIds)
            ->where('status', 'dihantar')
            ->update([
                'status' => 'ditolak',
                'approved_by' => $approverId,
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);
    }
}
