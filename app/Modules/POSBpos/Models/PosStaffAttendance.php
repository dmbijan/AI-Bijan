<?php

namespace App\Modules\POSBpos\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Kehadiran staf — masa masuk/keluar syif kerja.
 *
 * Berasal daripada B-POS ciri `staff_attendances`.
 */
class PosStaffAttendance extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_staff_attendances';

    public const STATUS_PRESENT = 'present';

    public const STATUS_LATE = 'late';

    public const STATUS_ABSENT = 'absent';

    public const STATUS_LEAVE = 'leave';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'register_id',
        'attendance_date',
        'clock_in_at',
        'clock_out_at',
        'status',
        'minutes_late',
        'minutes_worked',
        'note',
        'created_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
        'minutes_late' => 'integer',
        'minutes_worked' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_PRESENT,
        'minutes_late' => 0,
        'minutes_worked' => 0,
    ];

    public function jamKerja(): float
    {
        return round($this->minutes_worked / 60, 2);
    }
}
