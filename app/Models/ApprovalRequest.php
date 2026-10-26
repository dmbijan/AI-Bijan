<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use BelongsToTenant;

    protected $fillable = ['type', 'payload', 'status', 'requested_by', 'decided_by', 'decided_at', 'reason'];

    protected $casts = ['payload' => 'array', 'decided_at' => 'datetime'];
}
