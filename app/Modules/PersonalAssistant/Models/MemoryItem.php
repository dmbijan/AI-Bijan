<?php

namespace App\Modules\PersonalAssistant\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class MemoryItem extends Model
{
    use BelongsToTenant;

    protected $table = 'ai_memory_items';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'memory_type',
        'category',
        'content_text',
        'content_json',
        'embedding_id',
        'source',
        'confidence_score',
        'is_verified',
        'expires_at',
    ];

    protected $casts = [
        'content_json' => 'array',
        'confidence_score' => 'decimal:2',
        'is_verified' => 'boolean',
        'expires_at' => 'datetime',
    ];

    const TYPE_PREFERENCE = 'preference';
    const TYPE_CONTEXT = 'context';
    const TYPE_INSIGHT = 'insight';
    const TYPE_KNOWLEDGE = 'knowledge';
    const TYPE_HISTORY = 'history';

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)->orWhereNull('user_id');
        });
    }

    public function scopeShared($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('memory_type', $type);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Memori ini boleh diakses oleh pengguna tertentu?
     */
    public function isAccessibleBy($userId): bool
    {
        return $this->user_id === null || $this->user_id === $userId;
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
