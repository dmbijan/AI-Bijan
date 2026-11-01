<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class DesignationSkill extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $table = 'hrms_designation_skills';

    protected $guarded = ['id', 'tenant_id'];
}
