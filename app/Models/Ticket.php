<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use BelongsToTenant;

    protected $table = 'tickets';

    protected $fillable = ['number', 'subject', 'description', 'requester_name', 'requester_email', 'requester_phone', 'channel', 'category_id', 'priority', 'status', 'assignee_id', 'first_response_due', 'resolution_due', 'first_responded_at', 'resolved_at', 'response_breached', 'resolution_breached', 'escalation_level', 'access_token_hash'];

    protected $casts = ['first_response_due' => 'datetime', 'resolution_due' => 'datetime', 'first_responded_at' => 'datetime', 'resolved_at' => 'datetime', 'response_breached' => 'boolean', 'resolution_breached' => 'boolean'];

    protected $hidden = ['access_token_hash'];

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }
}
