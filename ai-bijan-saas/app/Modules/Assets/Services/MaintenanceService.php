<?php

namespace App\Modules\Assets\Services;

use App\Modules\Assets\Models\MaintenanceRequest;
use App\Modules\Assets\Models\MaintenancePart;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MaintenanceService
{
    /**
     * List maintenance requests for a tenant.
     */
    public function listRequests(int $tenantId, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = MaintenanceRequest::forTenant($tenantId)
            ->with(['asset', 'requester', 'assignee', 'vendor']);

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['type'])) {
            $query->byType($filters['type']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['asset_id'])) {
            $query->where('asset_id', $filters['asset_id']);
        }

        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Get a single maintenance request.
     */
    public function getRequest(int $requestId, int $tenantId): ?MaintenanceRequest
    {
        return MaintenanceRequest::forTenant($tenantId)
            ->with(['asset', 'requester', 'assignee', 'vendor', 'parts'])
            ->find($requestId);
    }

    /**
     * Create a new maintenance request.
     */
    public function createRequest(array $data, int $tenantId, int $userId): MaintenanceRequest
    {
        return DB::transaction(function () use ($data, $tenantId, $userId) {
            $data['tenant_id'] = $tenantId;
            $data['requested_by'] = $userId;
            $data['status'] = 'scheduled';

            $request = MaintenanceRequest::create($data);

            // Update asset status if emergency or critical
            if (in_array($data['type'], ['emergency', 'corrective']) && in_array($data['priority'], ['critical', 'high'])) {
                $asset = \App\Modules\Assets\Models\Asset::find($data['asset_id']);
                if ($asset && $asset->status === 'active') {
                    $asset->update(['status' => 'maintenance']);
                }
            }

            return $request;
        });
    }

    /**
     * Update a maintenance request.
     */
    public function updateRequest(int $requestId, array $data, int $tenantId): ?MaintenanceRequest
    {
        $request = $this->getRequest($requestId, $tenantId);

        if (!$request) {
            return null;
        }

        $request->update($data);

        // If completed, update asset status back to active
        if (isset($data['status']) && $data['status'] === 'completed') {
            $asset = \App\Modules\Assets\Models\Asset::find($request->asset_id);
            if ($asset && $asset->status === 'maintenance') {
                $asset->update(['status' => 'active']);
            }
        }

        return $request;
    }

    /**
     * Delete a maintenance request.
     */
    public function deleteRequest(int $requestId, int $tenantId): bool
    {
        $request = $this->getRequest($requestId, $tenantId);

        if (!$request) {
            return false;
        }

        $request->parts()->delete();

        return $request->delete();
    }

    /**
     * Add parts/materials to a maintenance request.
     */
    public function addPart(int $requestId, array $data, int $tenantId): MaintenancePart
    {
        $request = $this->getRequest($requestId, $tenantId);

        if (!$request) {
            throw ValidationException::withMessages([
                'request_id' => ['Maintenance request not found.'],
            ]);
        }

        $totalCost = ($data['quantity'] ?? 1) * ($data['unit_cost'] ?? 0);

        $part = MaintenancePart::create([
            'maintenance_request_id' => $requestId,
            'part_name' => $data['part_name'],
            'part_number' => $data['part_number'] ?? null,
            'quantity' => $data['quantity'] ?? 1,
            'unit_cost' => $data['unit_cost'] ?? 0,
            'total_cost' => $totalCost,
        ]);

        return $part;
    }

    /**
     * Remove a part from a maintenance request.
     */
    public function removePart(int $partId, int $tenantId): bool
    {
        return MaintenancePart::destroy($partId);
    }

    /**
     * Complete a maintenance request.
     */
    public function completeRequest(int $requestId, array $completionData, int $tenantId): ?MaintenanceRequest
    {
        $request = $this->getRequest($requestId, $tenantId);

        if (!$request) {
            return null;
        }

        return DB::transaction(function () use ($request, $completionData, $tenantId) {
            $request->update([
                'status' => 'completed',
                'completed_date' => now(),
                'resolution' => $completionData['resolution'] ?? null,
                'actual_cost' => $completionData['actual_cost'] ?? null,
                'downtime_hours' => $completionData['downtime_hours'] ?? null,
            ]);

            // Update asset status back to active
            $asset = \App\Modules\Assets\Models\Asset::find($request->asset_id);
            if ($asset && $asset->status === 'maintenance') {
                $asset->update(['status' => 'active']);
            }

            return $request;
        });
    }

    /**
     * Cancel a maintenance request.
     */
    public function cancelRequest(int $requestId, int $tenantId): ?MaintenanceRequest
    {
        $request = $this->getRequest($requestId, $tenantId);

        if (!$request) {
            return null;
        }

        $request->update(['status' => 'cancelled']);

        return $request;
    }

    /**
     * Assign a technician to a maintenance request.
     */
    public function assignTechnician(int $requestId, int $technicianId, int $tenantId): ?MaintenanceRequest
    {
        $request = $this->getRequest($requestId, $tenantId);

        if (!$request) {
            return null;
        }

        $request->update(['assigned_to' => $technicianId]);

        return $request;
    }

    /**
     * Get maintenance summary statistics.
     */
    public function getSummary(int $tenantId): array
    {
        $baseQuery = MaintenanceRequest::forTenant($tenantId);

        return [
            'total_requests' => $baseQuery->count(),
            'scheduled' => $baseQuery->cloneQuery()->where('status', 'scheduled')->count(),
            'in_progress' => $baseQuery->cloneQuery()->where('status', 'in_progress')->count(),
            'completed' => $baseQuery->cloneQuery()->where('status', 'completed')->count(),
            'cancelled' => $baseQuery->cloneQuery()->where('status', 'cancelled')->count(),
            'overdue' => $baseQuery->cloneQuery()
                ->whereNotNull('scheduled_date')
                ->where('scheduled_date', '<', now())
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->count(),
            'total_cost' => $baseQuery->sum('actual_cost') ?: 0,
            'by_type' => $baseQuery->selectRaw('type, count(*) as count')->groupBy('type')->get()->pluck('count', 'type'),
            'by_priority' => $baseQuery->selectRaw('priority, count(*) as count')->groupBy('priority')->get()->pluck('count', 'priority'),
        ];
    }

    /**
     * Get upcoming preventive maintenance schedule.
     */
    public function getUpcomingPreventive(int $tenantId, int $daysAhead = 30): array
    {
        return MaintenanceRequest::forTenant($tenantId)
            ->where('type', 'preventive')
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->where(function ($q) use ($daysAhead) {
                $q->whereNull('scheduled_date')
                  ->orWhere('scheduled_date', '>=', now())
                  ->orWhere('scheduled_date', '<=', now()->addDays($daysAhead));
            })
            ->with(['asset'])
            ->orderBy('scheduled_date', 'asc')
            ->limit(50)
            ->get();
    }

    /**
     * Get maintenance history for an asset.
     */
    public function getAssetHistory(int $assetId, int $tenantId): array
    {
        $requests = MaintenanceRequest::forTenant($tenantId)
            ->where('asset_id', $assetId)
            ->with(['parts', 'requester', 'assignee'])
            ->orderBy('completed_date', 'desc')
            ->get();

        return [
            'total_maintenance' => $requests->count(),
            'total_cost' => $requests->sum(fn($r) => $r->total_cost),
            'total_downtime' => $requests->sum('downtime_hours'),
            'requests' => $requests,
        ];
    }
}
