<?php

namespace App\Modules\Workspace\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Versi sejarah kandungan halaman — menyokong rollback dan audit.
 */
class WorkspacePageVersion extends Model
{
    use BelongsToTenant;

    protected $table = 'workspace_page_versions';

    protected $fillable = [
        'tenant_id',
        'page_id',
        'title',
        'content',
        'blocks_snapshot',
        'version',
        'created_by',
    ];

    protected $casts = [
        'blocks_snapshot' => 'array',
        'version' => 'integer',
    ];

    protected $attributes = [
        'version' => 1,
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(WorkspacePage::class, 'page_id');
    }
}
