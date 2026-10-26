<?php

namespace App\Modules\BusinessIntel\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiStudy extends Model
{
    use BelongsToTenant;

    protected $table = 'bi_studies';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_RUNNING = 'running';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'tenant_id',
        'title',
        'study_type',
        'status',
        'question',
        'data_snapshot',
        'summary',
        'findings',
        'recommendations',
        'risks',
        'opportunities',
        'confidence',
        'credits_used',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'data_snapshot' => 'array',
        'findings' => 'array',
        'recommendations' => 'array',
        'risks' => 'array',
        'opportunities' => 'array',
        'confidence' => 'float',
        'credits_used' => 'integer',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'study_type' => 'general',
        'status' => self::STATUS_DRAFT,
        'confidence' => 0,
        'credits_used' => 0,
    ];

    public function ideas(): HasMany
    {
        return $this->hasMany(BiIdea::class, 'bi_study_id');
    }

    public function selesai(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
