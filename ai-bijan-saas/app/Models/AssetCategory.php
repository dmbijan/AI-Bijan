<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use BelongsToTenant;

    protected $table = 'asset_categories';

    protected $fillable = ['name', 'useful_life_months', 'method', 'residual_pct'];

    protected $casts = [];
}
