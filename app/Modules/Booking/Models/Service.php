<?php

namespace App\Modules\Booking\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use BelongsToTenant;

    protected $table = 'booking_services';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'category',
        'description',
        'duration_minutes',
        'price',
        'cost',
        'provider_id',
        'is_active',
        'image_url',
        'max_capacity',
        'requires_prep',
        'prep_time_minutes',
        'cancellation_policy',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
        'max_capacity' => 'integer',
        'requires_prep' => 'boolean',
        'prep_time_minutes' => 'integer',
    ];

    /**
     * Get the tenant that owns the service.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the provider assigned to this service.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class, 'provider_id');
    }

    /**
     * Get all appointments for this service.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
