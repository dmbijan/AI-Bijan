<?php

namespace App\Modules\WorkManagement\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Garis dasar (baseline) — snapshot jadual untuk perbandingan.
 */
class ProjectBaseline extends Model
{
    use BelongsToTenant;

    protected $table = 'project_baselines';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'name',
        'captured_at',
        'scheduled_start',
        'scheduled_end',
        'planned_duration_days',
        'planned_cost',
        'actual_cost',
        'variance_days',
        'variance_cost',
        'snapshot',
        'notes',
        'captured_by',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
        'scheduled_start' => 'date',
        'scheduled_end' => 'date',
        'planned_duration_days' => 'integer',
        'planned_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'variance_days' => 'integer',
        'variance_cost' => 'decimal:2',
        'snapshot' => 'array',
    ];

    protected $attributes = [
        'planned_duration_days' => 0,
        'planned_cost' => 0,
        'actual_cost' => 0,
        'variance_days' => 0,
        'variance_cost' => 0,
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
