<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ExitInterview extends Model
{
    use BelongsToTenant;

    protected $table = 'hrms_exit_interviews';

    protected $guarded = ['id', 'tenant_id'];

    protected $casts = ['would_rehire' => 'boolean'];
}
