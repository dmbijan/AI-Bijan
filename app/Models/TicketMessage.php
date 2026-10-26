<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class TicketMessage extends Model
{
    use BelongsToTenant;

    protected $table = 'ticket_messages';

    protected $fillable = ['ticket_id', 'user_id', 'internal', 'body'];

    protected $casts = ['internal' => 'boolean'];
}
