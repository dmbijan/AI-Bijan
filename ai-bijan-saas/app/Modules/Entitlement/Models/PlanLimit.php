<?php

namespace App\Modules\Entitlement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Had penggunaan bagi satu plan (seats, AI tokens, storan, dll).
 *
 * `limit_value` null = tiada had (unlimited).
 */
class PlanLimit extends Model
{
    protected $table = 'plan_limits';

    protected $fillable = [
        'plan_id',
        'limit_key',
        'name',
        'limit_value',
        'unit',
        'period',
        'metadata',
    ];

    protected $casts = [
        'limit_value' => 'integer',
        'metadata' => 'array',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function tanpaHad(): bool
    {
        return $this->limit_value === null;
    }
}
