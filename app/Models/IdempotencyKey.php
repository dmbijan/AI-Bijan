<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    use BelongsToTenant;

    protected $table = 'idempotency_keys';

    protected $fillable = ['tenant_id', 'key', 'scope', 'result'];

    protected $casts = ['result' => 'array'];
}
