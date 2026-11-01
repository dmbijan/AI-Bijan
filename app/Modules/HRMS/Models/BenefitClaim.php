<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BenefitClaim extends Model
{
    use \App\Models\Concerns\ScopedToOrgUnits;

    protected static string $orgScopeColumn = 'employee_id';

    protected static string $orgScopeKind = 'employees';

    use BelongsToTenant;

    protected $table = 'hrms_benefit_claims';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['claim_date' => 'date:Y-m-d', 'decided_at' => 'datetime', 'amount' => 'float', 'approved_amount' => 'float'];
}
