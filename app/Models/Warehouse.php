<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use BelongsToTenant;

    protected $table = 'warehouses';

    protected $fillable = ['code', 'name', 'type', 'address', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];
}
