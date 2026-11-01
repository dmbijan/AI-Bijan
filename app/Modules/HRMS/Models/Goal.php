<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use BelongsToTenant;

    protected $table = 'hrms_goals';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['due_on' => 'date:Y-m-d', 'target' => 'float', 'actual' => 'float'];
}
