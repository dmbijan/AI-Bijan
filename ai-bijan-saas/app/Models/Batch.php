<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    use BelongsToTenant;

    protected $table = 'batches';

    protected $fillable = ['item_id', 'batch_no', 'manufactured_on', 'expires_on'];

    protected $casts = ['manufactured_on' => 'date', 'expires_on' => 'date'];
}
