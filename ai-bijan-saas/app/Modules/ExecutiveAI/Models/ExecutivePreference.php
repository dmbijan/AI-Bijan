<?php

namespace App\Modules\ExecutiveAI\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Preferensi eksekutif — bahasa, tahap autonomy, jadual briefing.
 */
class ExecutivePreference extends Model
{
    use BelongsToTenant;

    protected $table = 'executive_preferences';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'language',
        'autonomy_level',
        'briefing_time',
        'briefing_enabled',
        'voice_enabled',
        'notification_channels',
        'preferences',
    ];

    protected $casts = [
        'notification_channels' => 'array',
        'preferences' => 'array',
        'autonomy_level' => 'integer',
        'briefing_enabled' => 'boolean',
        'voice_enabled' => 'boolean',
    ];

    /**
     * Tahap autonomy yang berkesan untuk pengguna ini.
     */
    public function autonomyLevel(): int
    {
        return (int) ($this->autonomy_level ?? config('executive_ai.default_autonomy_level', 0));
    }
}
