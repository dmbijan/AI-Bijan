<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AssetDepreciation extends Model
{
    use BelongsToTenant;

    protected $table = 'asset_depreciations';

    public $timestamps = false;

    protected $fillable = ['asset_id', 'period', 'amount', 'accumulated', 'book_value', 'posted', 'posted_at'];

    protected $casts = [
        'amount' => 'decimal:2',
        'accumulated' => 'decimal:2',
        'book_value' => 'decimal:2',
        'posted' => 'boolean',
        'posted_at' => 'datetime',
    ];
}
