<?php

namespace App\Modules\VirtualOffice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Column extends Model
{
    use HasFactory;

    protected $table = 'vo_columns';

    protected $fillable = [
        'board_id',
        'name',
        'position',
        'is_default',
    ];

    /**
     * Get the board this column belongs to.
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Get cards in this column.
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
}
