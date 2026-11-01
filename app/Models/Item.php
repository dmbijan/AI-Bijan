<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use BelongsToTenant;

    protected $table = 'items';

    protected $fillable = ['tenant_id', 'sku', 'name', 'barcode', 'category', 'uom', 'is_stock', 'has_batch', 'has_expiry', 'reorder_level', 'safety_stock', 'standard_cost', 'sell_price', 'is_active',
        'lead_time_days', 'reorder_qty', 'min_order_qty', 'preferred_supplier_id', 'parent_id', 'brand', 'description', 'images', 'attributes', 'weight_g', 'channel_content'];

    protected $casts = ['is_stock' => 'boolean', 'has_batch' => 'boolean', 'has_expiry' => 'boolean', 'is_active' => 'boolean', 'standard_cost' => 'decimal:2', 'sell_price' => 'decimal:2', 'reorder_level' => 'decimal:3', 'safety_stock' => 'decimal:3',
        'reorder_qty' => 'decimal:3', 'min_order_qty' => 'decimal:3', 'images' => 'array', 'attributes' => 'array', 'channel_content' => 'array'];

    public function uoms()
    {
        return $this->hasMany(ItemUom::class);
    }
}
