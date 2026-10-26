<?php

namespace App\Modules\Marketplace\Services;

use App\Modules\Marketplace\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VendorService
{
    /**
     * Get all vendors with pagination.
     */
    public function list(int $tenantId, array $filters = [], int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Vendor::where('tenant_id', $tenantId)->with(['user']);

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_verified'])) {
            $query->where('is_verified', $filters['is_verified']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get a single vendor by ID.
     */
    public function find(int $vendorId): ?Vendor
    {
        return Vendor::with(['products'])->find($vendorId);
    }

    /**
     * Create a new vendor.
     */
    public function create(array $data): Vendor
    {
        return DB::transaction(function () use ($data) {
            // Generate slug from name
            if (empty($data['slug'])) {
                $data['slug'] = Vendor::generateSlug($data['name']);
            }

            // Set default commission rate
            if (empty($data['commission_rate'])) {
                $data['commission_rate'] = config('marketplace.commission.default_rate', 0.05);
            }

            $vendor = Vendor::create($data);

            // Initialize counters
            $vendor->update([
                'total_sales' => 0,
                'total_products' => 0,
                'rating' => 0,
            ]);

            return $vendor;
        });
    }

    /**
     * Update an existing vendor.
     */
    public function update(Vendor $vendor, array $data): Vendor
    {
        $vendor->update($data);
        return $vendor->fresh();
    }

    /**
     * Delete a vendor.
     */
    public function delete(Vendor $vendor): bool
    {
        return DB::transaction(function () use ($vendor) {
            // Delete associated products first
            $vendor->products()->delete();
            
            return $vendor->delete();
        });
    }

    /**
     * Approve a vendor application.
     */
    public function approve(Vendor $vendor): Vendor
    {
        return $vendor->update([
            'is_verified' => true,
            'is_active' => true,
        ]);
    }

    /**
     * Reject a vendor application.
     */
    public function reject(Vendor $vendor): Vendor
    {
        return $vendor->update([
            'is_verified' => false,
            'is_active' => false,
        ]);
    }

    /**
     * Toggle vendor active status.
     */
    public function toggleActive(Vendor $vendor, bool $isActive): Vendor
    {
        return $vendor->update(['is_active' => $isActive]);
    }

    /**
     * Update vendor rating based on reviews.
     */
    public function updateRating(Vendor $vendor): Vendor
    {
        $avgRating = $vendor->reviews()->avg('rating') ?? 0;
        
        return $vendor->update([
            'rating' => round($avgRating, 2),
        ]);
    }

    /**
     * Update total products count.
     */
    public function updateProductCount(Vendor $vendor): int
    {
        $count = $vendor->products()->count();
        $vendor->update(['total_products' => $count]);
        return $count;
    }

    /**
     * Update total sales count.
     */
    public function updateSalesCount(Vendor $vendor): int
    {
        $count = $vendor->orders()->count();
        $vendor->update(['total_sales' => $count]);
        return $count;
    }

    /**
     * Get top vendors by sales.
     */
    public function getTopVendors(int $tenantId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Vendor::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('is_verified', true)
            ->with(['products'])
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if vendor exists with slug.
     */
    public function existsBySlug(string $slug, int $tenantId, ?int $excludeId = null): bool
    {
        $query = Vendor::where('tenant_id', $tenantId)->where('slug', $slug);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Generate unique slug.
     */
    public function generateUniqueSlug(string $name, int $tenantId): string
    {
        $baseSlug = Vendor::generateSlug($name);
        $slug = $baseSlug;
        $counter = 1;

        while ($this->existsBySlug($slug, $tenantId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
