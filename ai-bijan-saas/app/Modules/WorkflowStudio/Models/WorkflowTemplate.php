<?php

namespace App\Modules\WorkflowStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class WorkflowTemplate extends Model
{
    // Template global (tenant_id null) + template tenant — jangan guna trait
    // kerana trait menyekat rekod tanpa tenant aktif.
    protected $table = 'studio_workflow_templates';

    protected $fillable = [
        'tenant_id',
        'key',
        'name',
        'category',
        'description',
        'definition',
        'required_connectors',
        'required_permissions',
        'install_count',
        'is_official',
        'is_active',
        'license',
        'meta',
    ];

    protected $casts = [
        'definition' => 'array',
        'required_connectors' => 'array',
        'required_permissions' => 'array',
        'meta' => 'array',
        'install_count' => 'integer',
        'is_official' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'category' => 'general',
        'install_count' => 0,
        'is_official' => false,
        'is_active' => true,
        'license' => 'proprietary',
    ];
}
