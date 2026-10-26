<?php

namespace App\Modules\Marketing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sequence extends Model
{
    use BelongsToTenant;

    protected $table = 'marketing_sequences';

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'name',
        'slug',
        'description',
        'trigger',
        'trigger_conditions',
        'steps',
        'is_active',
        'stop_on_response',
        'max_repetitions',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'steps' => 'array',
        'is_active' => 'boolean',
        'stop_on_response' => 'boolean',
        'max_repetitions' => 'integer',
    ];

    /**
     * Get the tenant that owns the sequence.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the campaign this sequence belongs to.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    /**
     * Get all sequence executions.
     */
    public function executions(): HasMany
    {
        return $this->hasMany(SequenceExecution::class, 'sequence_id');
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    }

    /**
     * Scope active sequences.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by trigger type.
     */
    public function scopeByTrigger($query, string $trigger)
    {
        return $query->where('trigger', $trigger);
    }
}
