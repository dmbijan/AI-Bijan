<?php

namespace App\Modules\SCM\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/** SUP-015 Kos pendaratan: caj penghantaran, duti & insurans yang ditambah ke kos stok terimaan. */
class LandedCost extends Model
{
    use BelongsToTenant;

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['posted_at' => 'datetime', 'total' => 'float', 'capitalised' => 'float', 'expensed' => 'float'];

    public function charges()
    {
        return $this->hasMany(LandedCostCharge::class);
    }

    public function lines()
    {
        return $this->hasMany(LandedCostLine::class);
    }
}
