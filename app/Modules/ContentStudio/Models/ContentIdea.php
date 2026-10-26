<?php

namespace App\Modules\ContentStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ContentIdea extends Model
{
    use BelongsToTenant;

    protected $table = 'content_ideas';

    public const STATUS_NEW = 'new';
    public const STATUS_USED = 'used';
    public const STATUS_DISMISSED = 'dismissed';

    protected $fillable = [
        'tenant_id',
        'title',
        'notes',
        'source',
        'category',
        'keywords',
        'status',
        'used_in_piece_id',
        'score',
    ];

    protected $casts = [
        'keywords' => 'array',
        'score' => 'float',
    ];

    protected $attributes = [
        'source' => 'manual',
        'status' => self::STATUS_NEW,
        'score' => 0,
    ];
}
