<?php

namespace App\Modules\HRMS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\HRMS\Services\LeaveService;
use App\Modules\HRMS\Models\LeaveRequest;
use App\Modules\HRMS\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LeaveController extends Controller
{
    protected $leaveService;

    public function __construct(LeaveService $leaveService)
    {
        $this->leaveService = $leaveService;
    }

    /**
     * List all leave types.
     */
    public function leaveTypes(): JsonResponse
    {
        $leaveTypes = $this->leaveService->getLeaveTypes(Auth::tenant()->id);

        return response()->json([
            'success' => true,
            'data' => $leaveTypes,
        ]);
    }

    /**
     * Create a new leave type.
     */
    public function createLeaveType(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|unique:hrms_leave_types,code',
            'name' => 'required|string|max:255',
            'max_days_per_year' => 'nullable|integer|min:0',
            'encashable' => 'boolean',
            'requires_document' => 'boolean',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $leaveType = $this->leaveService->createLeaveType($request->all(), Auth::tenant()->id);

            return response()->json([
                'success' => true,
                'message' => 'Leave type created successfully.',
                'data' => $leaveType,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create leave type.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit a leave request.
     */
    public function submitRequest(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'leave_type_id' => 'required|exists:hrms_leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'days_requested' => 'required|integer|min:1',
            'half_day' => 'nullable|in:full,morning,afternoon',
            'reason' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $request = $this->leaveService->submitRequest($request->all(), Auth::tenant()->id);

            return response()->json([
                'success' => true,
                'message' => 'Leave request submitted successfully.',
                'data' => $request,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Approve a leave request.
     */
    public function approveRequest(int $id): JsonResponse
    {
        $leaveRequest = LeaveRequest::where('id', $id)
            ->whereHas('employee', function ($query) {
                $query->where('tenant_id', Auth::tenant()->id);
            })
            ->first();

        if (!$leaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found.',
            ], 404);
        }

        try {
            $leaveRequest = $this->leaveService->approveRequest($leaveRequest);

            return response()->json([
                'success' => true,
                'message' => 'Leave request approved successfully.',
                'data' => $leaveRequest,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve leave request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject a leave request.
     */
    public function rejectRequest(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $leaveRequest = LeaveRequest::where('id', $id)
            ->whereHas('employee', function ($query) {
                $query->where('tenant_id', Auth::tenant()->id);
            })
            ->first();

        if (!$leaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found.',
            ], 404);
        }

        try {
            $leaveRequest = $this->leaveService->rejectRequest($leaveRequest, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Leave request rejected.',
                'data' => $leaveRequest,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject leave request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel a leave request.
     */
    public function cancelRequest(int $id): JsonResponse
    {
        $leaveRequest = LeaveRequest::where('id', $id)
            ->whereHas('employee', function ($query) {
                $query->where('tenant_id', Auth::tenant()->id);
            })
            ->first();

        if (!$leaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found.',
            ], 404);
        }

        try {
            $leaveRequest = $this->leaveService->cancelRequest($leaveRequest);

            return response()->json([
                'success' => true,
                'message' => 'Leave request cancelled.',
                'data' => $leaveRequest,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get employee leave balance.
     */
    public function getBalance(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'year' => 'nullable|integer|min:2020|max:' . (now()->year + 1),
        ]);

        $balances = $this->leaveService->getLeaveBalance(
            $request->employee_id,
            $request->year
        );

        return response()->json([
            'success' => true,
            'data' => $balances,
        ]);
    }

    /**
     * Allocate leave days to employee.
     */
    public function allocateLeave(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'leave_type_id' => 'required|exists:hrms_leave_types,id',
            'days' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date',
            'carried_over' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $allocation = $this->leaveService->allocateLeave(
                $request->employee_id,
                $request->leave_type_id,
                $request->days,
                $request->expiry_date,
                $request->carried_over
            );

            return response()->json([
                'success' => true,
                'message' => 'Leave allocated successfully.',
                'data' => $allocation,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to allocate leave.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending requests for approval.
     */
    public function getPending(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $requests = $this->leaveService->getPendingRequests(Auth::tenant()->id, $limit);

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    /**
     * Get employee leave history.
     */
    public function getHistory(Request $request): JsonResponse
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $history = $this->leaveService->getLeaveHistory(
            $request->employee_id,
            $request->limit ?? 20
        );

        return response()->json([
            'success' => true,
            'data' => $history,
        ]);
    }

    /**
     * Auto-approve requests below threshold.
     */
    public function autoApprove(Request $request): JsonResponse
    {
        $request->validate([
            'threshold_days' => 'nullable|integer|min:1|max:10',
        ]);

        $count = $this->leaveService->autoApproveBelowThreshold(
            Auth::tenant()->id,
            $request->threshold_days ?? 2
        );

        return response()->json([
            'success' => true,
            'message' => "Auto-approved {$count} requests.",
            'data' => ['count' => $count],
        ]);
    }
}
