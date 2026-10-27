<?php

namespace App\Modules\HRMS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HRMS\Services\EmployeeService;
use App\Modules\HRMS\Models\Employee;
use App\Modules\HRMS\Models\Department;
use App\Modules\HRMS\Models\Designation;
use App\Modules\HRMS\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EmployeeController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * List all employees with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'department_id', 'employment_type', 'search']);
        $employees = $this->employeeService->listEmployees(app(\App\Support\Tenancy\TenantContext::class)->id(), $filters);

        return response()->json([
            'success' => true,
            'data' => $employees,
        ]);
    }

    /**
     * Get employee details.
     */
    public function show(int $id): JsonResponse
    {
        $employee = $this->employeeService->getEmployee(app(\App\Support\Tenancy\TenantContext::class)->id(), $id);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $employee,
        ]);
    }

    /**
     * Create a new employee.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:hrms_employees,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'nationality' => 'nullable|string|max:100',
            'address_line1' => 'nullable|string|max:500',
            'address_line2' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:hrms_departments,id',
            'designation_id' => 'nullable|exists:hrms_designations,id',
            'employment_type' => 'required|in:full_time,part_time,contract,freelance,intern',
            'date_joined' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:date_joined',
            'probation_end_date' => 'nullable|date',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:50',
            'epf_no' => 'nullable|string|max:50',
            'soeso_no' => 'nullable|string|max:50',
            'eis_no' => 'nullable|string|max:50',
            'pcb_ref' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $employee = $this->employeeService->create($request->all(), app(\App\Support\Tenancy\TenantContext::class)->id());

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully.',
                'data' => $employee,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create employee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing employee.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $employee = Employee::where('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())->find($id);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'sometimes|email|unique:hrms_employees,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'nationality' => 'nullable|string|max:100',
            'address_line1' => 'nullable|string|max:500',
            'address_line2' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:hrms_departments,id',
            'designation_id' => 'nullable|exists:hrms_designations,id',
            'employment_type' => 'sometimes|in:full_time,part_time,contract,freelance,intern',
            'date_joined' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:date_joined',
            'probation_end_date' => 'nullable|date',
            'status' => 'sometimes|in:active,on_leave,suspended,terminated,resigned',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:50',
            'epf_no' => 'nullable|string|max:50',
            'soeso_no' => 'nullable|string|max:50',
            'eis_no' => 'nullable|string|max:50',
            'pcb_ref' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $employee = $this->employeeService->update($employee, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully.',
                'data' => $employee,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update employee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Terminate an employee.
     */
    public function terminate(Request $request, int $id): JsonResponse
    {
        $employee = Employee::where('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())->find($id);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'last_worked_date' => 'nullable|date',
            'termination_reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $employee = $this->employeeService->terminate($employee, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Employee terminated successfully.',
                'data' => $employee,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to terminate employee.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign shift to employee.
     */
    public function assignShift(Request $request, int $id): JsonResponse
    {
        $employee = Employee::where('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())->find($id);

        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'shift_id' => 'required|exists:hrms_shifts,id',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $shiftAssignment = $this->employeeService->assignShift(
                $id,
                $request->shift_id,
                $request->effective_from,
                $request->effective_to
            );

            return response()->json([
                'success' => true,
                'message' => 'Shift assigned successfully.',
                'data' => $shiftAssignment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign shift.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get employee attendance summary.
     */
    public function attendanceSummary(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $summary = $this->employeeService->getAttendanceSummary(
            $id,
            $request->start_date,
            $request->end_date
        );

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Get expiring contracts.
     */
    public function getExpiringContracts(): JsonResponse
    {
        $employees = $this->employeeService->getExpiringContracts(app(\App\Support\Tenancy\TenantContext::class)->id());

        return response()->json([
            'success' => true,
            'data' => $employees,
        ]);
    }

    /**
     * Get probationers.
     */
    public function getProbationers(): JsonResponse
    {
        $employees = $this->employeeService->getProbationers(app(\App\Support\Tenancy\TenantContext::class)->id());

        return response()->json([
            'success' => true,
            'data' => $employees,
        ]);
    }

    /**
     * Bulk import employees from CSV.
     */
    public function bulkImport(Request $request): JsonResponse
    {
        $request->validate([
            'records' => 'required|array',
            'records.*.first_name' => 'required|string|max:255',
            'records.*.email' => 'required|email',
            'records.*.employment_type' => 'required|in:full_time,part_time,contract,freelance,intern',
        ]);

        try {
            $result = $this->employeeService->bulkImport($request->records, app(\App\Support\Tenancy\TenantContext::class)->id());

            return response()->json([
                'success' => true,
                'message' => "Import completed.",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
