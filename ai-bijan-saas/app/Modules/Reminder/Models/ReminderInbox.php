<?php

namespace App\Modules\Reminder\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderInbox extends Model
{
    use BelongsToTenant;

    protected $table = 'reminder_inbox';

    protected $fillable = [
        'tenant_id',
        'reminder_id',
        'headline',
        'summary',
        'urgency',
        'suggested_action',
        'context',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'context' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected $attributes = [
        'urgency' => 'normal',
        'is_read' => false,
    ];

    public function reminder(): BelongsTo
    {
        return $this->belongsTo(Reminder::class, 'reminder_id');
    }
}
