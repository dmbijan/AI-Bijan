<?php

namespace App\Modules\Analytics\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Metric extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'analytics_metrics';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'category',
        'type',
        'description',
        'formula',
        'data_source',
        'data_source_table',
        'data_source_column',
        'aggregation_type', // sum, avg, count, min, max, ratio, cumulative
        'unit',
        'decimal_places',
        'target_value',
        'threshold_warning',
        'threshold_critical',
        'is_active',
        'refresh_interval_minutes',
        'last_calculated_at',
        'last_value',
        'previous_value',
        'trend_direction', // up, down, stable
        'trend_percentage',
        'metadata',
    ];

    protected $casts = [
        'formula' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'target_value' => 'decimal:2',
        'threshold_warning' => 'decimal:2',
        'threshold_critical' => 'decimal:2',
        'last_value' => 'decimal:10',
        'previous_value' => 'decimal:10',
        'trend_percentage' => 'decimal:2',
        'decimal_places' => 'integer',
        'refresh_interval_minutes' => 'integer',
        'calculated_at' => 'datetime',
    ];

    /**
     * Categories
     */
    const CATEGORY_FINANCE = 'finance';
    const CATEGORY_SALES = 'sales';
    const CATEGORY_OPERATIONS = 'operations';
    const CATEGORY_HR = 'hr';
    const CATEGORY_MARKETING = 'marketing';
    const CATEGORY_CUSTOMER = 'customer';
    const CATEGORY_INVENTORY = 'inventory';
    const CATEGORY_CUSTOM = 'custom';

    /**
     * Types
     */
    const TYPE_KPI = 'kpi';
    const TYPE_METRIC = 'metric';
    const TYPE_RATIO = 'ratio';
    const TYPE_RATE = 'rate';
    const TYPE_SCORE = 'score';

    /**
     * Aggregation types
     */
    const AGG_SUM = 'sum';
    const AGG_AVG = 'avg';
    const AGG_COUNT = 'count';
    const AGG_MIN = 'min';
    const AGG_MAX = 'max';
    const AGG_RATIO = 'ratio';
    const AGG_CUMULATIVE = 'cumulative';

    /**
     * Trend directions
     */
    const TREND_UP = 'up';
    const TREND_DOWN = 'down';
    const TREND_STABLE = 'stable';

    /**
     * Relationship: Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Relationship: Metric values history
     */
    public function values()
    {
        return $this->hasMany(MetricValue::class);
    }

    /**
     * Scope: Active metrics
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
     * Calculate metric value
     */
    public function calculate(): float
    {
        $value = $this->evaluateFormula();
        
        // Calculate trend
        $previous = $this->values()->orderBy('calculated_at', 'desc')->value('value');
        if ($previous && $previous != 0) {
            $this->trend_percentage = (($value - $previous) / abs($previous)) * 100;
            $this->trend_direction = $this->trend_percentage > 0 ? self::TREND_UP : 
                                     ($this->trend_percentage < 0 ? self::TREND_DOWN : self::TREND_STABLE);
        }

        $this->last_value = $value;
        $this->previous_value = $previous;
        $this->last_calculated_at = now();
        $this->save();

        // Store in history
        MetricValue::create([
            'tenant_id' => $this->tenant_id,
            'metric_id' => $this->id,
            'value' => $value,
            'calculated_at' => now(),
        ]);

        return $value;
    }

    /**
     * Evaluate formula
     */
    protected function evaluateFormula(): float
    {
        // Placeholder - implement based on data_source and aggregation_type
        return 0;
    }

    /**
     * Get status indicator
     */
    public function getStatusAttribute(): string
    {
        if (!$this->last_value) return 'pending';
        
        if ($this->threshold_critical && $this->last_value <= $this->threshold_critical) {
            return 'critical';
        }
        
        if ($this->threshold_warning && $this->last_value <= $this->threshold_warning) {
            return 'warning';
        }
        
        return 'ok';
    }

    /**
     * Get category label
     */
    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            self::CATEGORY_FINANCE => 'Finance',
            self::CATEGORY_SALES => 'Sales',
            self::CATEGORY_OPERATIONS => 'Operations',
            self::CATEGORY_HR => 'Human Resources',
            self::CATEGORY_MARKETING => 'Marketing',
            self::CATEGORY_CUSTOMER => 'Customer',
            self::CATEGORY_INVENTORY => 'Inventory',
            self::CATEGORY_CUSTOM => 'Custom',
            default => $this->category,
        };
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_KPI => 'KPI',
            self::TYPE_METRIC => 'Metric',
            self::TYPE_RATIO => 'Ratio',
            self::TYPE_RATE => 'Rate',
            self::TYPE_SCORE => 'Score',
            default => $this->type,
        };
    }
}
