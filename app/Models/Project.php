<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use BelongsToTenant;

    protected $table = 'projects';

    protected $fillable = ['code', 'name', 'description', 'client', 'status', 'start_date', 'end_date', 'budget', 'hourly_rate', 'owner_id'];

    protected $casts = ['start_date' => 'date:Y-m-d', 'end_date' => 'date:Y-m-d'];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'project_members')->withPivot('tenant_id');
    }
}
