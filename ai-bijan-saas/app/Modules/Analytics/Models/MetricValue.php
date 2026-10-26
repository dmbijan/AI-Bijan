<?php

namespace App\Modules\Analytics\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetricValue extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'analytics_metric_values';

    protected $fillable = [
        'tenant_id',
        'metric_id',
        'value',
        'calculated_at',
        'metadata',
    ];

    protected $casts = [
        'value' => 'decimal:10',
        'calculated_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relationship: Metric
     */
    public function metric()
    {
        return $this->belongsTo(Metric::class);
    }

    /**
     * Scope: By date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('calculated_at', [$from, $to]);
    }

    /**
     * Get trend data for chart
     */
    public static function getTrendData(int $metricId, int $days = 30): array
    {
        return static::where('metric_id', $metricId)
            ->where('calculated_at', '>=', now()->subDays($days))
            ->orderBy('calculated_at')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->calculated_at->format('Y-m-d'),
                    'value' => (float) $item->value,
                ];
            })
            ->toArray();
    }
}
