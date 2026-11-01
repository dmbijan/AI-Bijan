<?php

namespace App\Modules\Finance\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'fiscal_year', 'status', 'approved_by', 'approved_at'];

    protected $casts = ['fiscal_year' => 'integer', 'approved_at' => 'datetime'];

    public function lines()
    {
        return $this->hasMany(BudgetLine::class);
    }
}
