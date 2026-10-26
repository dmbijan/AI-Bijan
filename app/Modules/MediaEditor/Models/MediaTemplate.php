<?php

namespace App\Modules\MediaEditor\Models;

use Illuminate\Database\Eloquent\Model;

class MediaTemplate extends Model
{
    protected $table = 'media_templates';

    protected $fillable = [
        'tenant_id',
        'key',
        'name',
        'category',
        'project_type',
        'definition',
        'preview_url',
        'resolution',
        'use_count',
        'is_official',
        'is_active',
        'license',
        'meta',
    ];

    protected $casts = [
        'definition' => 'array',
        'meta' => 'array',
        'use_count' => 'integer',
        'is_official' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'category' => 'social',
        'project_type' => 'image',
        'resolution' => '1080x1080',
        'use_count' => 0,
        'is_official' => false,
        'is_active' => true,
        'license' => 'proprietary',
    ];
}
