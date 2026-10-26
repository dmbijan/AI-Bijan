<?php

namespace App\Modules\Site\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends Model
{
    use BelongsToTenant;

    protected $table = 'site_domains';

    protected $fillable = [
        'tenant_id',
        'site_id',
        'domain',
        'type',
        'is_primary',
        'ssl_enabled',
        'ssl_status',
        'redirect_to',
        'verified',
        'verification_code',
        'dns_records',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'ssl_enabled' => 'boolean',
        'verified' => 'boolean',
        'dns_records' => 'array',
    ];

    /**
     * Get the tenant that owns the domain.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the site this domain belongs to.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * Scope primary domains.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope verified domains.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Check if SSL is properly configured.
     */
    public function hasValidSsl(): bool
    {
        return $this->ssl_enabled && $this->ssl_status === 'active';
    }

    /**
     * Validate domain format.
     */
    public static function validateDomain(string $domain): bool
    {
        return preg_match('/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/', $domain) === 1;
    }
}
