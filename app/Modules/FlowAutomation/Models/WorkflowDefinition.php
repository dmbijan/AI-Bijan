<?php

namespace App\Modules\FlowAutomation\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class WorkflowDefinition extends Model
{
    use BelongsToTenant;

    protected $table = 'workflow_definitions';

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'trigger_type',
        'trigger_config',
        'conditions',
        'steps',
        'is_active',
        'requires_approval',
        'version',
        'created_by',
        'last_run_at',
        'run_count',
        'failure_count',
        'kill_switch',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'conditions' => 'array',
        'steps' => 'array',
        'is_active' => 'boolean',
        'requires_approval' => 'boolean',
        'kill_switch' => 'boolean',
        'last_run_at' => 'datetime',
        'version' => 'integer',
        'run_count' => 'integer',
        'failure_count' => 'integer',
    ];

    public function runs()
    {
        return $this->hasMany(WorkflowRun::class, 'workflow_id');
    }

    // ===== Scopes =====

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('kill_switch', false);
    }

    public function scopeByTrigger($query, $triggerType)
    {
        return $query->where('trigger_type', $triggerType);
    }

    // ===== Helpers =====

    /**
     * Boleh dijalankan sekarang?
     */
    public function isRunnable(): bool
    {
        return $this->is_active && !$this->kill_switch;
    }

    /**
     * Cari workflow aktif yang sepadan dengan event
     */
    public static function findForEvent(int $tenantId, string $eventName): \Illuminate\Support\Collection
    {
        return static::forTenant($tenantId)
            ->active()
            ->byTrigger('domain_event')
            ->get()
            ->filter(function (self $workflow) use ($eventName) {
                $config = $workflow->trigger_config ?? [];
                return ($config['event_name'] ?? null) === $eventName
                    || ($config['event'] ?? null) === $eventName;
            })
            ->values();
    }

    /**
     * Semak semua syarat terhadap data
     */
    public function conditionsMatch(array $data): bool
    {
        foreach ($this->conditions ?? [] as $condition) {
            if (!$this->evaluateCondition($condition, $data)) {
                return false;
            }
        }

        return true;
    }

    protected function evaluateCondition(array $condition, array $data): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? 'equals';
        $expected = $condition['value'] ?? null;

        $actual = data_get($data, $field);

        return match ($operator) {
            'equals' => $actual == $expected,
            'not_equals' => $actual != $expected,
            'greater_than' => $actual > $expected,
            'greater_or_equal' => $actual >= $expected,
            'less_than' => $actual < $expected,
            'less_or_equal' => $actual <= $expected,
            'contains' => is_string($actual) && str_contains($actual, (string) $expected),
            'not_contains' => is_string($actual) && !str_contains($actual, (string) $expected),
            'in' => is_array($expected) && in_array($actual, $expected),
            'not_in' => is_array($expected) && !in_array($actual, $expected),
            'is_empty' => empty($actual),
            'is_not_empty' => !empty($actual),
            'changed' => ($data['_changed'][$field] ?? false) === true,
            default => false,
        };
    }

    /**
     * Bilangan langkah
     */
    public function stepCount(): int
    {
        return count($this->steps ?? []);
    }
}
