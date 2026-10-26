<?php

namespace App\Modules\ProofOfValue\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Baseline — titik mula pengiraan perubahan.
 */
class PovBaseline extends Model
{
    use BelongsToTenant;

    protected $table = 'pov_baselines';

    protected $fillable = [
        'tenant_id',
        'metric_id',
        'baseline_date',
        'baseline_value',
        'source',
        'note',
        'recorded_by',
    ];

    protected $casts = [
        'baseline_date' => 'date',
        'baseline_value' => 'decimal:2',
    ];

    public function metric(): BelongsTo
    {
        return $this->belongsTo(PovMetric::class, 'metric_id');
    }
}
