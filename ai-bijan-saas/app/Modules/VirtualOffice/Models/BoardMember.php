<?php

namespace App\Modules\VirtualOffice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoardMember extends Model
{
    use HasFactory;

    protected $table = 'vo_board_members';

    protected $fillable = [
        'board_id',
        'user_id',
        'role',
    ];

    /**
     * Get the board this member belongs to.
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Check if member is owner.
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Check if member can edit.
     */
    public function canEdit(): bool
    {
        return in_array($this->role, ['owner', 'editor']);
    }
}
