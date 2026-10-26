<?php

namespace App\Modules\AgentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgentBlueprint extends Model
{
    use BelongsToTenant;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'tenant_id',
        'key', 'name', 'slug', 'description', 'category', 'persona',
        'system_prompt', 'capabilities', 'knowledge_sources', 'autonomy',
        'escalation_rules', 'model', 'temperature', 'max_tokens', 'status',
        'is_published', 'created_by', 'run_count', 'success_rate',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'knowledge_sources' => 'array',
        'autonomy' => 'array',
        'escalation_rules' => 'array',
        'temperature' => 'float',
        'max_tokens' => 'integer',
        'is_published' => 'boolean',
        'run_count' => 'integer',
        'success_rate' => 'float',
    ];

    protected $attributes = [
        'category' => 'general',
        'model' => 'default',
        'temperature' => 0.70,
        'max_tokens' => 2048,
        'status' => self::STATUS_DRAFT,
        'is_published' => false,
        'run_count' => 0,
        'success_rate' => 0.00,
    ];

    public function tools(): HasMany
    {
        return $this->hasMany(AgentToolBinding::class, 'agent_blueprint_id');
    }

    public function knowledge(): HasMany
    {
        return $this->hasMany(AgentKnowledgeBinding::class, 'agent_blueprint_id');
    }

    public function testRuns(): HasMany
    {
        return $this->hasMany(AgentTestRun::class, 'agent_blueprint_id');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(AgentRun::class, 'agent_blueprint_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isPublished(): bool
    {
        return (bool) $this->is_published;
    }

    /**
     * Tahap otonomi: 0 = manual, 1 = cadangan, 2 = separa automatik, 3 = penuh
     */
    public function tahapOtonomi(): int
    {
        return (int) ($this->autonomy['level'] ?? 0);
    }

    public function bolehLakuSendiri(): bool
    {
        return $this->tahapOtonomi() >= 2;
    }

    public function perluKelulusan(): bool
    {
        return $this->tahapOtonomi() < 2;
    }
}
