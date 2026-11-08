<?php

namespace App\Modules\POSBpos\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Jadual syif staf — rancangan shift mingguan/harian.
 *
 * Berasal daripada B-POS ciri `staff_roster_shifts`.
 */
class PosStaffRosterShift extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_staff_roster_shifts';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'register_id',
        'shift_date',
        'start_time',
        'end_time',
        'role',
        'status',
        'note',
        'created_by',
    ];

    protected $casts = [
        'shift_date' => 'date',
    ];

    protected $attributes = [
        'status' => 'scheduled',
    ];

    /**
     * Tempoh syif dalam jam.
     */
    public function tempohJam(): float
    {
        $mula = Carbon::parse($this->start_time);
        $tamat = Carbon::parse($this->end_time);

        return round($mula->diffInMinutes($tamat) / 60, 2);
    }
}
