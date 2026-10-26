<?php

namespace App\Modules\HRMS\Services;

use App\Modules\HRMS\Models\Attendance;
use App\Modules\HRMS\Models\Employee;
use App\Modules\HRMS\Models\Holiday;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceService
{
    /**
     * Check in for an employee.
     */
    public function checkIn(int $employeeId, array $data): Attendance
    {
        return DB::transaction(function () use ($employeeId, $data) {
            $today = now()->toDateString();

            // Get or create today's attendance record
            $attendance = Attendance::firstOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date' => $today,
                ],
                [
                    'check_in_method' => $data['method'] ?? 'manual',
                    'check_in_location' => $data['location'] ?? null,
                    'check_in_photo' => $data['photo'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]
            );

            if ($attendance->hasCheckedIn()) {
                throw new \Exception('Employee has already checked in today.');
            }

            $attendance->update([
                'check_in_time' => now()->format('H:i:s'),
                'check_in_method' => $data['method'] ?? 'manual',
                'check_in_location' => $data['location'] ?? null,
                'check_in_photo' => $data['photo'] ?? null,
            ]);

            // Calculate minutes late if shift is assigned
            $currentShift = $attendance->employee->currentShift;
            if ($currentShift && $currentShift->shift) {
                $expectedTime = $today . ' ' . $currentShift->shift->start_time;
                $actualTime = strtotime($attendance->check_in_time);
                $expectedTime = strtotime($expectedTime);

                if ($actualTime > $expectedTime) {
                    $minutesLate = floor(($actualTime - $expectedTime) / 60);
                    $attendance->update(['minutes_late' => $minutesLate]);

                    // Update status to late if exceeds grace period (15 mins default)
                    if ($minutesLate > config('aibijan.hrms.working_hours.grace_period_minutes', 15)) {
                        $attendance->update(['status' => 'late']);
                    }
                }
            }

            return $attendance->fresh();
        });
    }

    /**
     * Check out for an employee.
     */
    public function checkOut(int $employeeId): Attendance
    {
        return DB::transaction(function () use ($employeeId) {
            $today = now()->toDateString();

            $attendance = Attendance::where('employee_id', $employeeId)
                ->where('date', $today)
                ->first();

            if (!$attendance || !$attendance->hasCheckedIn()) {
                throw new \Exception('No check-in record found for today.');
            }

            if ($attendance->hasCheckedOut()) {
                throw new \Exception('Employee has already checked out today.');
            }

            $attendance->update([
                'check_out_time' => now()->format('H:i:s'),
                'check_out_location' => request('location'),
            ]);

            // Calculate total hours
            $attendance->calculateTotalHours();

            // Calculate overtime
            $currentShift = $attendance->employee->currentShift;
            if ($currentShift && $currentShift->shift) {
                $netWorkingHours = $currentShift->shift->net_working_hours;
                $totalHours = $attendance->total_hours;

                if ($totalHours > $netWorkingHours) {
                    $overtimeHours = round($totalHours - $netWorkingHours, 2);
                    $attendance->update(['overtime_hours' => $overtimeHours]);
                }
            }

            return $attendance->fresh();
        });
    }

    /**
     * Get attendance records for a date range.
     */
    public function getAttendanceRecords(int $tenantId, string $startDate, string $endDate, ?int $departmentId = null): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Attendance::whereHas('employee', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        })
        ->whereBetween('date', [$startDate, $endDate])
        ->with(['employee.department', 'employee.user']);

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        return $query->orderBy('date')->orderBy('employee_id')->paginate(50);
    }

    /**
     * Get daily attendance summary.
     */
    public function getDailySummary(string $date): array
    {
        $total = Attendance::where('date', $date)->count();
        $present = Attendance::where('date', $date)->where('status', 'present')->count();
        $late = Attendance::where('date', $date)->where('status', 'late')->count();
        $absent = Attendance::where('date', $date)->where('status', 'absent')->count();
        $onLeave = Attendance::where('date', $date)->where('status', 'on_leave')->count();
        $holiday = Holiday::where('date', $date)->count() > 0;

        return [
            'date' => $date,
            'total_records' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'on_leave' => $onLeave,
            'is_holiday' => $holiday,
            'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Mark attendance manually (for admin).
     */
    public function markAttendance(int $employeeId, string $date, string $status, array $data = []): Attendance
    {
        return DB::transaction(function () use ($employeeId, $date, $status, $data) {
            return Attendance::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'date' => $date,
                ],
                array_merge([
                    'status' => $status,
                    'check_in_time' => $data['check_in_time'] ?? null,
                    'check_out_time' => $data['check_out_time'] ?? null,
                    'total_hours' => $data['total_hours'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ], $data)
            );
        });
    }

    /**
     * Get monthly attendance report for an employee.
     */
    public function getMonthlyReport(int $employeeId, int $month, int $year): array
    {
        $records = Attendance::where('employee_id', $employeeId)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $totalDays = $records->count();
        $presentDays = $records->where('status', 'present')->count();
        $lateDays = $records->where('status', 'late')->count();
        $absentDays = $records->where('status', 'absent')->count();
        $leaveDays = $records->where('status', 'on_leave')->count();
        $holidayDays = $records->where('status', 'holiday')->count();
        $totalHours = $records->sum('total_hours');
        $totalOvertime = $records->sum('overtime_hours');
        $totalMinutesLate = $records->sum('minutes_late');

        return [
            'month' => $month,
            'year' => $year,
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'absent_days' => $absentDays,
            'leave_days' => $leaveDays,
            'holiday_days' => $holidayDays,
            'total_hours_worked' => round($totalHours, 2),
            'total_overtime_hours' => round($totalOvertime, 2),
            'total_minutes_late' => $totalMinutesLate,
            'average_hours_per_day' => $presentDays > 0 ? round($totalHours / $presentDays, 2) : 0,
        ];
    }

    /**
     * Bulk mark attendance for multiple employees.
     */
    public function bulkMarkAttendance(array $records, string $date): array
    {
        $created = 0;
        $errors = [];

        foreach ($records as $record) {
            try {
                $validator = Validator::make($record, [
                    'employee_id' => 'required|integer',
                    'status' => 'required|in:present,absent,late,half_day,on_leave,holiday',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Invalid record: " . $validator->errors()->first();
                    continue;
                }

                $record['date'] = $date;
                Attendance::updateOrCreate(
                    ['employee_id' => $record['employee_id'], 'date' => $date],
                    $record
                );
                $created++;

            } catch (\Exception $e) {
                $errors[] = "Error processing employee {$record['employee_id']}: " . $e->getMessage();
            }
        }

        return ['created' => $created, 'errors' => $errors];
    }
}
