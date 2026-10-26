<?php

namespace App\Modules\Assets\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'asset_categories';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'default_purchase_price',
        'default_useful_life_years',
        'depreciation_method',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_purchase_price' => 'decimal:2',
        'default_useful_life_years' => 'integer',
    ];

    /**
     * Get the assets for this category.
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    /**
     * Generate category code.
     */
    public static function generateCode(string $name): string
    {
        $slug = strtolower(str_replace(' ', '_', preg_replace('/[^A-Za-z0-9_]/', '', $name)));
        $year = now()->format('Y');
        
        // Find last code with same prefix
        $lastCode = static::where('code', 'like', "CAT-{$year}-%")
            ->orderBy('code', 'desc')
            ->value('code');
        
        $number = $lastCode ? intval(substr($lastCode, -4)) + 1 : 1;
        
        return 'CAT-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scope by tenant.
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope active categories only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
