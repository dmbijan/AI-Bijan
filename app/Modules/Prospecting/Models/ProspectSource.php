<?php

namespace App\Modules\Prospecting\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Sumber prospek — boleh jadi import CSV, connector API berlesen,
 * atau scraping web publik (robots.txt aware).
 */
class ProspectSource extends Model
{
    use BelongsToTenant;

    protected $table = 'prospect_sources';

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'connector_slug',
        'config',
        'status',
        'last_sync_at',
        'last_error',
    ];

    protected $casts = [
        'config' => 'array',
        'last_sync_at' => 'datetime',
    ];

    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class, 'source_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
