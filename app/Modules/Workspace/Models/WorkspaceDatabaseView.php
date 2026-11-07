<?php

namespace App\Modules\Workspace\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Paparan pangkalan data — definisi lajur + filter + susun atur.
 */
class WorkspaceDatabaseView extends Model
{
    use BelongsToTenant;

    protected $table = 'workspace_database_views';

    protected $fillable = [
        'tenant_id',
        'database_id',
        'name',
        'type',
        'columns',
        'filters',
        'sorts',
        'group_by',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'columns' => 'array',
        'filters' => 'array',
        'sorts' => 'array',
        'is_default' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'type' => 'grid',
        'is_default' => false,
        'sort_order' => 0,
    ];

    public function database(): BelongsTo
    {
        return $this->belongsTo(WorkspaceDatabase::class, 'database_id');
    }

    /**
     * @return array<string, string>
     */
    public static function jenis(): array
    {
        return ['grid' => 'Grid', 'table' => 'Jadual', 'kanban' => 'Kanban', 'calendar' => 'Kalendar', 'gallery' => 'Galeri'];
    }
}
