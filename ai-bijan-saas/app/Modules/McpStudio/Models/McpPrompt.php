<?php

namespace App\Modules\McpStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class McpPrompt extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name', 'title', 'description', 'template',
        'arguments_schema', 'status', 'use_count',
    ];

    protected $casts = [
        'arguments_schema' => 'array',
        'use_count' => 'integer',
    ];

    protected $attributes = [
        'status' => 'active',
        'use_count' => 0,
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Render templat prompt dengan argumen.
     *
     * Ganti {{argumen}} dengan nilai sebenar.
     */
    public function render(array $argumen = []): string
    {
        $hasil = $this->template;

        foreach ($argumen as $kunci => $nilai) {
            $hasil = str_replace(['{{' . $kunci . '}}', '{{' . $kunci . '}}'], (string) $nilai, $hasil);
        }

        return $hasil;
    }

    /**
     * Senarai argumen wajib daripada schema.
     */
    public function argumentWajib(): array
    {
        return $this->arguments_schema['required'] ?? [];
    }
}
