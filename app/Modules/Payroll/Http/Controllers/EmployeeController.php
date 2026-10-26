<?php

namespace App\Modules\Payroll\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Payroll\Models\Employee;
use Illuminate\Http\Request;

/**
 * B-Payroll Employee Controller
 * 
 * Manages employee master data for payroll processing.
 */
class EmployeeController extends Controller
{
    /**
     * List all employees for current tenant.
     */
    public function index(Request $request)
    {
        $query = Employee::where('tenant_id', $request->tenant_id)
            ->orderBy('name');

        if ($request->active_only) {
            $query->where('is_active', true);
        }

        return response()->json($query->paginate(20));
    }

    /**
     * Store a new employee.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ic_no' => 'required|string|unique:employees,ic_no',
            'epf_no' => 'nullable|string|max:20',
            'socso_no' => 'nullable|string|max:20',
            'position' => 'required|string|max:80',
            'department' => 'nullable|string|max:80',
            'start_date' => 'nullable|date',
            'employment_type' => 'nullable|in:permanent,contract,part_time,casual',
            'basic_salary' => 'required|numeric|min:0',
            'fixed_allowance' => 'nullable|numeric|min:0',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:50',
        ]);

        $employee = Employee::create([
            ...$validated,
            'tenant_id' => $request->tenant_id,
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Employee created successfully',
            'data' => $employee,
        ], 201);
    }

    /**
     * Get employee details.
     */
    public function show(Request $request, int $employeeId)
    {
        $employee = Employee::where('tenant_id', $request->tenant_id)
            ->findOrFail($employeeId);

        return response()->json($employee);
    }

    /**
     * Update employee details.
     */
    public function update(Request $request, int $employeeId)
    {
        $employee = Employee::where('tenant_id', $request->tenant_id)
            ->findOrFail($employeeId);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'ic_no' => 'sometimes|string|unique:employees,ic_no,' . $employeeId,
            'epf_no' => 'nullable|string|max:20',
            'socso_no' => 'nullable|string|max:20',
            'position' => 'sometimes|string|max:80',
            'department' => 'nullable|string|max:80',
            'basic_salary' => 'sometimes|numeric|min:0',
            'fixed_allowance' => 'nullable|numeric|min:0',
            'end_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $employee->update($validated);

        return response()->json([
            'message' => 'Employee updated successfully',
            'data' => $employee->fresh(),
        ]);
    }

    /**
     * Deactivate an employee (soft end employment).
     */
    public function deactivate(Request $request, int $employeeId)
    {
        $employee = Employee::where('tenant_id', $request->tenant_id)
            ->findOrFail($employeeId);

        $employee->update([
            'is_active' => false,
            'end_date' => now(),
        ]);

        return response()->json([
            'message' => 'Employee deactivated successfully',
            'data' => $employee->fresh(),
        ]);
    }
}
