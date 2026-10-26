<?php

namespace App\Modules\BusinessIntel\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiIdea extends Model
{
    use BelongsToTenant;

    protected $table = 'bi_ideas';

    public const IMPACT_LOW = 'low';
    public const IMPACT_MEDIUM = 'medium';
    public const IMPACT_HIGH = 'high';

    public const STATUS_NEW = 'new';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    protected $fillable = [
        'tenant_id',
        'bi_study_id',
        'title',
        'description',
        'category',
        'source',
        'impact',
        'effort',
        'impact_score',
        'confidence',
        'evidence',
        'expected_outcome',
        'status',
        'accepted_at',
        'done_at',
        'actual_outcome',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'impact_score' => 'float',
        'confidence' => 'float',
        'actual_outcome' => 'float',
        'accepted_at' => 'datetime',
        'done_at' => 'datetime',
    ];

    protected $attributes = [
        'category' => 'growth',
        'source' => 'ai',
        'impact' => self::IMPACT_MEDIUM,
        'effort' => self::IMPACT_MEDIUM,
        'impact_score' => 0,
        'confidence' => 0,
        'status' => self::STATUS_NEW,
    ];

    public function study(): BelongsTo
    {
        return $this->belongsTo(BiStudy::class, 'bi_study_id');
    }

    /**
     * Nisbah impak/effort — keutamaan.
     */
    public function nisbahKeutamaan(): float
    {
        $effortBerat = ['low' => 1, 'medium' => 2, 'high' => 3][$this->effort] ?? 2;

        return round((float) $this->impact_score / $effortBerat, 2);
    }

    public function bolehTerima(): bool
    {
        return $this->status === self::STATUS_NEW;
    }
}
