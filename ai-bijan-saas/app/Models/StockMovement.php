<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use BelongsToTenant;

    protected $table = 'stock_movements';

    protected $fillable = ['number', 'type', 'status', 'from_warehouse_id', 'to_warehouse_id', 'reason', 'reference', 'freight_in', 'created_by', 'posted_by', 'posted_at', 'received_by', 'received_at'];

    protected $casts = ['posted_at' => 'datetime', 'received_at' => 'datetime'];

    public function lines()
    {
        return $this->hasMany(StockMovementLine::class);
    }
}
