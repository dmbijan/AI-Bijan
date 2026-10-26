<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    use BelongsToTenant;

    protected $table = 'ticket_categories';

    public $timestamps = false;

    protected $fillable = ['name', 'default_assignee_id'];

    protected $casts = [];
}
