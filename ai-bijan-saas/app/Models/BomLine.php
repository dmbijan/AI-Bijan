<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BomLine extends Model
{
    use BelongsToTenant;

    protected $table = 'bom_lines';

    public $timestamps = false;

    protected $fillable = ['bom_id', 'item_id', 'qty', 'wastage_pct'];

    protected $casts = [];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
