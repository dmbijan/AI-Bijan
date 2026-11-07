<?php

namespace App\Modules\Workspace\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Blok kandungan dalam halaman — unit atomik (teks, senarai, jadual, kod).
 *
 * inspired oleh AppFlowy: halaman dibina daripada blok boleh susun semula,
 * bukan satu dokumen HTML besar.
 */
class WorkspaceBlock extends Model
{
    use BelongsToTenant;

    protected $table = 'workspace_blocks';

    public const TYPE_PARAGRAPH = 'paragraph';

    public const TYPE_HEADING = 'heading';

    public const TYPE_BULLET = 'bullet';

    public const TYPE_NUMBERED = 'numbered';

    public const TYPE_TODO = 'todo';

    public const TYPE_QUOTE = 'quote';

    public const TYPE_CODE = 'code';

    public const TYPE_IMAGE = 'image';

    public const TYPE_TABLE = 'table';

    public const TYPE_CALL_OUT = 'callout';

    public const TYPE_DIVIDER = 'divider';

    public const TYPE_EMBED = 'embed';

    protected $fillable = [
        'tenant_id',
        'page_id',
        'parent_id',
        'type',
        'content',
        'data',
        'sort_order',
        'is_checked',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'data' => 'array',
        'sort_order' => 'integer',
        'is_checked' => 'boolean',
    ];

    protected $attributes = [
        'type' => self::TYPE_PARAGRAPH,
        'sort_order' => 0,
        'is_checked' => false,
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(WorkspacePage::class, 'page_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return array<string, string>
     */
    public static function jenis(): array
    {
        return [
            self::TYPE_PARAGRAPH => 'Perenggan',
            self::TYPE_HEADING => 'Tajuk',
            self::TYPE_BULLET => 'Senarai Bulat',
            self::TYPE_NUMBERED => 'Senarai Nombor',
            self::TYPE_TODO => 'Senarai Todo',
            self::TYPE_QUOTE => 'Petikan',
            self::TYPE_CODE => 'Kod',
            self::TYPE_IMAGE => 'Imej',
            self::TYPE_TABLE => 'Jadual',
            self::TYPE_CALL_OUT => 'Kotak Sorotan',
            self::TYPE_DIVIDER => 'Pembatas',
            self::TYPE_EMBED => 'Benam',
        ];
    }
}
