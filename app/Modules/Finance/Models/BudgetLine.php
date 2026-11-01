<?php

namespace App\Modules\Finance\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BudgetLine extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'budget_id', 'account_id', 'month', 'amount'];

    protected $casts = ['month' => 'integer', 'amount' => 'decimal:2'];
}
