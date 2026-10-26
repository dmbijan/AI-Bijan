<?php

namespace App\Modules\BrainHub\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recommendation extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'brainhub_recommendations';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'title',
        'description',
        'type', // actionable, optimization, risk_mitigation, opportunity, cost_saving
        'priority', // low, medium, high, urgent
        'source_insight_id',
        'related_module',
        'related_metric',
        'action_steps',
        'estimated_impact',
        'estimated_effort',
        'estimated_savings',
        'is_accepted',
        'is_completed',
        'completed_at',
        'feedback_score',
        'feedback_notes',
        'metadata',
    ];

    protected $casts = [
        'action_steps' => 'array',
        'estimated_impact' => 'decimal:2',
        'estimated_effort' => 'string', // low, medium, high
        'estimated_savings' => 'decimal:10',
        'is_accepted' => 'boolean',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
        'feedback_score' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Types
     */
    const TYPE_ACTIONABLE = 'actionable';
    const TYPE_OPTIMIZATION = 'optimization';
    const TYPE_RISK_MITIGATION = 'risk_mitigation';
    const TYPE_OPPORTUNITY = 'opportunity';
    const TYPE_COST_SAVING = 'cost_saving';

    /**
     * Priorities
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Relationship: Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Relationship: Assigned user
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Relationship: Source insight
     */
    public function sourceInsight()
    {
        return $this->belongsTo(Insight::class, 'source_insight_id');
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: By priority
     */
    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Pending recommendations
     */
    public function scopePending($query)
    {
        return $query->where('is_accepted', false)->whereNull('is_completed');
    }

    /**
     * Accept recommendation
     */
    public function accept(): void
    {
        $this->update(['is_accepted' => true]);
    }

    /**
     * Complete recommendation
     */
    public function complete(): void
    {
        $this->update([
            'is_completed' => true,
            'completed_at' => now(),
        ]);
    }

    /**
     * Provide feedback
     */
    public function provideFeedback(int $score, string $notes = ''): void
    {
        $this->update([
            'feedback_score' => $score,
            'feedback_notes' => $notes,
        ]);
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_ACTIONABLE => 'Action Required',
            self::TYPE_OPTIMIZATION => 'Optimization',
            self::TYPE_RISK_MITIGATION => 'Risk Mitigation',
            self::TYPE_OPPORTUNITY => 'Opportunity',
            self::TYPE_COST_SAVING => 'Cost Saving',
            default => $this->type,
        };
    }

    /**
     * Get priority label
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
            default => $this->priority,
        };
    }
}
