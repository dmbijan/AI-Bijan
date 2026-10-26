<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class StockBalance extends Model
{
    use BelongsToTenant;

    protected $table = 'stock_balances';

    protected $fillable = ['item_id', 'warehouse_id', 'batch_id', 'qty', 'reserved', 'value'];

    protected $casts = [];
}
