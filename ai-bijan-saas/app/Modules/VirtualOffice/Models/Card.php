<?php

namespace App\Modules\VirtualOffice\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Card extends Model
{
    use HasFactory;

    protected $table = 'vo_cards';

    protected $fillable = [
        'board_id',
        'column_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'priority',
        'status',
        'due_date',
        'position',
        'labels',
        'attachments',
        'checklist',
        'completed_at',
    ];

    protected $casts = [
        'labels' => 'array',
        'attachments' => 'array',
        'checklist' => 'array',
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the board this card belongs to.
     */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Get the column this card is in.
     */
    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    /**
     * Get the user who created this card.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the agent assigned to this card.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'assigned_to');
    }

    /**
     * Get comments on this card.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(CardComment::class, 'card_id');
    }

    /**
     * Get history of this card.
     */
    public function history(): HasMany
    {
        return $this->hasMany(CardHistory::class, 'card_id');
    }

    /**
     * Check if card is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !in_array($this->status, ['done', 'cancelled']);
    }

    /**
     * Check if card is due soon (within 24 hours).
     */
    public function isDueSoon(): bool
    {
        if (!$this->due_date) {
            return false;
        }
        $now = now();
        $diff = $this->due_date->diffInHours($now, false);
        return $diff > 0 && $diff <= 24 && !in_array($this->status, ['done', 'cancelled']);
    }

    /**
     * Mark card as completed.
     */
    public function complete(): void
    {
        $this->update([
            'status' => 'done',
            'completed_at' => now(),
        ]);
    }

    /**
     * Move card to different column.
     */
    public function moveToColumn(int $columnId): void
    {
        $this->update(['column_id' => $columnId]);
    }

    /**
     * Reorder card position within column.
     */
    public function reorder(int $position): void
    {
        $this->update(['position' => $position]);
    }

    /**
     * Add comment to card.
     */
    public function addComment(string $body, int $userId): CardComment
    {
        return $this->comments()->create([
            'user_id' => $userId,
            'body' => $body,
        ]);
    }

    /**
     * Update checklist items.
     */
    public function updateChecklist(array $items): void
    {
        $this->update(['checklist' => $items]);
    }

    /**
     * Get completed checklist count.
     */
    public function getCompletedChecklistCount(): int
    {
        if (!$this->checklist) {
            return 0;
        }
        return collect($this->checklist)->where('completed', true)->count();
    }

    /**
     * Get total checklist count.
     */
    public function getTotalChecklistCount(): int
    {
        if (!$this->checklist) {
            return 0;
        }
        return count($this->checklist);
    }

    /**
     * Get checklist progress percentage.
     */
    public function getChecklistProgress(): float
    {
        $total = $this->getTotalChecklistCount();
        if ($total === 0) {
            return 0.0;
        }
        return ($this->getCompletedChecklistCount() / $total) * 100;
    }
}
