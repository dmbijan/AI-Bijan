<?php

namespace App\Modules\HRMS\Services;

use App\Modules\HRMS\Models\Employee;
use App\Modules\HRMS\Models\Department;
use App\Modules\HRMS\Models\Designation;
use App\Modules\HRMS\Models\EmployeeShift;
use App\Modules\HRMS\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmployeeService
{
    /**
     * Get all employees for a tenant.
     */
    public function listEmployees(int $tenantId, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Employee::where('tenant_id', $tenantId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['employment_type'])) {
            $query->where('employment_type', $filters['employment_type']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_no', 'like', "%{$search}%")
                  ->orWhere('ic_number', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('last_name')->paginate(20);
    }

    /**
     * Get employee by ID.
     */
    public function getEmployee(int $tenantId, int $employeeId): ?Employee
    {
        return Employee::where('tenant_id', $tenantId)
            ->with(['department', 'designation', 'user', 'currentShift'])
            ->find($employeeId);
    }

    /**
     * Create a new employee.
     */
    public function create(array $data, int $tenantId): Employee
    {
        return DB::transaction(function () use ($data, $tenantId) {
            // Generate employee number
            $data['employee_no'] = Employee::generateEmployeeNo();
            $data['tenant_id'] = $tenantId;

            $employee = Employee::create($data);

            // If user_id provided, link them
            if (!empty($data['user_id'])) {
                $employee->update(['user_id' => $data['user_id']]);
            }

            return $employee->fresh(['department', 'designation']);
        });
    }

    /**
     * Update an existing employee.
     */
    public function update(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $employee->update($data);
            return $employee->fresh(['department', 'designation']);
        });
    }

    /**
     * Terminate an employee.
     */
    public function terminate(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $employee->update([
                'status' => 'terminated',
                'last_worked_date' => $data['last_worked_date'] ?? now()->toDateString(),
                'termination_reason' => $data['termination_reason'] ?? null,
            ]);

            // Deactivate associated user if exists
            if ($employee->user_id) {
                $employee->user->update(['is_active' => false]);
            }

            return $employee;
        });
    }

    /**
     * Assign shift to employee.
     */
    public function assignShift(int $employeeId, int $shiftId, string $effectiveFrom, ?string $effectiveTo = null): EmployeeShift
    {
        return DB::transaction(function () use ($employeeId, $shiftId, $effectiveFrom, $effectiveTo) {
            // End any current shift without end date
            EmployeeShift::where('employee_id', $employeeId)
                ->whereNull('effective_to')
                ->update(['effective_to' => $effectiveFrom]);

            return EmployeeShift::create([
                'employee_id' => $employeeId,
                'shift_id' => $shiftId,
                'effective_from' => $effectiveFrom,
                'effective_to' => $effectiveTo,
            ]);
        });
    }

    /**
     * Get employee attendance summary for a period.
     */
    public function getAttendanceSummary(int $employeeId, string $startDate, string $endDate): array
    {
        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $totalDays = $attendance->count();
        $presentDays = $attendance->where('status', 'present')->count();
        $lateDays = $attendance->where('status', 'late')->count();
        $absentDays = $attendance->where('status', 'absent')->count();
        $leaveDays = $attendance->where('status', 'on_leave')->count();
        $totalHours = $attendance->sum('total_hours');
        $totalOvertime = $attendance->sum('overtime_hours');
        $totalMinutesLate = $attendance->sum('minutes_late');

        return [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'absent_days' => $absentDays,
            'leave_days' => $leaveDays,
            'total_hours' => round($totalHours, 2),
            'total_overtime_hours' => round($totalOvertime, 2),
            'total_minutes_late' => $totalMinutesLate,
            'average_hours_per_day' => $presentDays > 0 ? round($totalHours / $presentDays, 2) : 0,
        ];
    }

    /**
     * Get department statistics.
     */
    public function getDepartmentStats(int $tenantId): array
    {
        $departments = Department::where('tenant_id', $tenantId)
            ->withCount(['employees' => function ($query) {
                $query->where('status', 'active');
            }])
            ->with(['children' => function ($query) {
                $query->withCount(['employees' => function ($q) {
                    $q->where('status', 'active');
                }]);
            }])
            ->get();

        $stats = [];
        foreach ($departments as $dept) {
            $stats[$dept->id] = [
                'id' => $dept->id,
                'name' => $dept->name,
                'code' => $dept->code,
                'employee_count' => $dept->employees_count,
                'manager' => $dept->manager?->name,
                'children_count' => $dept->children->count(),
                'child_employees' => $dept->children->sum('employees_count'),
            ];
        }

        return $stats;
    }

    /**
     * Bulk import employees from CSV data.
     */
    public function bulkImport(array $records, int $tenantId): array
    {
        $created = 0;
        $errors = [];

        foreach ($records as $index => $record) {
            try {
                $validator = Validator::make($record, [
                    'first_name' => 'required|string|max:255',
                    'email' => 'required|email|unique:hrms_employees,email',
                    'employment_type' => 'required|in:full_time,part_time,contract,freelance,intern',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Row " . ($index + 1) . ": " . $validator->errors()->first();
                    continue;
                }

                $record['tenant_id'] = $tenantId;
                Employee::create($record);
                $created++;

            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        return ['created' => $created, 'errors' => $errors];
    }

    /**
     * Get employees with expiring contracts (within 30 days).
     */
    public function getExpiringContracts(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Employee::where('tenant_id', $tenantId)
            ->whereNotNull('contract_end_date')
            ->where('contract_end_date', '>=', now())
            ->where('contract_end_date', '<=', now()->addDays(30))
            ->where('status', 'active')
            ->with(['department', 'designation'])
            ->get();
    }

    /**
     * Get employees on probation.
     */
    public function getProbationers(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Employee::where('tenant_id', $tenantId)
            ->whereNotNull('probation_end_date')
            ->where('probation_end_date', '>=', now())
            ->where('status', 'active')
            ->with(['department', 'designation'])
            ->get();
    }
}
