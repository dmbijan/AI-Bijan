<?php

namespace App\Modules\Cod\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Kutipan wang COD harian — reconciliation dengan kurier.
 */
class CodCollection extends Model
{
    use BelongsToTenant;

    protected $table = 'cod_collections';

    protected $fillable = [
        'tenant_id',
        'collection_date',
        'courier',
        'total_collected',
        'total_expected',
        'orders_collected',
        'orders_failed',
        'total_refunded',
        'note',
        'recorded_by',
    ];

    protected $casts = [
        'collection_date' => 'date',
        'total_collected' => 'decimal:2',
        'total_expected' => 'decimal:2',
        'total_refunded' => 'decimal:2',
        'orders_collected' => 'integer',
        'orders_failed' => 'integer',
    ];

    protected $attributes = [
        'total_collected' => 0,
        'total_expected' => 0,
        'orders_collected' => 0,
        'orders_failed' => 0,
        'total_refunded' => 0,
    ];

    /**
     * Peratus kutipan berjaya.
     */
    public function kadarKutipan(): float
    {
        $expected = (float) $this->total_expected;

        if ($expected <= 0) {
            return 0.0;
        }

        return round(((float) $this->total_collected / $expected) * 100, 2);
    }

    /**
     * Jurang antara dijangka dan dikutip.
     */
    public function jurang(): float
    {
        return round((float) $this->total_expected - (float) $this->total_collected, 2);
    }
}
