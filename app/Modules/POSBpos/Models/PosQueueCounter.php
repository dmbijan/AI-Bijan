<?php

namespace App\Modules\POSBpos\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Kaunter nombor giliran — rolling counter untuk panggilan pesanan.
 *
 * Berasal daripada B-POS ciri `queue_counters`.
 */
class PosQueueCounter extends Model
{
    use BelongsToTenant;

    protected $table = 'pos_queue_counters';

    protected $fillable = [
        'tenant_id',
        'food_court_id',
        'register_id',
        'counter_date',
        'shift_key',
        'prefix',
        'last_number',
        'current_number',
        'called_at',
    ];

    protected $casts = [
        'counter_date' => 'date',
        'last_number' => 'integer',
        'current_number' => 'integer',
        'called_at' => 'datetime',
    ];

    protected $attributes = [
        'prefix' => 'A',
        'last_number' => 0,
        'current_number' => 0,
    ];

    /**
     * Nombor giliran seterusnya (contoh: A012).
     */
    public function nomborSeterusnya(): string
    {
        return $this->prefix.str_pad((string) ($this->last_number + 1), 3, '0', STR_PAD_LEFT);
    }
}
