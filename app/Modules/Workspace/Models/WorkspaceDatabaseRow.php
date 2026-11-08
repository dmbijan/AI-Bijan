<?php

namespace App\Modules\Workspace\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Baris data dalam pangkalan data ruang kerja.
 */
class WorkspaceDatabaseRow extends Model
{
    use BelongsToTenant;

    protected $table = 'workspace_database_rows';

    protected $fillable = [
        'tenant_id',
        'database_id',
        'view_id',
        'values',
        'sort_order',
        'created_by',
        'updated_by',
        'archived_at',
    ];

    protected $casts = [
        'values' => 'array',
        'sort_order' => 'integer',
        'archived_at' => 'datetime',
    ];

    protected $attributes = [
        'sort_order' => 0,
    ];

    public function database(): BelongsTo
    {
        return $this->belongsTo(WorkspaceDatabase::class, 'database_id');
    }
}
