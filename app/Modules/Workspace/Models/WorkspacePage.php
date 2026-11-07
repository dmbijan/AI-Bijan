<?php

namespace App\Modules\Workspace\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Halaman dalam ruang kerja — unit dokumen asas (seperti AppFlowy page).
 */
class WorkspacePage extends Model
{
    use BelongsToTenant;

    protected $table = 'workspace_pages';

    protected $fillable = [
        'tenant_id',
        'space_id',
        'title',
        'slug',
        'icon',
        'cover_url',
        'content',
        'is_full_width',
        'is_locked',
        'locked_by',
        'sort_order',
        'created_by',
        'updated_by',
        'last_edited_at',
        'published_at',
    ];

    protected $casts = [
        'is_full_width' => 'boolean',
        'is_locked' => 'boolean',
        'sort_order' => 'integer',
        'last_edited_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected $attributes = [
        'is_full_width' => false,
        'is_locked' => false,
        'sort_order' => 0,
    ];

    public function space(): BelongsTo
    {
        return $this->belongsTo(WorkspaceSpace::class, 'space_id');
    }

    public function blocks(): HasMany
    {
        return $this->hasMany(WorkspaceBlock::class, 'page_id')->orderBy('sort_order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(WorkspaceComment::class, 'page_id');
    }
}
