<?php

namespace App\Modules\AgentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentKnowledgeBinding extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'agent_blueprint_id', 'source_type', 'source_ref', 'title', 'meta', 'weight', 'is_enabled',
    ];

    protected $casts = [
        'meta' => 'array',
        'weight' => 'float',
        'is_enabled' => 'boolean',
    ];

    protected $attributes = [
        'weight' => 1.00,
        'is_enabled' => true,
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(AgentBlueprint::class, 'agent_blueprint_id');
    }
}
