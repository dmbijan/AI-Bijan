<?php

namespace App\Modules\Booking\Services;

use App\Modules\Booking\Models\Service;
use Illuminate\Validation\ValidationException;

class ServiceCatalogService
{
    /**
     * List all services for a tenant.
     */
    public function listServices(int $tenantId, array $filters = []): array
    {
        $query = Service::where('tenant_id', $tenantId);

        if (isset($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (isset($filters['active']) && $filters['active']) {
            $query->active();
        }

        if (isset($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        $services = $query->orderBy('name')->get();

        return $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'slug' => $service->slug,
                'category' => $service->category,
                'duration_minutes' => $service->duration_minutes,
                'price' => $service->price,
                'is_active' => $service->is_active,
                'provider' => $service->provider ? [
                    'id' => $service->provider->id,
                    'name' => $service->provider->name,
                ] : null,
            ];
        })->toArray();
    }

    /**
     * Get a single service.
     */
    public function getService(int $serviceId, int $tenantId): ?array
    {
        $service = Service::where('id', $serviceId)
            ->where('tenant_id', $tenantId)
            ->with(['provider'])
            ->first();

        if (!$service) {
            return null;
        }

        return [
            'id' => $service->id,
            'name' => $service->name,
            'slug' => $service->slug,
            'category' => $service->category,
            'description' => $service->description,
            'duration_minutes' => $service->duration_minutes,
            'price' => $service->price,
            'cost' => $service->cost,
            'is_active' => $service->is_active,
            'image_url' => $service->image_url,
            'max_capacity' => $service->max_capacity,
            'requires_prep' => $service->requires_prep,
            'prep_time_minutes' => $service->prep_time_minutes,
            'cancellation_policy' => $service->cancellation_policy,
            'provider' => $service->provider ? [
                'id' => $service->provider->id,
                'name' => $service->provider->name,
                'specialization' => $service->provider->specialization,
            ] : null,
        ];
    }

    /**
     * Create a new service.
     */
    public function createService(array $data, int $tenantId, ?int $userId = null): Service
    {
        // Validate slug uniqueness
        $slug = Service::generateSlug($data['name']);
        
        if (Service::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            throw ValidationException::withMessages([
                'name' => 'A service with this name already exists.',
            ]);
        }

        $data['slug'] = $slug;
        $data['tenant_id'] = $tenantId;

        return Service::create($data);
    }

    /**
     * Update a service.
     */
    public function updateService(int $serviceId, array $data, int $tenantId): ?Service
    {
        $service = Service::where('id', $serviceId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$service) {
            return null;
        }

        $service->update($data);

        return $service;
    }

    /**
     * Delete a service.
     */
    public function deleteService(int $serviceId, int $tenantId): bool
    {
        $service = Service::where('id', $serviceId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$service) {
            return false;
        }

        // Check if service has appointments
        if ($service->appointments()->count() > 0) {
            throw ValidationException::withMessages([
                'service' => 'Cannot delete service with existing appointments.',
            ]);
        }

        return $service->delete();
    }

    /**
     * Toggle service active status.
     */
    public function toggleActive(int $serviceId, int $tenantId): ?Service
    {
        $service = Service::where('id', $serviceId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$service) {
            return null;
        }

        $service->is_active = !$service->is_active;
        $service->save();

        return $service;
    }
}
