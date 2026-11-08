<?php

namespace App\Modules\Workspace\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Komen pada halaman — sokongan perbincangan bersama.
 */
class WorkspaceComment extends Model
{
    use BelongsToTenant;

    protected $table = 'workspace_comments';

    protected $fillable = [
        'tenant_id',
        'page_id',
        'block_id',
        'parent_id',
        'body',
        'is_resolved',
        'resolved_by',
        'resolved_at',
        'created_by',
    ];

    protected $casts = [
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    protected $attributes = [
        'is_resolved' => false,
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(WorkspacePage::class, 'page_id');
    }
}
