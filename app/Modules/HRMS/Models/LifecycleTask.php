<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class LifecycleTask extends Model
{
    use \App\Models\Concerns\ScopedToOrgUnits;

    protected static string $orgScopeColumn = 'employee_id';

    protected static string $orgScopeKind = 'employees';

    use BelongsToTenant;

    protected $table = 'hrms_lifecycle_tasks';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['due_on' => 'date:Y-m-d', 'done_at' => 'datetime'];
}
