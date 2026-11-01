<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ChecklistTemplate extends Model
{
    use BelongsToTenant;

    protected $table = 'hrms_checklist_templates';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['items' => 'array'];
}
