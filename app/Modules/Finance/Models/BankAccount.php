<?php

namespace App\Modules\Finance\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'bank_name', 'account_last4', 'gl_account_id', 'currency', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function lines()
    {
        return $this->hasMany(BankStatementLine::class);
    }
}
