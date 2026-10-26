<?php

namespace App\Modules\Reminder\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderSchedule extends Model
{
    use BelongsToTenant;

    protected $table = 'reminder_schedules';

    public const FREQ_ONCE = 'once';
    public const FREQ_DAILY = 'daily';
    public const FREQ_WEEKLY = 'weekly';
    public const FREQ_MONTHLY = 'monthly';
    public const FREQ_QUARTERLY = 'quarterly';
    public const FREQ_ANNUALLY = 'annually';

    protected $fillable = [
        'tenant_id',
        'reminder_type_id',
        'type_key',
        'name',
        'frequency',
        'time_of_day',
        'interval_count',
        'day_of_week',
        'day_of_month',
        'timezone',
        'channels',
        'is_active',
        'last_run_at',
        'next_run_at',
        'meta',
    ];

    protected $casts = [
        'channels' => 'array',
        'meta' => 'array',
        'is_active' => 'boolean',
        'interval_count' => 'integer',
        'day_of_week' => 'integer',
        'day_of_month' => 'integer',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    protected $attributes = [
        'frequency' => self::FREQ_DAILY,
        'timezone' => 'Asia/Kuala_Lumpur',
        'interval_count' => 1,
        'is_active' => true,
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ReminderType::class, 'reminder_type_id');
    }

    /**
     * Kira tarikh run seterusnya.
     */
    public function kiraRunSeterusnya(?\DateTimeInterface $dari = null): \Illuminate\Support\Carbon
    {
        $dari = $dari ? \Illuminate\Support\Carbon::instance($dari) : now();

        return match ($this->frequency) {
            self::FREQ_ONCE => $this->next_run_at
                ? \Illuminate\Support\Carbon::instance($this->next_run_at)
                : $dari->addDay(),
            self::FREQ_DAILY => $dari->addDays(max(1, (int) $this->interval_count)),
            self::FREQ_WEEKLY => $dari->addWeeks(max(1, (int) $this->interval_count)),
            self::FREQ_MONTHLY => $dari->addMonths(max(1, (int) $this->interval_count)),
            self::FREQ_QUARTERLY => $dari->addMonths(3 * max(1, (int) $this->interval_count)),
            self::FREQ_ANNUALLY => $dari->addYears(max(1, (int) $this->interval_count)),
            default => $dari->addDay(),
        };
    }
}
