<?php

namespace App\Modules\HRMS\Services;

use App\Modules\HRMS\Models\LeaveRequest;
use App\Modules\HRMS\Models\LeaveAllocation;
use App\Modules\HRMS\Models\LeaveType;
use App\Modules\HRMS\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LeaveService
{
    /**
     * Get all leave types for a tenant.
     */
    public function getLeaveTypes(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return LeaveType::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new leave type.
     */
    public function createLeaveType(array $data, int $tenantId): LeaveType
    {
        $data['tenant_id'] = $tenantId;
        return LeaveType::create($data);
    }

    /**
     * Submit a leave request.
     */
    public function submitRequest(array $data, int $tenantId): LeaveRequest
    {
        return DB::transaction(function () use ($data, $tenantId) {
            $employee = Employee::where('tenant_id', $tenantId)
                ->where('id', $data['employee_id'])
                ->firstOrFail();

            // Check leave balance
            $allocation = LeaveAllocation::where('employee_id', $employee->id)
                ->where('leave_type_id', $data['leave_type_id'])
                ->where('year', now()->year)
                ->first();

            if (!$allocation || $allocation->remaining_days < $data['days_requested']) {
                throw new \Exception('Insufficient leave balance.');
            }

            $request = LeaveRequest::create([
                'employee_id' => $employee->id,
                'leave_type_id' => $data['leave_type_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'days_requested' => $data['days_requested'],
                'half_day' => $data['half_day'] ?? 'full',
                'reason' => $data['reason'] ?? null,
                'status' => 'pending',
            ]);

            // Record history
            $request->history()->create([
                'user_id' => auth()->id(),
                'action' => 'submitted',
                'notes' => 'Leave request submitted',
            ]);

            return $request->fresh(['employee', 'leaveType']);
        });
    }

    /**
     * Approve a leave request.
     */
    public function approveRequest(LeaveRequest $request): LeaveRequest
    {
        return DB::transaction(function () use ($request) {
            $request->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Deduct from allocation
            $allocation = LeaveAllocation::where('employee_id', $request->employee_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', now()->year)
                ->first();

            if ($allocation) {
                $allocation->increment('used_days', $request->days_requested);
            }

            // Record history
            $request->history()->create([
                'user_id' => auth()->id(),
                'action' => 'approved',
                'notes' => 'Leave request approved',
            ]);

            return $request->fresh(['employee', 'approver']);
        });
    }

    /**
     * Reject a leave request.
     */
    public function rejectRequest(LeaveRequest $request, string $reason): LeaveRequest
    {
        return DB::transaction(function () use ($request, $reason) {
            $request->update([
                'status' => 'rejected',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'rejection_reason' => $reason,
            ]);

            // Record history
            $request->history()->create([
                'user_id' => auth()->id(),
                'action' => 'rejected',
                'notes' => 'Leave request rejected: ' . $reason,
            ]);

            return $request;
        });
    }

    /**
     * Cancel a leave request.
     */
    public function cancelRequest(LeaveRequest $request): LeaveRequest
    {
        return DB::transaction(function () use ($request) {
            if ($request->status !== 'pending' && $request->status !== 'draft') {
                throw new \Exception('Only pending or draft requests can be cancelled.');
            }

            $request->update(['status' => 'cancelled']);

            // Record history
            $request->history()->create([
                'user_id' => auth()->id(),
                'action' => 'cancelled',
                'notes' => 'Leave request cancelled by employee',
            ]);

            return $request;
        });
    }

    /**
     * Get employee leave balance.
     */
    public function getLeaveBalance(int $employeeId, int $year = null): array
    {
        $year = $year ?? now()->year;

        $allocations = LeaveAllocation::where('employee_id', $employeeId)
            ->where('year', $year)
            ->with('leaveType')
            ->get();

        $balances = [];
        foreach ($allocations as $allocation) {
            $balances[] = [
                'leave_type_id' => $allocation->leave_type_id,
                'leave_type_code' => $allocation->leaveType->code,
                'leave_type_name' => $allocation->leaveType->name,
                'total_days' => $allocation->total_days,
                'used_days' => $allocation->used_days,
                'remaining_days' => $allocation->remaining_days,
                'carried_over' => $allocation->carried_over_days,
                'expiry_date' => $allocation->expiry_date,
            ];
        }

        return $balances;
    }

    /**
     * Allocate leave days to employee.
     */
    public function allocateLeave(int $employeeId, int $leaveTypeId, int $days, ?string $expiryDate = null, int $carriedOver = 0): LeaveAllocation
    {
        return DB::transaction(function () use ($employeeId, $leaveTypeId, $days, $expiryDate, $carriedOver) {
            return LeaveAllocation::updateOrCreate(
                [
                    'employee_id' => $employeeId,
                    'leave_type_id' => $leaveTypeId,
                    'year' => now()->year,
                ],
                [
                    'total_days' => $days,
                    'carried_over_days' => $carriedOver,
                    'expiry_date' => $expiryDate,
                ]
            );
        });
    }

    /**
     * Get pending leave requests for approval.
     */
    public function getPendingRequests(int $tenantId, ?int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return LeaveRequest::whereHas('employee', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })
        ->where('status', 'pending')
        ->with(['employee.department', 'employee.user'])
        ->orderBy('created_at', 'desc')
        ->limit($limit)
        ->get();
    }

    /**
     * Get employee leave history.
     */
    public function getLeaveHistory(int $employeeId, ?int $limit = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        return LeaveRequest::where('employee_id', $employeeId)
            ->with(['leaveType', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    /**
     * Auto-approve requests below threshold (configurable).
     */
    public function autoApproveBelowThreshold(int $tenantId, int $thresholdDays = 2): int
    {
        $count = 0;

        $requests = LeaveRequest::whereHas('employee', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })
        ->where('status', 'pending')
        ->where('days_requested', '<=', $thresholdDays)
        ->with('employee')
        ->get();

        foreach ($requests as $request) {
            $this->approveRequest($request);
            $count++;
        }

        return $count;
    }
}
