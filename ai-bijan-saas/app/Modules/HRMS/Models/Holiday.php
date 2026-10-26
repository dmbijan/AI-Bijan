<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Holiday extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'hrms_holidays';

    protected $fillable = [
        'tenant_id',
        'name',
        'date',
        'type',
        'state_applicable',
        'is_recurring',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the tenant that owns the holiday.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Scope by year.
     */
    public function scopeByYear($query, int $year)
    {
        return $query->whereYear('date', $year);
    }

    /**
     * Scope by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if holiday is today.
     */
    public function isToday(): bool
    {
        return $this->date->isToday();
    }

    /**
     * Check if holiday is in the future.
     */
    public function isUpcoming(): bool
    {
        return $this->date->isFuture();
    }
}
