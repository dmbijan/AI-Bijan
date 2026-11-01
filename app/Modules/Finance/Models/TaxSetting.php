<?php

namespace App\Modules\Finance\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TaxSetting extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'sst_registration_no', 'sst_registered_from', 'period_start_month', 'default_tax_code'];

    protected $casts = ['sst_registered_from' => 'date', 'period_start_month' => 'integer'];
}
