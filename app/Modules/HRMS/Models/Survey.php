<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use BelongsToTenant;

    protected $table = 'hrms_surveys';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['questions' => 'array', 'anonymous' => 'boolean', 'closes_on' => 'date:Y-m-d'];
}
