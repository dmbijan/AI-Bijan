<?php

namespace App\Modules\Marketing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Segment extends Model
{
    use BelongsToTenant;

    protected $table = 'marketing_segments';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'type',
        'criteria',
        'count',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'criteria' => 'array',
        'count' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns the segment.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get all contacts in this segment.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'segment_id');
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    }

    /**
     * Scope active segments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Update segment count.
     */
    public function updateCount(int $count): void
    {
        $this->update(['count' => $count]);
    }
}
