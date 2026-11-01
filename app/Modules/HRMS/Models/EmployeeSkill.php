<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class EmployeeSkill extends Model
{
    use BelongsToTenant;

    protected $table = 'hrms_employee_skills';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['certified_until' => 'date:Y-m-d'];
}
