<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BenefitPlan extends Model
{
    use BelongsToTenant;

    protected $table = 'hrms_benefit_plans';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['panel_clinics' => 'array', 'is_active' => 'boolean', 'annual_limit' => 'float', 'per_claim_limit' => 'float'];
}
