<?php

namespace App\Modules\McpStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class McpTool extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name', 'title', 'description', 'source_module', 'action', 'method',
        'parameters_schema', 'permission_scope', 'status', 'requires_approval',
        'is_destructive', 'call_count',
    ];

    protected $casts = [
        'parameters_schema' => 'array',
        'permission_scope' => 'array',
        'requires_approval' => 'boolean',
        'is_destructive' => 'boolean',
        'call_count' => 'integer',
    ];

    protected $attributes = [
        'status' => 'draft',
        'requires_approval' => true,
        'is_destructive' => false,
        'call_count' => 0,
    ];

    public function executions(): HasMany
    {
        return $this->hasMany(McpExecution::class, 'mcp_tool_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function requiresApproval(): bool
    {
        return (bool) $this->requires_approval;
    }

    public function isDestructive(): bool
    {
        return (bool) $this->is_destructive;
    }

    /**
     * Parameter skema sebagai JSON Schema.
     */
    public function skemaParameter(): array
    {
        return $this->parameters_schema ?? [
            'type' => 'object',
            'properties' => new \stdClass(),
            'required' => [],
        ];
    }

    /**
     * Senarai parameter wajib.
     */
    public function parameterWajib(): array
    {
        return $this->skemaParameter()['required'] ?? [];
    }

    /**
     * Validasi argumen terhadap skema parameter.
     *
     * @return array{valid: bool, errors: array<int, string>}
     */
    public function validasiArgumen(array $argumen): array
    {
        $errors = [];
        $skema = $this->skemaParameter();
        $properties = $skema['properties'] ?? [];

        foreach ($this->parameterWajib() as $wajib) {
            if (! array_key_exists($wajib, $argumen) || $argumen[$wajib] === null || $argumen[$wajib] === '') {
                $errors[] = "Parameter '{$wajib}' adalah wajib";
            }
        }

        foreach ($argumen as $kunci => $nilai) {
            if (! isset($properties[$kunci])) {
                continue;
            }

            $jenis = $properties[$kunci]['type'] ?? 'string';

            if ($jenis === 'integer' && ! is_int($nilai)) {
                $errors[] = "Parameter '{$kunci}' mesti integer";
            }

            if ($jenis === 'number' && ! is_numeric($nilai)) {
                $errors[] = "Parameter '{$kunci}' mesti nombor";
            }

            if ($jenis === 'boolean' && ! is_bool($nilai)) {
                $errors[] = "Parameter '{$kunci}' mesti boolean";
            }

            if (isset($properties[$kunci]['enum']) && ! in_array($nilai, $properties[$kunci]['enum'], true)) {
                $errors[] = "Parameter '{$kunci}' mesti antara: " . implode(', ', $properties[$kunci]['enum']);
            }
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }
}
