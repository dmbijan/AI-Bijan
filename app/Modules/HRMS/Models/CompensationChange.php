<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class CompensationChange extends Model
{
    use \App\Models\Concerns\ScopedToOrgUnits;

    protected static string $orgScopeColumn = 'employee_id';

    protected static string $orgScopeKind = 'employees';

    use BelongsToTenant;

    protected $table = 'hrms_compensation_changes';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['effective_on' => 'date:Y-m-d', 'decided_at' => 'datetime', 'amount' => 'float', 'current_salary' => 'float', 'new_salary' => 'float'];
}
