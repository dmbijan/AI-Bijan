<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    use BelongsToTenant;

    protected $table = 'task_comments';

    protected $fillable = ['task_id', 'user_id', 'body', 'file_ulid'];

    protected $casts = [];
}
