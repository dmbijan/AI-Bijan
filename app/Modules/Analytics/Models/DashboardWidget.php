<?php

namespace App\Modules\Analytics\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DashboardWidget extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'dashboard_widgets';

    protected $fillable = [
        'tenant_id',
        'dashboard_id',
        'title',
        'type', // kpi_card, line_chart, bar_chart, pie_chart, table, gauge, trend, metric
        'metric_id',
        'chart_config',
        'data_source',
        'filters',
        'position_x',
        'position_y',
        'width',
        'height',
        'refresh_interval',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'chart_config' => 'array',
        'filters' => 'array',
        'position_x' => 'integer',
        'position_y' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'refresh_interval' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Widget types
     */
    const TYPE_KPI_CARD = 'kpi_card';
    const TYPE_LINE_CHART = 'line_chart';
    const TYPE_BAR_CHART = 'bar_chart';
    const TYPE_PIE_CHART = 'pie_chart';
    const TYPE_TABLE = 'table';
    const TYPE_GAUGE = 'gauge';
    const TYPE_TREND = 'trend';
    const TYPE_METRIC = 'metric';

    /**
     * Relationship: Tenant
     */
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Relationship: Dashboard
     */
    public function dashboard()
    {
        return $this->belongsTo(Dashboard::class);
    }

    /**
     * Relationship: Metric
     */
    public function metric()
    {
        return $this->belongsTo(Metric::class);
    }

    /**
     * Scope: By type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Active widgets
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get type label
     */
    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_KPI_CARD => 'KPI Card',
            self::TYPE_LINE_CHART => 'Line Chart',
            self::TYPE_BAR_CHART => 'Bar Chart',
            self::TYPE_PIE_CHART => 'Pie Chart',
            self::TYPE_TABLE => 'Table',
            self::TYPE_GAUGE => 'Gauge',
            self::TYPE_TREND => 'Trend',
            self::TYPE_METRIC => 'Metric',
            default => $this->type,
        };
    }
}
