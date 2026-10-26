<?php

namespace App\Modules\WorkflowStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use BelongsToTenant;

    protected $table = 'studio_workflows';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_ARCHIVED = 'archived';

    public const TRIGGER_MANUAL = 'manual';
    public const TRIGGER_SCHEDULE = 'schedule';
    public const TRIGGER_EVENT = 'event';
    public const TRIGGER_WEBHOOK = 'webhook';
    public const TRIGGER_AI = 'ai';

    protected $fillable = [
        'tenant_id',
        'workflow_template_id',
        'name',
        'description',
        'definition',
        'status',
        'trigger_type',
        'trigger_config',
        'is_enabled',
        'dry_run_mode',
        'run_count',
        'success_count',
        'failure_count',
        'last_run_at',
        'meta',
    ];

    protected $casts = [
        'definition' => 'array',
        'meta' => 'array',
        'is_enabled' => 'boolean',
        'dry_run_mode' => 'boolean',
        'run_count' => 'integer',
        'success_count' => 'integer',
        'failure_count' => 'integer',
        'last_run_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'trigger_type' => self::TRIGGER_MANUAL,
        'is_enabled' => false,
        'dry_run_mode' => true,
        'run_count' => 0,
        'success_count' => 0,
        'failure_count' => 0,
    ];

    public function runs(): HasMany
    {
        return $this->hasMany(WorkflowRun::class, 'workflow_id');
    }

    /** Senarai node dalam definisi. */
    public function nodes(): array
    {
        return $this->definition['nodes'] ?? [];
    }

    /** Senarai edge (sambungan) dalam definisi. */
    public function edges(): array
    {
        return $this->definition['edges'] ?? [];
    }

    public function bolehLaksana(): bool
    {
        return $this->is_enabled && $this->status === self::STATUS_ACTIVE;
    }

    /** Kesan node permulaan (tiada incoming edge). */
    public function nodePermulaan(): ?string
    {
        $nodes = $this->nodes();
        if (empty($nodes)) {
            return null;
        }

        $idsDenganIncoming = array_map(fn ($e) => $e['to'] ?? null, $this->edges());
        $idsDenganIncoming = array_filter($idsDenganIncoming);

        foreach ($nodes as $n) {
            $id = $n['id'] ?? null;
            if ($id !== null && ! in_array($id, $idsDenganIncoming, true)) {
                return $id;
            }
        }

        return $nodes[0]['id'] ?? null;
    }

    /** Kesan node seterusnya selepas node tertentu. */
    public function nodeSeterusnya(string $nodeId): array
    {
        $ids = [];

        foreach ($this->edges() as $e) {
            if (($e['from'] ?? null) === $nodeId) {
                $ids[] = $e['to'] ?? null;
            }
        }

        return array_values(array_filter($ids));
    }
}
