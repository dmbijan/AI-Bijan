<?php

namespace App\Modules\BrainHub\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insight extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'brainhub_insights';

    protected $fillable = [
        'tenant_id',
        'title',
        'description',
        'category', // financial, sales, operations, hr, marketing, customer, inventory, logistics
        'type', // trend, anomaly, opportunity, risk, recommendation
        'severity', // low, medium, high, critical
        'source_module',
        'source_metric',
        'data_points',
        'confidence_score',
        'is_read',
        'dismissed_at',
        'dismissed_by',
        'action_taken',
        'action_notes',
        'metadata',
    ];

    protected $casts = [
        'data_points' => 'array',
        'confidence_score' => 'decimal:2',
        'is_read' => 'boolean',
        'dismissed_at' => 'datetime',
        'action_taken' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Categories
     */
    const CATEGORY_FINANCIAL = 'financial';
    const CATEGORY_SALES = 'sales';
    const CATEGORY_OPERATIONS = 'operations';
    const CATEGORY_HR = 'hr';
    const CATEGORY_MARKETING = 'marketing';
    const CATEGORY_CUSTOMER = 'customer';
    const CATEGORY_INVENTORY = 'inventory';
    const CATEGORY_LOGISTICS = 'logistics';

    /**
     * Types
     */
    const TYPE_TREND = 'trend';
    const TYPE_ANOMALY = 'anomaly';
    const TYPE_OPPORTUNITY = 'opportunity';
    const TYPE_RISK = 'risk';
    const TYPE_RECOMMENDATION = 'recommendation';

    /**
     * Severity levels
     */
    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';

    /**
     * Relationship: Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Relationship: Dismissed by user
     */
    public function dismissedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'dismissed_by');
    }

    /**
     * Scope: By category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: By severity
     */
    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope: Unread insights
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope: Undismissed
     */
    public function scopeUndismissed($query)
    {
        return $query->whereNull('dismissed_at');
    }

    /**
     * Mark as read
     */
    public function markAsRead(): void
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Dismiss insight
     */
    public function dismiss(int $userId): void
    {
        $this->update([
            'dismissed_at' => now(),
            'dismissed_by' => $userId,
        ]);
    }

    /**
     * Record action taken
     */
    public function recordAction(string $notes = ''): void
    {
        $this->update([
            'action_taken' => true,
            'action_notes' => $notes,
        ]);
    }

    /**
     * Get severity label
     */
    public function getSeverityLabelAttribute(): string
    {
        return match($this->severity) {
            self::SEVERITY_LOW => 'Low',
            self::SEVERITY_MEDIUM => 'Medium',
            self::SEVERITY_HIGH => 'High',
            self::SEVERITY_CRITICAL => 'Critical',
            default => $this->severity,
        };
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_TREND => 'Trend Detected',
            self::TYPE_ANOMALY => 'Anomaly Detected',
            self::TYPE_OPPORTUNITY => 'Opportunity',
            self::TYPE_RISK => 'Risk Warning',
            self::TYPE_RECOMMENDATION => 'Recommendation',
            default => $this->type,
        };
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            self::CATEGORY_FINANCIAL => 'Financial',
            self::CATEGORY_SALES => 'Sales',
            self::CATEGORY_OPERATIONS => 'Operations',
            self::CATEGORY_HR => 'Human Resources',
            self::CATEGORY_MARKETING => 'Marketing',
            self::CATEGORY_CUSTOMER => 'Customer',
            self::CATEGORY_INVENTORY => 'Inventory',
            self::CATEGORY_LOGISTICS => 'Logistics',
            default => $this->category,
        };
    }
}
