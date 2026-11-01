<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use BelongsToTenant;

    protected $table = 'hrms_skills';

    protected $guarded = ['id', 'tenant_id'];
}
