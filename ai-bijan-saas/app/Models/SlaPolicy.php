<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    use BelongsToTenant;

    protected $table = 'sla_policies';

    public $timestamps = false;

    protected $fillable = ['priority', 'first_response_minutes', 'resolution_minutes'];

    protected $casts = [];
}
