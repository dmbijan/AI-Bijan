<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    use BelongsToTenant;

    protected $table = 'hrms_vacancies';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['closes_on' => 'date:Y-m-d'];
}
