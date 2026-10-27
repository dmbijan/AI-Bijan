<?php

namespace App\Modules\HRMS\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\HRMS\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ShiftController extends Controller
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
    /**
     * List all shifts.
     */
    public function index(): JsonResponse
    {
        $shifts = Shift::where('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())
            ->with(['employeeShifts.employee'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $shifts,
        ]);
    }

    /**
     * Get shift details.
     */
    public function show(int $id): JsonResponse
    {
        $shift = Shift::where('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())->find($id);

        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'Shift not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $shift,
        ]);
    }

    /**
     * Create a new shift.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:hrms_shifts,code',
            'description' => 'nullable|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'working_hours' => 'nullable|integer|min:1|max:24',
            'working_days' => 'nullable|array',
            'type' => 'nullable|in:standard,rotating,flexible,night',
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
            $data['tenant_id'] = app(\App\Support\Tenancy\TenantContext::class)->id();

            // Default working hours if not provided
            if (!isset($data['working_hours'])) {
                $data['working_hours'] = 8;
            }

            // Default working days (Mon-Fri)
            if (!isset($data['working_days'])) {
                $data['working_days'] = [1, 2, 3, 4, 5];
            }

            $shift = Shift::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Shift created successfully.',
                'data' => $shift,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create shift.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing shift.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $shift = Shift::where('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())->find($id);

        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'Shift not found.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|unique:hrms_shifts,code,' . $id,
            'description' => 'nullable|string',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i|after:start_time',
            'break_start' => 'nullable|date_format:H:i',
            'break_end' => 'nullable|date_format:H:i|after:break_start',
            'working_hours' => 'nullable|integer|min:1|max:24',
            'working_days' => 'nullable|array',
            'type' => 'sometimes|in:standard,rotating,flexible,night',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $shift->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Shift updated successfully.',
                'data' => $shift,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shift.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a shift.
     */
    public function destroy(int $id): JsonResponse
    {
        $shift = Shift::where('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())->find($id);

        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'Shift not found.',
            ], 404);
        }

        // Check if shift is assigned to any employee
        if ($shift->employeeShifts()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete shift with active assignments.',
            ], 422);
        }

        try {
            $shift->delete();

            return response()->json([
                'success' => true,
                'message' => 'Shift deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete shift.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle shift active status.
     */
    public function toggleStatus(int $id): JsonResponse
    {
        $shift = Shift::where('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())->find($id);

        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'Shift not found.',
            ], 404);
        }

        $shift->update(['is_active' => !$shift->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Shift status updated.',
            'data' => $shift,
        ]);
    }
}
