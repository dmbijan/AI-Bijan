<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class StockMovementLine extends Model
{
    use BelongsToTenant;

    protected $table = 'stock_movement_lines';

    public $timestamps = false;

    protected $fillable = ['stock_movement_id', 'item_id', 'batch_no', 'expires_on', 'qty', 'unit_cost', 'system_qty', 'cost_value'];

    protected $casts = [
        'qty' => 'decimal:6',
        'unit_cost' => 'decimal:6',
        'system_qty' => 'decimal:6',
        'cost_value' => 'decimal:6',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
