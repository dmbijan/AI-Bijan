<?php

namespace App\Modules\Workspace\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pangkalan data dalam ruang kerja (ringkasan InnoDB ala AppFlowy).
 */
class WorkspaceDatabase extends Model
{
    use BelongsToTenant;

    protected $table = 'workspace_databases';

    protected $fillable = [
        'tenant_id',
        'space_id',
        'name',
        'description',
        'icon',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'sort_order' => 0,
    ];

    public function space(): BelongsTo
    {
        return $this->belongsTo(WorkspaceSpace::class, 'space_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(WorkspaceDatabaseView::class, 'database_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(WorkspaceDatabaseRow::class, 'database_id');
    }
}
