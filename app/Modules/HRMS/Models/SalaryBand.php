<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SalaryBand extends Model
{
    use BelongsToTenant;

    protected $table = 'hrms_salary_bands';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['min_salary' => 'float', 'max_salary' => 'float'];
}
