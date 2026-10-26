<?php

namespace App\Modules\HRMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequestHistory extends Model
{
    use HasFactory;

    protected $table = 'hrms_leave_request_history';

    protected $fillable = [
        'leave_request_id',
        'user_id',
        'action',
        'changes',
        'notes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    /**
     * Get the leave request.
     */
    public function leaveRequest(): BelongsTo
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
