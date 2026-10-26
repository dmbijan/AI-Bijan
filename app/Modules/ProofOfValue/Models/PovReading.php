<?php

namespace App\Modules\ProofOfValue\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Bacaan metrik pada satu tarikh.
 */
class PovReading extends Model
{
    use BelongsToTenant;

    protected $table = 'pov_readings';

    protected $fillable = [
        'tenant_id',
        'metric_id',
        'reading_date',
        'value',
        'change_percent',
        'source',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'reading_date' => 'date',
        'value' => 'decimal:2',
        'change_percent' => 'decimal:2',
    ];

    public function metric(): BelongsTo
    {
        return $this->belongsTo(PovMetric::class, 'metric_id');
    }
}
