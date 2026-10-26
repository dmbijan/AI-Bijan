<?php

namespace App\Modules\Fundamental\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Snapshot metrik berkala — untuk sparkline, grafik dan alert evaluation.
 */
class MetricSnapshot extends Model
{
    use BelongsToTenant;

    public const TYPE_GAUGE = 'gauge';
    public const TYPE_COUNTER = 'counter';
    public const TYPE_HISTOGRAM = 'histogram';

    public const TYPES = [
        self::TYPE_GAUGE,
        self::TYPE_COUNTER,
        self::TYPE_HISTOGRAM,
    ];

    protected $table = 'metric_snapshots';

    protected $fillable = [
        'tenant_id',
        'metric_key',
        'name',
        'type',
        'labels',
        'value',
        'unit',
        'recorded_at',
    ];

    protected $casts = [
        'value' => 'double',
        'recorded_at' => 'datetime',
    ];

    /**
     * Ambil siri nilai untuk metrik (untuk sparkline).
     */
    public static function siri(?int $tenantId, string $metricKey, int $had = 30): array
    {
        $query = static::query()
            ->where('metric_key', $metricKey)
            ->orderByDesc('recorded_at')
            ->limit($had);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->get()
            ->sortBy([
                ['recorded_at', 'asc'],
                ['id', 'asc'],
            ])
            ->values()
            ->map(fn ($s) => [
                'recorded_at' => $s->recorded_at?->toIso8601String(),
                'value' => (float) $s->value,
            ])
            ->all();
    }
}
