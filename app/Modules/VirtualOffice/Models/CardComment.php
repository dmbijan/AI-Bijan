<?php

namespace App\Modules\VirtualOffice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardComment extends Model
{
    use HasFactory;

    protected $table = 'vo_card_comments';

    protected $fillable = [
        'card_id',
        'user_id',
        'body',
    ];

    /**
     * Get the card this comment belongs to.
     */
    public function card(): BelongsTo
    {
        return $this->belongsTo(Card::class, 'card_id');
    }

    /**
     * Get the user who created this comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
