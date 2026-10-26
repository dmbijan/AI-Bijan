<?php

namespace App\Modules\McpStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class McpResource extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'uri', 'name', 'mime_type', 'source_table', 'columns',
        'row_filter', 'status', 'access_count',
    ];

    protected $casts = [
        'columns' => 'array',
        'row_filter' => 'array',
        'access_count' => 'integer',
    ];

    protected $attributes = [
        'mime_type' => 'application/json',
        'status' => 'active',
        'access_count' => 0,
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     URI sumber dalam format MCP: module://resource/id
     */
    public function dibinaUri(?int $id = null): string
    {
        if ($id === null) {
            return $this->uri;
        }

        return rtrim($this->uri, '/') . '/' . $id;
    }
}
