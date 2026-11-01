<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Recognition extends Model
{
    use BelongsToTenant;

    protected $table = 'hrms_recognitions';

    protected $guarded = ['id', 'tenant_id'];
}
