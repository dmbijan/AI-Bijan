<?php

namespace App\Modules\PersonalAssistant\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class UserPreference extends Model
{
    use BelongsToTenant;

    protected $table = 'ai_user_preferences';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'language',
        'tone',
        'report_format',
        'preferred_model',
        'default_assistant',
        'timezone',
        'notification_channels',
        'quiet_hours_start',
        'quiet_hours_end',
        'custom_instructions',
    ];

    protected $casts = [
        'notification_channels' => 'array',
    ];

    public function scopeForUser($query, $tenantId, $userId)
    {
        return $query->where('tenant_id', $tenantId)->where('user_id', $userId);
    }

    /**
     * Dapatkan atau cipta preferensi default
     */
    public static function forUser(int $tenantId, int $userId): self
    {
        return static::firstOrCreate(
            ['tenant_id' => $tenantId, 'user_id' => $userId],
            [
                'language' => 'ms',
                'tone' => 'formal',
                'report_format' => 'standard',
                'default_assistant' => 'owner',
                'timezone' => 'Asia/Kuala_Lumpur',
                'notification_channels' => ['in_app', 'email'],
            ]
        );
    }

    public function isWithinQuietHours(): bool
    {
        if (!$this->quiet_hours_start || !$this->quiet_hours_end) {
            return false;
        }

        $now = now($this->timezone ?? config('app.timezone'));
        $current = $now->format('H:i:s');

        if ($this->quiet_hours_start <= $this->quiet_hours_end) {
            return $current >= $this->quiet_hours_start && $current <= $this->quiet_hours_end;
        }

        // Merentas tengah malam
        return $current >= $this->quiet_hours_start || $current <= $this->quiet_hours_end;
    }
}
