<?php

namespace App\Modules\Finance\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CashCount extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'cash_box_id', 'cash_shift_id', 'denominations', 'counted_total', 'expected_total', 'variance', 'note', 'counted_by',
    ];

    protected $casts = ['denominations' => 'array', 'counted_total' => 'decimal:2', 'expected_total' => 'decimal:2', 'variance' => 'decimal:2'];
}
