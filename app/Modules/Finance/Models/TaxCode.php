<?php

namespace App\Modules\Finance\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TaxCode extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'code', 'name', 'type', 'rate', 'effective_from', 'effective_to', 'is_active'];

    protected $casts = ['rate' => 'decimal:2', 'effective_from' => 'date', 'effective_to' => 'date', 'is_active' => 'boolean'];

    public function setEffectiveFromAttribute($v): void
    {
        $this->attributes['effective_from'] = $v === null ? null : \Illuminate\Support\Carbon::parse($v)->toDateString();
    }

    public function setEffectiveToAttribute($v): void
    {
        $this->attributes['effective_to'] = $v === null ? null : \Illuminate\Support\Carbon::parse($v)->toDateString();
    }
}
