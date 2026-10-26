<?php

namespace App\Modules\Reminder\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReminderType extends Model
{
    use BelongsToTenant;

    protected $table = 'reminder_types';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'tenant_id',
        'key',
        'name',
        'category',
        'source_module',
        'description',
        'default_priority',
        'default_lead_hours',
        'is_active',
        'requires_ai_triage',
        'default_channels',
        'meta',
    ];

    protected $casts = [
        'default_channels' => 'array',
        'meta' => 'array',
        'is_active' => 'boolean',
        'requires_ai_triage' => 'boolean',
        'default_lead_hours' => 'integer',
    ];

    protected $attributes = [
        'category' => 'general',
        'default_priority' => self::PRIORITY_NORMAL,
        'default_lead_hours' => 24,
        'is_active' => true,
        'requires_ai_triage' => false,
    ];

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class, 'reminder_type_id');
    }
}
