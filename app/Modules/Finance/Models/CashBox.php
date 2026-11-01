<?php

namespace App\Modules\Finance\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CashBox extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'type', 'float_amount', 'is_active'];

    protected $casts = ['float_amount' => 'decimal:2', 'is_active' => 'boolean'];

    public function entries()
    {
        return $this->hasMany(CashBoxEntry::class);
    }

    public function baki(): float
    {
        return round((float) $this->entries()->sum('amount'), 2);
    }
}
