<?php

namespace App\Modules\Marketplace\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use BelongsToTenant;

    protected $table = 'marketplace_products';

    protected $fillable = [
        'tenant_id',
        'vendor_id',
        'sku',
        'name',
        'slug',
        'description',
        'short_description',
        'category',
        'subcategory',
        'price',
        'compare_at_price',
        'cost',
        'stock_quantity',
        'low_stock_threshold',
        'is_active',
        'is_featured',
        'is_digital',
        'weight',
        'length',
        'width',
        'height',
        'images',
        'specifications',
        'tags',
        'seo_title',
        'seo_description',
        'views_count',
        'sales_count',
        'rating',
        'review_count',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_digital' => 'boolean',
        'weight' => 'decimal:2',
        'images' => 'array',
        'specifications' => 'array',
        'tags' => 'array',
        'views_count' => 'integer',
        'sales_count' => 'integer',
        'rating' => 'decimal:2',
        'review_count' => 'integer',
    ];

    /**
     * Get the tenant that owns the product.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the vendor who sells this product.
     */
    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    /**
     * Get all reviews for this product.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    }

    /**
     * Scope active products.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by vendor.
     */
    public function scopeByVendor($query, int $vendorId)
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Check if product is in stock.
     */
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    /**
     * Check if product is low on stock.
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= ($this->low_stock_threshold ?? 5);
    }

    /**
     * Get discount percentage.
     */
    public function getDiscountPercentage(): float
    {
        if ($this->compare_at_price && $this->compare_at_price > $this->price) {
            return round((($this->compare_at_price - $this->price) / $this->compare_at_price) * 100, 2);
        }
        return 0;
    }
}
