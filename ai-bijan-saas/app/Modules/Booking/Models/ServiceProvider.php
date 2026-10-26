<?php

namespace App\Modules\Booking\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceProvider extends Model
{
    use BelongsToTenant;

    protected $table = 'booking_service_providers';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'name',
        'slug',
        'specialization',
        'bio',
        'phone',
        'email',
        'avatar_url',
        'is_active',
        'max_concurrent_appointments',
        'break_time_minutes',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_concurrent_appointments' => 'integer',
        'break_time_minutes' => 'integer',
    ];

    /**
     * Get the tenant that owns the service provider.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the user associated with this provider.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Get all services provided by this provider.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'provider_id');
    }

    /**
     * Get all appointments for this provider.
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'provider_id');
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    }

    /**
     * Scope active providers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
