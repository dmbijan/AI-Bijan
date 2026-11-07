<?php

namespace App\Modules\Scheduling\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Pautan penjadualan — halaman tempahan yang dikongsi.
 *
 * inspired oleh https://github.com/calcom/cal.diy
 *
 * Setiap pautan menunjuk ke satu atau lebih servis, dengan windows
 * ketersediaan, tempoh minimum notifikasi dan routing round-robin.
 */
class SchedulingLink extends Model
{
    use BelongsToTenant;

    protected $table = 'scheduling_links';

    public const DURATION_MINUTES = 15;

    public const DURATION_HOURLY = 60;

    protected $fillable = [
        'tenant_id',
        'title',
        'slug',
        'description',
        'service_ids',
        'provider_ids',
        'duration_minutes',
        'interval_minutes',
        'buffer_before_minutes',
        'buffer_after_minutes',
        'minimum_notice_minutes',
        'max_booking_days',
        'timezone',
        'availability_rules',
        'routing_type',
        'routing_strategy',
        'max_bookings_per_day',
        'requires_confirmation',
        'is_active',
        'is_public',
        'hide_booking_details',
        'require_phone',
        'require_name',
        'redirect_url',
        'utm_params',
        'booking_count',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'service_ids' => 'array',
        'provider_ids' => 'array',
        'availability_rules' => 'array',
        'utm_params' => 'array',
        'duration_minutes' => 'integer',
        'interval_minutes' => 'integer',
        'buffer_before_minutes' => 'integer',
        'buffer_after_minutes' => 'integer',
        'minimum_notice_minutes' => 'integer',
        'max_booking_days' => 'integer',
        'max_bookings_per_day' => 'integer',
        'requires_confirmation' => 'boolean',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'hide_booking_details' => 'boolean',
        'require_phone' => 'boolean',
        'require_name' => 'boolean',
        'booking_count' => 'integer',
        'expires_at' => 'datetime',
    ];

    protected $attributes = [
        'duration_minutes' => self::DURATION_MINUTES,
        'interval_minutes' => 15,
        'buffer_before_minutes' => 0,
        'buffer_after_minutes' => 0,
        'minimum_notice_minutes' => 120,
        'max_booking_days' => 60,
        'timezone' => 'Asia/Kuala_Lumpur',
        'routing_type' => 'collective',
        'routing_strategy' => 'round_robin',
        'max_bookings_per_day' => 8,
        'requires_confirmation' => false,
        'is_active' => true,
        'is_public' => true,
        'hide_booking_details' => false,
        'require_phone' => false,
        'require_name' => true,
        'booking_count' => 0,
    ];

    /**
     * Strategi routing.
     *
     * @return array<string, string>
     */
    public static function strategi(): array
    {
        return [
            'round_robin' => 'Giliran Beratur',
            'least_loaded' => 'Beban Paling Rendah',
            'random' => 'Rawak',
            'skill_match' => 'Padanan Kemahiran',
        ];
    }
}
