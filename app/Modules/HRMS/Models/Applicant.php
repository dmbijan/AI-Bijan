<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Applicant extends Model
{
    use BelongsToTenant;

    protected $table = 'hrms_applicants';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['interview_at' => 'datetime', 'stage_changed_at' => 'datetime'];
}
