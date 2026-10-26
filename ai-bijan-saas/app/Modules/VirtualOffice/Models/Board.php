<?php

namespace App\Modules\VirtualOffice\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Board extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'vo_boards';

    protected $fillable = [
        'tenant_id',
        'created_by',
        'name',
        'description',
        'visibility',
        'color',
        'settings',
        'archived_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'archived_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the board.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the creator of the board.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get members of this board.
     */
    public function members(): HasMany
    {
        return $this->hasMany(BoardMember::class);
    }

    /**
     * Get columns for this board.
     */
    public function columns(): HasMany
    {
        return $this->hasMany(Column::class);
    }

    /**
     * Get cards/tasks for this board.
     */
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }

    /**
     * Check if board is archived.
     */
    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * Archive the board.
     */
    public function archive(): void
    {
        $this->update(['archived_at' => now()]);
    }

    /**
     * Restore the board from archive.
     */
    public function restore(): void
    {
        $this->update(['archived_at' => null]);
    }

    /**
     * Check if user has access to this board.
     */
    public function hasAccess(int $userId): bool
    {
        if ($this->visibility === 'public') {
            return true;
        }
        if ($this->visibility === 'team') {
            // Team-wide access
            return true;
        }
        // Private board - check membership
        return $this->members()->where('user_id', $userId)->exists();
    }
}
