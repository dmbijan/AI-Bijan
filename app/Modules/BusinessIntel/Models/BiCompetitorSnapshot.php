<?php

namespace App\Modules\BusinessIntel\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiCompetitorSnapshot extends Model
{
    use BelongsToTenant;

    protected $table = 'bi_competitor_snapshots';

    protected $fillable = [
        'tenant_id',
        'bi_competitor_id',
        'snapshot_date',
        'observed_data',
        'changes',
        'ai_analysis',
        'threats',
        'opportunities',
        'source_type',
        'source_url',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'observed_data' => 'array',
        'changes' => 'array',
        'threats' => 'array',
        'opportunities' => 'array',
    ];

    protected $attributes = [
        'source_type' => 'manual',
    ];

    public function competitor(): BelongsTo
    {
        return $this->belongsTo(BiCompetitor::class, 'bi_competitor_id');
    }
}
