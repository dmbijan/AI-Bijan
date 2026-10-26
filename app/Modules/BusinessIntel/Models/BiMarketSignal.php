<?php

namespace App\Modules\BusinessIntel\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BiMarketSignal extends Model
{
    use BelongsToTenant;

    protected $table = 'bi_market_signals';

    public const TYPE_TREND = 'trend';
    public const TYPE_KEYWORD = 'keyword';
    public const TYPE_CATEGORY = 'category';
    public const TYPE_SEASON = 'season';
    public const TYPE_REGULATION = 'regulation';

    protected $fillable = [
        'tenant_id',
        'signal_type',
        'subject',
        'direction',
        'strength',
        'summary',
        'evidence',
        'source',
        'observed_at',
    ];

    protected $casts = [
        'evidence' => 'array',
        'strength' => 'float',
        'observed_at' => 'date',
    ];

    protected $attributes = [
        'direction' => 'up',
        'strength' => 0,
        'source' => 'ai',
    ];
}
