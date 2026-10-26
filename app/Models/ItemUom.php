<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ItemUom extends Model
{
    use BelongsToTenant;

    protected $table = 'item_uoms';

    public $timestamps = false;

    protected $fillable = ['item_id', 'uom', 'factor'];

    protected $casts = [];
}
