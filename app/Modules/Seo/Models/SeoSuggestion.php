<?php

namespace App\Modules\Seo\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoSuggestion extends Model
{
    use BelongsToTenant;

    protected $table = 'seo_suggestions';

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_APPLIED = 'applied';

    protected $fillable = [
        'tenant_id',
        'seo_metadata_id',
        'object_type',
        'object_id',
        'field',
        'current_value',
        'suggested_value',
        'reasoning',
        'confidence',
        'status',
        'applied_at',
    ];

    protected $casts = [
        'confidence' => 'float',
        'applied_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'confidence' => 0,
    ];

    public function metadata(): BelongsTo
    {
        return $this->belongsTo(SeoMetadata::class, 'seo_metadata_id');
    }
}
