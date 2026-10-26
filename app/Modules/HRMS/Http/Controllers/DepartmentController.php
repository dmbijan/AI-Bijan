<?php

namespace App\Modules\HRMS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HRMS\Services\EmployeeService;
use App\Modules\HRMS\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    protected $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }

    /**
     * List all departments.
     */
    public function index(): JsonResponse
    {
        $departments = Department::where('tenant_id', Auth::tenant()->id)
            ->with(['manager', 'children'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $departments,
        ]);
    }

    /**
     * Get department details.
     */
    public function show(int $id): JsonResponse
    {
        $department = Department::where('tenant_id', Auth::tenant()->id)
            ->with(['manager', 'children', 'employees.user'])
            ->find($id);

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $department,
        ]);
    }

    /**
     * Create a new department.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:hrms_departments,code',
            'parent_id' => 'nullable|exists:hrms_departments,id',
            'manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $request->all();
            $data['tenant_id'] = Auth::tenant()->id;

            $department = Department::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Department created successfully.',
                'data' => $department,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create department.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing department.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $department = Department::where('tenant_id', Auth::tenant()->id)->find($id);

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:hrms_departments,code,' . $id,
            'parent_id' => 'nullable|exists:hrms_departments,id',
            'manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $department->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Department updated successfully.',
                'data' => $department->fresh(['manager']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update department.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a department.
     */
    public function destroy(int $id): JsonResponse
    {
        $department = Department::where('tenant_id', Auth::tenant()->id)->find($id);

        if (!$department) {
            return response()->json([
                'success' => false,
                'message' => 'Department not found.',
            ], 404);
        }

        // Check if department has employees
        if ($department->employees()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department with active employees.',
            ], 422);
        }

        try {
            $department->delete();

            return response()->json([
                'success' => true,
                'message' => 'Department deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete department.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get department statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->employeeService->getDepartmentStats(Auth::tenant()->id());

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
