<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AssetMaintenance extends Model
{
    use BelongsToTenant;

    protected $table = 'asset_maintenances';

    protected $fillable = ['asset_id', 'type', 'scheduled_on', 'done_on', 'cost', 'vendor', 'notes', 'repeat_months', 'status'];

    protected $casts = ['scheduled_on' => 'date:Y-m-d', 'done_on' => 'date:Y-m-d'];
}
