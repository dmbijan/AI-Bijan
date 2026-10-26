<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    use BelongsToTenant;

    protected $table = 'boms';

    protected $fillable = ['item_id', 'version', 'type', 'quantity', 'labour_cost', 'overhead_cost', 'is_active', 'is_default', 'notes', 'created_by'];

    protected $casts = ['is_active' => 'boolean', 'is_default' => 'boolean'];

    public function lines()
    {
        return $this->hasMany(BomLine::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
