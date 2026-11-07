<?php

namespace App\Modules\WorkManagement\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Garis masa projek (Gantt) — hubungan bergantung antara aktiviti.
 *
 * inspired oleh https://github.com/opf/openproject
 *
 * Menyokong: Gantt, boarddown, perancangan sumber, dan perbandingan
 * garis masa dengan garis dasar (baseline).
 */
class ProjectTimeline extends Model
{
    use BelongsToTenant;

    protected $table = 'project_timelines';

    public const TYPE_BAR = 'bar';

    public const TYPE_GANTT = 'gantt';

    public const TYPE_BOARDDOWN = 'boarddown';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'name',
        'type',
        'start_date',
        'end_date',
        'duration_days',
        'progress',
        'parent_id',
        'depends_on_id',
        'lag_days',
        'color',
        'is_milestone',
        'is_baseline',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'duration_days' => 'integer',
        'progress' => 'integer',
        'lag_days' => 'integer',
        'is_milestone' => 'boolean',
        'is_baseline' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'type' => self::TYPE_GANTT,
        'duration_days' => 1,
        'progress' => 0,
        'lag_days' => 0,
        'is_milestone' => false,
        'is_baseline' => false,
        'sort_order' => 0,
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function dependsOn(): BelongsTo
    {
        return $this->belongsTo(self::class, 'depends_on_id');
    }

    /**
     * Adakah aktiviti lewat daripada jadual?
     */
    public function lewat(): bool
    {
        return $this->end_date !== null && $this->end_date->isPast() && $this->progress < 100;
    }
}
