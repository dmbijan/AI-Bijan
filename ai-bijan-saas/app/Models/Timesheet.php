<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Timesheet extends Model
{
    use BelongsToTenant;

    protected $table = 'timesheets';

    protected $fillable = ['project_id', 'task_id', 'user_id', 'work_date', 'hours', 'note', 'billable', 'status', 'approved_by'];

    protected $casts = ['work_date' => 'date:Y-m-d', 'billable' => 'boolean'];
}
