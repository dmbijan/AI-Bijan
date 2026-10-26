<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use BelongsToTenant;

    protected $table = 'items';

    protected $fillable = ['tenant_id', 'sku', 'name', 'barcode', 'category', 'uom', 'is_stock', 'has_batch', 'has_expiry', 'reorder_level', 'safety_stock', 'standard_cost', 'sell_price', 'is_active'];

    protected $casts = ['is_stock' => 'boolean', 'has_batch' => 'boolean', 'has_expiry' => 'boolean', 'is_active' => 'boolean', 'standard_cost' => 'decimal:2', 'sell_price' => 'decimal:2', 'reorder_level' => 'decimal:3', 'safety_stock' => 'decimal:3'];

    public function uoms()
    {
        return $this->hasMany(ItemUom::class);
    }
}
