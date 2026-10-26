<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AssetMovement extends Model
{
    use BelongsToTenant;

    protected $table = 'asset_movements';

    protected $fillable = ['asset_id', 'from_location', 'to_location', 'from_custodian_id', 'to_custodian_id', 'moved_on', 'note', 'user_id'];

    protected $casts = ['moved_on' => 'date:Y-m-d'];
}
