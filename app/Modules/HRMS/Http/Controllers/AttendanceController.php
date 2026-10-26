<?php

namespace App\Modules\HRMS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HRMS\Services\AttendanceService;
use App\Modules\HRMS\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    protected $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    /**
     * Check in for current employee.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'method' => 'nullable|in:manual,biometric,gps,web',
            'location' => 'nullable|string|max:500',
            'photo' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $attendance = $this->attendanceService->checkIn(
                Auth::user()->employee->id ?? auth()->id(),
                $request->all()
            );

            return response()->json([
                'success' => true,
                'message' => 'Checked in successfully.',
                'data' => $attendance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Check out for current employee.
     */
    public function checkOut(): JsonResponse
    {
        try {
            $attendance = $this->attendanceService->checkOut(
                Auth::user()->employee->id ?? auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Checked out successfully.',
                'data' => $attendance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get attendance records with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'department_id' => 'nullable|integer',
            'status' => 'nullable|in:present,absent,late,half_day,on_leave,holiday',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $records = $this->attendanceService->getAttendanceRecords(
            Auth::tenant()->id,
            $request->start_date,
            $request->end_date,
            $request->department_id
        );

        return response()->json([
            'success' => true,
            'data' => $records,
        ]);
    }

    /**
     * Get daily attendance summary.
     */
    public function dailySummary(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $summary = $this->attendanceService->getDailySummary($request->date);

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }

    /**
     * Mark attendance manually (admin).
     */
    public function markAttendance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,half_day,on_leave,holiday',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'total_hours' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $attendance = $this->attendanceService->markAttendance(
                $request->employee_id,
                $request->date,
                $request->status,
                $request->only(['check_in_time', 'check_out_time', 'total_hours', 'notes'])
            );

            return response()->json([
                'success' => true,
                'message' => 'Attendance marked successfully.',
                'data' => $attendance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get monthly attendance report.
     */
    public function monthlyReport(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
        ]);

        $report = $this->attendanceService->getMonthlyReport(
            $request->employee_id,
            $request->month,
            $request->year
        );

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Bulk mark attendance (admin).
     */
    public function bulkMark(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'records' => 'required|array',
            'records.*.employee_id' => 'required|integer',
            'records.*.status' => 'required|in:present,absent,late,half_day,on_leave,holiday',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->attendanceService->bulkMarkAttendance(
                $request->records,
                $request->date
            );

            return response()->json([
                'success' => true,
                'message' => 'Bulk attendance marked.',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk attendance failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get today's attendance for current employee.
     */
    public function getToday(): JsonResponse
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('employee_id', Auth::user()->employee->id ?? auth()->id())
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => true,
                'data' => null,
                'message' => 'No attendance record for today.',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $attendance,
        ]);
    }
}
