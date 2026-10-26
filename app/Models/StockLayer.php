<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class StockLayer extends Model
{
    use BelongsToTenant;

    protected $table = 'stock_layers';

    public $timestamps = false;

    protected $fillable = ['item_id', 'warehouse_id', 'batch_id', 'qty_remaining', 'unit_cost', 'received_at'];

    protected $casts = ['received_at' => 'datetime'];
}
