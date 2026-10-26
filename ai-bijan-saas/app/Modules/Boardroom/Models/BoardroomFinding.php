<?php

namespace App\Modules\Boardroom\Models;

use Illuminate\Database\Eloquent\Model;

class BoardroomFinding extends Model
{
    protected $table = 'boardroom_findings';

    protected $fillable = [
        'session_id',
        'agent_key',
        'finding_type',
        'title',
        'content',
        'assumptions',
        'evidence',
        'risks',
        'source_reference',
        'confidence_score',
        'requires_review',
        'reviewed_at',
    ];

    protected $casts = [
        'assumptions' => 'array',
        'evidence' => 'array',
        'risks' => 'array',
        'confidence_score' => 'decimal:2',
        'requires_review' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    const TYPE_FACT = 'fact';
    const TYPE_ESTIMATE = 'estimate';
    const TYPE_ASSUMPTION = 'assumption';
    const TYPE_FORECAST = 'forecast';
    const TYPE_RECOMMENDATION = 'recommendation';

    public function session()
    {
        return $this->belongsTo(BoardroomSession::class, 'session_id');
    }

    /** Alias camelCase untuk `requires_review` (dipakai ujian dan paparan API). */
    public function getRequiresReviewAttribute(): bool
    {
        return (bool) $this->attributes['requires_review'];
    }

    public function scopeByAgent($query, $agentKey)
    {
        return $query->where('agent_key', $agentKey);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('finding_type', $type);
    }

    public function scopeRequiresReview($query)
    {
        return $query->where('requires_review', true)->whereNull('reviewed_at');
    }

    /**
     * jenis dapatan ini memerlukan sumber?
     */
    public function requiresSource(): bool
    {
        return config("boardroom.finding_types.{$this->finding_type}.requires_source", false);
    }

    /**
     * Dapatan ini sah?
     */
    public function isValid(): bool
    {
        if ($this->requiresSource() && empty($this->source_reference)) {
            return false;
        }

        if ($this->finding_type === self::TYPE_ASSUMPTION && empty($this->assumptions)) {
            return false;
        }

        return true;
    }
}
