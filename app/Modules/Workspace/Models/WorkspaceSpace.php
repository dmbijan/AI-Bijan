<?php

namespace App\Modules\Workspace\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Ruang kerja — bekas untuk dokumen dan pangkalan data.
 *
 * inspired oleh https://github.com/AppFlowy-IO/AppFlowy
 */
class WorkspaceSpace extends Model
{
    use BelongsToTenant;

    protected $table = 'workspace_spaces';

    public const KIND_DOCUMENT = 'document';

    public const KIND_DATABASE = 'database';

    public const KIND_BOARD = 'board';

    public const KIND_FOLDER = 'folder';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'kind',
        'icon',
        'color',
        'parent_id',
        'is_favorite',
        'is_archived',
        'sort_order',
        'created_by',
        'last_opened_at',
    ];

    protected $casts = [
        'is_favorite' => 'boolean',
        'is_archived' => 'boolean',
        'sort_order' => 'integer',
        'last_opened_at' => 'datetime',
    ];

    protected $attributes = [
        'kind' => self::KIND_DOCUMENT,
        'is_favorite' => false,
        'is_archived' => false,
        'sort_order' => 0,
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(WorkspacePage::class, 'space_id');
    }

    /**
     * @return array<string, string>
     */
    public static function jenis(): array
    {
        return [
            self::KIND_DOCUMENT => 'Dokumen',
            self::KIND_DATABASE => 'Pangkalan Data',
            self::KIND_BOARD => 'Papan',
            self::KIND_FOLDER => 'Folder',
        ];
    }
}
