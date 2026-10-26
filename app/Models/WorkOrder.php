<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use BelongsToTenant;

    protected $table = 'work_orders';

    protected $fillable = ['number', 'bom_id', 'item_id', 'qty_planned', 'source_warehouse_id', 'target_warehouse_id', 'status', 'qty_good', 'qty_reject', 'material_cost', 'labour_cost', 'overhead_cost', 'unit_cost', 'standard_unit_cost', 'variance', 'created_by', 'completed_at'];

    protected $casts = ['completed_at' => 'datetime'];

    public function materials()
    {
        return $this->hasMany(WorkOrderMaterial::class);
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class);
    }
}
