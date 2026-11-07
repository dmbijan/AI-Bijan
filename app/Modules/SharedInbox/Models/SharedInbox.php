<?php

namespace App\Modules\SharedInbox\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Peti masuk bersama — sekumpulan ejen berkongsi satu kotak.
 *
 * inspired oleh https://github.com/chatwoot/chatwoot
 */
class SharedInbox extends Model
{
    use BelongsToTenant;

    protected $table = 'shared_inboxes';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'channel_type',
        'provider',
        'description',
        'assignment_strategy',
        'sla_response_minutes',
        'sla_resolution_minutes',
        'business_hours',
        'working_hours_only',
        'ai_enabled',
        'ai_confidence_threshold',
        'auto_assign_enabled',
        'default_team_id',
        'welcome_message',
        'status',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'business_hours' => 'array',
        'settings' => 'array',
        'sla_response_minutes' => 'integer',
        'sla_resolution_minutes' => 'integer',
        'working_hours_only' => 'boolean',
        'ai_enabled' => 'boolean',
        'ai_confidence_threshold' => 'float',
        'auto_assign_enabled' => 'boolean',
    ];

    protected $attributes = [
        'channel_type' => 'chat',
        'assignment_strategy' => 'round_robin',
        'sla_response_minutes' => 60,
        'sla_resolution_minutes' => 1440,
        'working_hours_only' => false,
        'ai_enabled' => true,
        'ai_confidence_threshold' => 0.75,
        'auto_assign_enabled' => true,
        'status' => 'active',
    ];

    /**
     * Strategi penugasan.
     *
     * @return array<string, string>
     */
    public static function strategi(): array
    {
        return [
            'round_robin' => 'Giliran Beratur',
            'least_loaded' => 'Beban Paling Rendah',
            'manual' => 'Tugasan Manual',
            'skill_match' => 'Padanan Kemahiran',
        ];
    }
}
