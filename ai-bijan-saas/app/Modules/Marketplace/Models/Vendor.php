<?php

namespace App\Modules\Marketplace\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use BelongsToTenant;

    protected $table = 'marketplace_vendors';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'slug',
        'description',
        'logo_url',
        'banner_url',
        'contact_email',
        'contact_phone',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'is_active',
        'is_verified',
        'commission_rate',
        'rating',
        'total_sales',
        'total_products',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'commission_rate' => 'decimal:4',
        'rating' => 'decimal:2',
        'total_sales' => 'integer',
        'total_products' => 'integer',
    ];

    /**
     * Get the tenant that owns the vendor.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the user associated with this vendor.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Get all products from this vendor.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    }

    /**
     * Scope active vendors.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope verified vendors.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Get commission rate based on sales tier.
     */
    public function getEffectiveCommissionRate(): float
    {
        $config = config('marketplace.commission.vendor_tiers', []);
        
        foreach ($config as $tier) {
            if ($this->total_sales >= ($tier['min_sales'] ?? 0)) {
                return $tier['rate'];
            }
        }

        return config('marketplace.commission.default_rate', 0.05);
    }
}
