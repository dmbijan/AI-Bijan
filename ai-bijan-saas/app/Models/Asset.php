<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use BelongsToTenant;

    protected $table = 'assets';

    protected $fillable = ['tag', 'name', 'category_id', 'serial_no', 'location', 'custodian_id', 'supplier', 'purchase_date', 'depreciation_start', 'cost', 'residual_value', 'useful_life_months', 'method', 'warranty_until', 'status', 'disposed_on', 'disposal_proceeds', 'disposal_gain_loss', 'disposal_reason'];

    protected $casts = ['purchase_date' => 'date:Y-m-d', 'depreciation_start' => 'date:Y-m-d', 'warranty_until' => 'date:Y-m-d', 'disposed_on' => 'date:Y-m-d'];

    public function depreciations()
    {
        return $this->hasMany(AssetDepreciation::class);
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class);
    }
}
