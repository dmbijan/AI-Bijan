<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class WorkOrderMaterial extends Model
{
    use BelongsToTenant;

    protected $table = 'work_order_materials';

    public $timestamps = false;

    protected $fillable = ['work_order_id', 'item_id', 'qty_required', 'qty_consumed', 'cost'];

    protected $casts = [
        'qty_required' => 'decimal:6',
        'qty_consumed' => 'decimal:6',
        'cost' => 'decimal:6',
    ];
}
