<?php

namespace App\Modules\Marketplace\Services;

use App\Modules\Marketplace\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Get all products with pagination.
     */
    public function list(int $tenantId, array $filters = [], int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Product::where('tenant_id', $tenantId)->with(['vendor']);

        // Apply filters
        if (!empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (!empty($filters['vendor_id'])) {
            $query->byVendor($filters['vendor_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get a single product by ID.
     */
    public function find(int $productId): ?Product
    {
        return Product::with(['vendor'])->find($productId);
    }

    /**
     * Create a new product.
     */
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            // Generate SKU if not provided
            if (empty($data['sku'])) {
                $data['sku'] = 'MKT-PROD-' . strtoupper(Str::random(8));
            }

            // Generate slug from name
            if (empty($data['slug'])) {
                $data['slug'] = Product::generateSlug($data['name']);
            }

            $product = Product::create($data);

            // Initialize counts
            $product->update([
                'views_count' => 0,
                'sales_count' => 0,
                'rating' => 0,
                'review_count' => 0,
            ]);

            return $product;
        });
    }

    /**
     * Update an existing product.
     */
    public function update(Product $product, array $data): Product
    {
        // Generate slug if name changed and slug not explicitly set
        if (isset($data['name']) && empty($data['slug'])) {
            $data['slug'] = Product::generateSlug($data['name']);
        }

        $product->update($data);
        return $product->fresh();
    }

    /**
     * Delete a product.
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Soft delete inactive products.
     */
    public function purgeInactive(int $tenantId, int $daysInactive = 90): int
    {
        $cutoffDate = now()->subDays($daysInactive);
        
        return Product::where('tenant_id', $tenantId)
            ->where('is_active', false)
            ->where('updated_at', '<', $cutoffDate)
            ->delete();
    }

    /**
     * Update stock quantity.
     */
    public function updateStock(Product $product, int $quantityChange, string $operation = 'set'): Product
    {
        return DB::transaction(function () use ($product, $quantityChange, $operation) {
            switch ($operation) {
                case 'set':
                    $product->stock_quantity = $quantityChange;
                    break;
                case 'add':
                    $product->stock_quantity += $quantityChange;
                    break;
                case 'remove':
                    $product->stock_quantity -= $quantityChange;
                    break;
                default:
                    throw new \InvalidArgumentException("Invalid operation: {$operation}");
            }

            $product->save();
            return $product->fresh();
        });
    }

    /**
     * Record a product view.
     */
    public function recordView(Product $product): void
    {
        $product->increment('views_count');
    }

    /**
     * Record a sale for the product.
     */
    public function recordSale(Product $product, int $quantity = 1): void
    {
        $product->increment('sales_count', $quantity);
    }

    /**
     * Get featured products.
     */
    public function getFeatured(int $tenantId, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('is_featured', true)
            ->with(['vendor'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Search products across multiple tenants (marketplace-wide search).
     */
    public function search(string $query, array $filters = [], int $limit = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        $products = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%");
            });

        if (!empty($filters['category'])) {
            $products->byCategory($filters['category']);
        }

        if (!empty($filters['min_rating'])) {
            $products->where('rating', '>=', $filters['min_rating']);
        }

        return $products->with(['vendor'])
            ->orderByDesc('rating')
            ->orderByDesc('sales_count')
            ->paginate($limit);
    }

    /**
     * Bulk update product status.
     */
    public function bulkUpdateStatus(array $productIds, bool $isActive): int
    {
        return Product::whereIn('id', $productIds)->update(['is_active' => $isActive]);
    }

    /**
     * Get low stock products.
     */
    public function getLowStockProducts(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Product::where('tenant_id', $tenantId)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('is_active', true)
            ->with(['vendor'])
            ->orderBy('stock_quantity', 'asc')
            ->get();
    }
}
