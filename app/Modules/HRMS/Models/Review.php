<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use \App\Models\Concerns\ScopedToOrgUnits;

    protected static string $orgScopeColumn = 'employee_id';

    protected static string $orgScopeKind = 'employees';

    use BelongsToTenant;

    protected $table = 'hrms_reviews';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['submitted_at' => 'datetime', 'goal_score' => 'float'];
}
