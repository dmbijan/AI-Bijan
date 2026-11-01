<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    use BelongsToTenant;

    protected $table = 'hrms_survey_responses';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['answers' => 'array'];
}
