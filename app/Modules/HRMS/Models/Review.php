<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use BelongsToTenant;

    protected $table = 'hrms_reviews';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['submitted_at' => 'datetime', 'goal_score' => 'float'];
}
