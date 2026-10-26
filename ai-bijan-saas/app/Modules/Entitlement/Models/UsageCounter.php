<?php

namespace App\Modules\Entitlement\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Kaunter penggunaan bagi satu had (tenant scoped).
 *
 * `used_value` — usage yang sudah diset.
 * `reserved_value` — usage yang dikuatkuasakan tetapi belum selesai.
 *
 * Untuk AI tokens: reserve dahulu (atomic), kemudian settle. Ini
 * langkah pencegahan credit overspend.
 */
class UsageCounter extends Model
{
    protected $table = 'usage_counters';

    protected $fillable = [
        'tenant_id',
        'limit_key',
        'period_key',
        'used_value',
        'reserved_value',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'used_value' => 'integer',
        'reserved_value' => 'integer',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
    ];

    /**
     * Jumlah terpakai termasuk reservation.
     */
    public function jumlahDigunakan(): int
    {
        return (int) $this->used_value + (int) $this->reserved_value;
    }
}
