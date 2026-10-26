<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use BelongsToTenant;

    protected $fillable = ['name', 'prefix', 'hash', 'scopes', 'created_by', 'last_used_at', 'revoked_at'];

    protected $hidden = ['hash'];

    protected $casts = ['scopes' => 'array', 'last_used_at' => 'datetime', 'revoked_at' => 'datetime'];
}
