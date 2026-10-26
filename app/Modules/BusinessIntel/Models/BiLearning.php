<?php

namespace App\Modules\BusinessIntel\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BiLearning extends Model
{
    use BelongsToTenant;

    protected $table = 'bi_learnings';

    public const TYPE_PATTERN = 'pattern';
    public const TYPE_OUTCOME = 'outcome';
    public const TYPE_CORRECTION = 'correction';
    public const TYPE_PREFERENCE = 'preference';

    protected $fillable = [
        'tenant_id',
        'learning_type',
        'subject_type',
        'subject_id',
        'insight',
        'confidence',
        'evidence',
        'applied_count',
        'is_active',
    ];

    protected $casts = [
        'evidence' => 'array',
        'confidence' => 'float',
        'applied_count' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'learning_type' => self::TYPE_PATTERN,
        'confidence' => 0,
        'applied_count' => 0,
        'is_active' => true,
    ];
}
