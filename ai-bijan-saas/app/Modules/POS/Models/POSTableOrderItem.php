<?php

namespace App\Modules\POS\Models;

use Illuminate\Database\Eloquent\Model;

class POSTableOrderItem extends Model
{
    protected $table = 'pos_table_order_items';

    protected $fillable = ['order_id', 'item_id', 'name', 'quantity', 'unit_price', 'note'];

    protected $casts = ['quantity' => 'decimal:3', 'unit_price' => 'decimal:2'];
}
